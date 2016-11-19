<?php

//namespace OndrejnovEET;

//use Ondrejnov\EET\Exceptions\ClientException;
//use Ondrejnov\EET\Exceptions\RequirementsException;
//use Ondrejnov\EET\Exceptions\ServerException;
//use Ondrejnov\EET\SoapClient;
//use Ondrejnov\EET\Utils\Format;

/**
 * Receipt for Ministry of Finance
 */
class Ondrejnov_EET_Dispatcher {

    /**
     * Certificate key
     * @var string
     */
    private $key;

    /**
     * Certificate
     * @var string
     */
    private $cert;

    /**
     * WSDL path or URL
     * @var string
     */
    private $service;

    /**
     * @var boolean
     */
    public $trace;

    /**
     *
     * @var Ondrejnov_EET_SoapClient
     */
    protected $soapClient;


    protected $bkp;
    protected $pkp;
    protected $fik;
    /** @var  Ondrejnov_EET_Receipt */
    protected $lastReceipt;

    /**
     * 
     * @param string $key
     * @param string $cert
     */
    public function __construct($service, $key, $cert) {
        $this->service = $service;
        $this->key = $key;
        $this->cert = $cert;
        $this->checkRequirements();
    }

    /**
     * 
     * @param string $service
     * @param Ondrejnov_EET_Receipt $receipt
     * @return boolean|string
     */
    public function check(Ondrejnov_EET_Receipt $receipt) {
        try {
            return $this->send($receipt, TRUE);
        } catch (Ondrejnov_EET_Exceptions_ServerException $e) {
            return FALSE;
        }
    }

    /**
     * 
     * @param boolean $tillLastRequest optional If not set/FALSE connection time till now is returned.
     * @return float
     */
    public function getConnectionTime($tillLastRequest = FALSE) {
        !$this->trace && $this->throwTraceNotEnabled();
        return $this->getSoapClient()->__getConnectionTime($tillLastRequest);
    }

    /**
     * 
     * @return int
     */
    public function getLastResponseSize() {
        !$this->trace && $this->throwTraceNotEnabled();
        return mb_strlen($this->getSoapClient()->__getLastResponse(), '8bit');
    }

    /**
     * 
     * @return int
     */
    public function getLastRequestSize() {
        !$this->trace && $this->throwTraceNotEnabled();
        return mb_strlen($this->getSoapClient()->__getLastRequest(), '8bit');
    }

    /**
     * 
     * @return float time in ms
     */
    public function getLastResponseTime() {
        !$this->trace && $this->throwTraceNotEnabled();
        return $this->getSoapClient()->__getLastResponseTime();
    }

    /**
     * 
     * @throws Ondrejnov_EET_Exceptions_ClientException
     */
    private function throwTraceNotEnabled() {
        throw new Ondrejnov_EET_Exceptions_ClientException('Trace is not enabled! Set trace property to TRUE.');
    }

    protected function getXmlSecurityKey()
    {
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $objKey->loadKey($this->key, TRUE);
        return $objKey;
    }

    /**
     * 
     * @param Ondrejnov_EET_Receipt $receipt
     * @return array
     */
    public function getCheckCodes(Ondrejnov_EET_Receipt $receipt) {
//        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
//        $objKey->loadKey($this->key, TRUE);
        $objKey = $this->getXmlSecurityKey();

        $arr = array(
            $receipt->dic_popl,
            $receipt->id_provoz,
            $receipt->id_pokl,
            $receipt->porad_cis,
            $receipt->dat_trzby->format('c'),
            Ondrejnov_EET_Utils_Format::price($receipt->celk_trzba)
        );

        $this->pkp = $objKey->signData(implode('|', $arr));
        $this->bkp = Ondrejnov_EET_Utils_Format::BKB(sha1($this->pkp));
        return array(
            'pkp' => array(
                '_' => $this->pkp,
                'digest' => 'SHA256',
                'cipher' => 'RSA2048',
                'encoding' => 'base64'
            ),
            'bkp' => array(
                '_' => $this->bkp,
                'digest' => 'SHA1',
                'encoding' => 'base16'
            )
        );
    }

    /**
     * 
     * @param Receipt $receipt
     * @param boolean $check
     * @return boolean|string
     */
    public function send(Ondrejnov_EET_Receipt $receipt, $check = FALSE) {
        $this->initSoapClient();

        $response = $this->processData($receipt, $check);

        isset($response->Chyba) && $this->processError($response->Chyba);

        $this->fik = $check ? TRUE : $response->Potvrzeni->fik;
        return $this->fik;
    }

    /**
     * 
     * @throws Ondrejnov_EET_Exceptions_RequirementsException
     * @return void
     */
    private function checkRequirements() {
        if (!class_exists('SoapClient')) {
            throw new Ondrejnov_EET_Exceptions_RequirementsException('Class SoapClient is not defined! Please, allow php extension php_soap.dll in php.ini');
        }
    }

    /**
     * Get (or if not exists: initialize and get) SOAP client.
     * 
     * @return Ondrejnov_EET_SoapClient
     */
    private function getSoapClient() {
        !isset($this->soapClient) && $this->initSoapClient();
        return $this->soapClient;
    }

    /**
     * Require to initialize a new SOAP client for a new request.
     * 
     * @return void
     */
    protected function initSoapClient() {
        $this->soapClient = new Ondrejnov_EET_SoapClient($this->service, $this->key, $this->cert, $this->trace);
    }

    /**
     * 
     * @param Receipt $receipt
     * @param boolean $check
     * @return object
     */
    private function processData(Ondrejnov_EET_Receipt $receipt, $check = FALSE) {
        $this->lastReceipt = $receipt;
        $head = array(
            'uuid_zpravy' => $receipt->uuid_zpravy,
            'dat_odesl' => time(),
            'prvni_zaslani' => $receipt->prvni_zaslani,
            'overeni' => $check
        );

        $body = array(
            'dic_popl' => $receipt->dic_popl,
            'id_provoz' => $receipt->id_provoz,
            'id_pokl' => $receipt->id_pokl,
            'porad_cis' => $receipt->porad_cis,
            'dat_trzby' => $receipt->dat_trzby->format('c'),
            'celk_trzba' => Ondrejnov_EET_Utils_Format::price($receipt->celk_trzba),
            'rezim' => $receipt->rezim
        );
        if($receipt->dic_poverujiciho){
            $body['dic_poverujiciho'] = Ondrejnov_EET_Utils_Format::price($receipt->dic_poverujiciho);
        }
        if($receipt->zakl_nepodl_dph){
            $body['zakl_nepodl_dph'] = Ondrejnov_EET_Utils_Format::price($receipt->zakl_nepodl_dph);
        }
        if($receipt->zakl_dan1){
            $body['zakl_dan1'] = Ondrejnov_EET_Utils_Format::price($receipt->zakl_dan1);
        }
        if($receipt->dan1){
            $body['dan1'] = Ondrejnov_EET_Utils_Format::price($receipt->dan1);
        }
        if($receipt->zakl_dan2){
            $body['zakl_dan2'] = Ondrejnov_EET_Utils_Format::price($receipt->zakl_dan2);
        }
        if($receipt->dan2){
            $body['dan2'] = Ondrejnov_EET_Utils_Format::price($receipt->dan2);
        }
        if($receipt->zakl_dan3){
            $body['zakl_dan3'] = Ondrejnov_EET_Utils_Format::price($receipt->zakl_dan3);
        }
        if($receipt->dan3){
            $body['dan3'] = Ondrejnov_EET_Utils_Format::price($receipt->dan3);
        }

        return $this->getSoapClient()->OdeslaniTrzby(array(
                    'Hlavicka' => $head,
                    'Data' => $body,
                    'KontrolniKody' => $this->getCheckCodes($receipt)
            )
        );
    }

    /**
     * @param $error
     * @throws Ondrejnov_EET_Exceptions_ServerException
     */
    private function processError($error) {
        if ($error->kod) {
            $msgs = array(
                -1 => 'Docasna technicka chyba zpracovani – odeslete prosim datovou zpravu pozdeji',
                2 => 'Kodovani XML neni platne',
                3 => 'XML zprava nevyhovela kontrole XML schematu',
                4 => 'Neplatny podpis SOAP zpravy',
                5 => 'Neplatny kontrolni bezpecnostni kod poplatnika (BKP)',
                6 => 'DIC poplatnika ma chybnou strukturu',
                7 => 'Datova zprava je prilis velka',
                8 => 'Datova zprava nebyla zpracovana kvuli technicke chybe nebo chybe dat',
            );
            $msg = isset($msgs[$error->kod]) ? $msgs[$error->kod] : '';
            throw new Ondrejnov_EET_Exceptions_ServerException($msg, $error->kod);
        }
    }

    public function getBkp()
    {
        return $this->bkp;
    }

    public function getPkp()
    {
        return (string)$this->pkp;
    }

    public function getFik()
    {
        return $this->fik;
    }

    public function getLastReceipt()
    {
        return $this->lastReceipt;
    }
}
