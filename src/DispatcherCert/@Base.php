<?php

/**
 * Created by PhpStorm.
 * User: LamA.nOOb
 * Date: 06.11.2016
 * Time: 11:20
 */
interface Lamanoob7_EET_DispatcherCert_IBase
{
//    public function getDispatcher();

    public function getService();

    public function getKey();

    /** return bool */
    public function getIsKeyFile();

    public function getCert();

    public function getIsCertFile();
}