<?php

/**
 * Created by PhpStorm.
 * User: LamA.nOOb
 * Date: 06.11.2016
 * Time: 11:20
 */
class Lamanoob7_EET_DispatcherCert_CertKey implements Lamanoob7_EET_DispatcherCert_IBase
{
    public $servicePath;
    public $certPath;
    public $keyPath;
    public $isKeyFile = true;

    public function __construct($servicePath, $keyPath, $certPath)
    {
        $this->servicePath = $servicePath;
        $this->certPath = $certPath;
        $this->keyPath = $keyPath;
    }

    public function getService()
    {
        return $this->servicePath;
    }

    public function getKey()
    {
        return $this->keyPath;
    }

    /**
     * @return bool
     */
    public function getIsKeyFile()
    {
        return true;
    }

    public function getCert()
    {
        return $this->certPath;
    }

    public function getIsCertFile()
    {
        return true;
    }
}