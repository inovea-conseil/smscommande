<?php

/* SMS Commande - Send SMS since ORDER card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    lib/sms.php
 * \ingroup smscommande
 * \brief   ActionsSmsCommande
 *
 * Class connect to OVH API and send SMS
 */

require __DIR__ . '/vendor/autoload.php';
use \Ovh\Sms\SmsApi;

/**
 * Class de connexion SMS
 * 
 */
class sms {
    private $smsOvh;
    private $account;
    
    public function __construct($applicationKey, $applicationSecret, $consumerKey) {
        $endpoint = 'ovh-eu';
        
        try {
            $this->smsOvh = new SmsApi( $applicationKey, $applicationSecret, $endpoint, $consumerKey );
        } catch (Exception $ex) {
            return false;
        }
        
        $accounts = $this->smsOvh->getAccounts();
        
        if (count($accounts) > 0) {
            $this->account = $accounts[0];
        }
    }
    
    /**
     * Vérifie que la connexion est bonne
     * 
     * @return boolean
     */
    public function connexion() {
        return !is_null($this->account) ? true : false;
    }
    
    public function getCredit() {
        if ($this->connexion()) {
            $accountDetail = $this->smsOvh->getAccountDetails($this->account);
            return $accountDetail['creditsLeft'];
        } else {
            return 0;
        }
    }
    
    public function sendSMS($content, $destinataire) {
        
        if (!$this->connexion()) {
            throw new Exception('SMS_CONNEXION_FAILED');
        }
        
        if ($this->getCredit() <= 0) {
            throw new Exception('SMS_CREDIT_EMPTY');
        }
        
        $this->smsOvh->setAccount($this->account);
        
        $message = $this->smsOvh->createMessage(true);
        try {
            $message->addReceiver($destinataire);
        } catch (Exception $ex) {
            throw new Exception('SMS_ERROR_DESTINATAIRE');
        }
        $message->setIsMarketing(false);

        try {
            $message->send($content);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            throw new Exception('SMS_ERROR_SEND');
        }
        return true;
    }
}
