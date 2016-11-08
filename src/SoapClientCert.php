<?php

//namespace Ondrejnov\EET;

class Ondrejnov_EET_SoapClientCert extends Ondrejnov_EET_SoapClient
{
    protected $dispatcherCert;

    /**
     *
     * @param string $service
     * @param string $key
     * @param string $cert
     * @param boolean $trace
     */
    public function __construct(Lamanoob7_EET_DispatcherCert_IBase $dispatcherCert, $trace = FALSE) {
        $this->dispatcherCert = $dispatcherCert;
        parent::__construct($dispatcherCert->getService(), $dispatcherCert->getKey(), $dispatcherCert->getCert());
    }

    protected function getObjKey()
    {
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
        $objKey->loadKey($this->key, $this->dispatcherCert->getIsKeyFile());
        return $objKey;
    }

    protected function getCertContent()
    {
        if($this->dispatcherCert->getIsCertFile()){
            return file_get_contents($this->cert);
        } else {
            return $this->cert;
        }
    }

}