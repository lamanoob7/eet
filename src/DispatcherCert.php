<?php

/**
 * Receipt for Ministry of Finance
 */
class Ondrejnov_EET_DispatcherCert extends Ondrejnov_EET_Dispatcher{

    protected $dispatcherCert;
    /**
     * Ondrejnov_EET_DispatcherCert constructor.
     * @param Lamanoob7_EET_DispatcherCert_IBase $settings
     */
    public function __construct(Lamanoob7_EET_DispatcherCert_IBase $dispatcherCert) {
        $this->dispatcherCert = $dispatcherCert;
        parent::__construct($dispatcherCert->getService(), $dispatcherCert->getKey(), $dispatcherCert->getCert());
    }

    /**
     * @return XMLSecurityKey
     */
    protected function getXmlSecurityKey()
    {
        $key = $this->dispatcherCert->getKey();
        $isKeyFile = $this->dispatcherCert->getIsKeyFile();
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $objKey->loadKey($key, $isKeyFile);
        return $objKey;
    }

    /**
     * Require to initialize a new SOAP client for a new request.
     *
     * @return void
     */
    protected function initSoapClient() {
        $this->soapClient = new Ondrejnov_EET_SoapClientCert($this->dispatcherCert, $this->trace);
    }
}
