<?php

//namespace Ondrejnov\EET;

class Ondrejnov_EET_SoapClient extends SoapClient {

    /** @var string */
    protected $key;

    /** @var string */
    protected $cert;

    /** @var boolean */
    protected $traceRequired;

    /** @var float */
    protected $connectionStartTime;

    /** @var float */
    protected $lastResponseStartTime;

    /** @var float */
    protected $lastResponseEndTime;

    /** @var string */
    protected $lastRequest;

    /**
     * 
     * @param string $service
     * @param string $key
     * @param string $cert
     * @param boolean $trace
     */
    public function __construct($service, $key, $cert, $trace = FALSE) {
        $this->connectionStartTime = microtime(TRUE);
        parent::__construct($service, array(
                'exceptions' => TRUE,
                'trace' => $trace
            )
        );
        $this->key = $key;
        $this->cert = $cert;
        $this->traceRequired = $trace;
    }

    protected function getObjKey()
    {
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $objKey->loadKey($this->key, TRUE);
        return $objKey;
    }

    protected function getCertContent()
    {
        return file_get_contents($this->cert);
    }

    public function __doRequest($request, $location, $saction, $version, $one_way = NULL) {
        $doc = new DOMDocument('1.0');
        $doc->loadXML($request);

        $objWSSE = new WSSESoap($doc);
        $objWSSE->addTimestamp();

        $objKey = $this->getObjKey();
        $objWSSE->signSoapDoc($objKey, array("algorithm" => XMLSecurityDSig::SHA256));

        $token = $objWSSE->addBinaryToken($this->getCertContent());
        $objWSSE->attachTokentoSig($token);

        $this->traceRequired && $this->lastResponseStartTime = microtime(TRUE);

        $this->lastRequest = $objWSSE->saveXML();
        $response = parent::__doRequest($this->lastRequest, $location, $saction, $version);

        $this->traceRequired && $this->lastResponseEndTime = microtime(TRUE);

        return $response;
    }

    /**
     * 
     * @return float
     */
    public function __getLastResponseTime() {
        return $this->lastResponseEndTime - $this->lastResponseStartTime;
    }

    /**
     * 
     * @return float
     */
    public function __getConnectionTime($tillLastRequest = FALSE) {
        return $tillLastRequest ? $this->getConnectionTimeTillLastRequest() : $this->getConnectionTimeTillNow();
    }

    private function getConnectionTimeTillLastRequest() {
        if (!$this->lastResponseEndTime || !$this->connectionStartTime) {
            return NULL;
        }
        return $this->lastResponseEndTime - $this->connectionStartTime;
    }

    private function getConnectionTimeTillNow() {
        if (!$this->connectionStartTime) {
            return NULL;
        }
        return microtime(TRUE) - $this->connectionStartTime;
    }

    /**
     * @return string
     */
    public function __getLastRequest() {
        return $this->lastRequest;
    }

}
