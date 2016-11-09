<?php

/**
 * Created by PhpStorm.
 * User: LamA.nOOb
 * Date: 06.11.2016
 * Time: 11:20
 */
class Lamanoob7_EET_DispatcherCert_P12 implements Lamanoob7_EET_DispatcherCert_IBase
{
    public $servicePath;
    public $cert;
    public $key;

    public function __construct($servicePath, $p12Path, $password)
    {
        $this->servicePath = $servicePath;
        if (!$certStore = file_get_contents($p12Path)) {
            echo "Error: Unable to read the cert file\n";
            exit;
        }

        if (openssl_pkcs12_read($certStore, $certInfo, $password)) {
            $this->cert = trim($certInfo['cert']);
            $this->key = trim($certInfo['pkey']);
        } else {
            echo "P12 Error: Unable to read the cert store.\n";
//    exit;
        }
    }

    public function getService()
    {
        return $this->servicePath;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return bool
     */
    public function getIsKeyFile()
    {
        return false;
    }

    public function getCert()
    {
        return $this->cert;
    }

    public function getIsCertFile()
    {
        return false;
    }
}