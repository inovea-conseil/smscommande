<?php
/* SMS Commande - Send SMS since COmmande card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    ajax/ajaxSendSms.php
 * \ingroup smscommande
 * \brief   smscommande send SMS
 *
 * SEND SMS (POST COMMANDE ID)
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

require_once dol_buildpath('/smscommande/lib/sms.php');

global $langs, $user, $db;

// Vérifie les droits
if (!isset($user->rights->SmsCommande) || !isset($user->rights->SmsCommande->send) || !$user->rights->SmsCommande->send) {
    exit;
}

$langs->load("smscommande@smscommande");

require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
$commande = new Commande($db);
$commande->fetch(GETPOST('id','alpha'));

//Societe
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
$societe = new Societe($db);
$societe->fetch($commande->socid);

        
$regexTel = '/^(\+33|0)(6|7)([0-9]{8})/';

$errorSms = NULL;

// Vérifie les constantes SMS et si la connexion se fait etc...
$applicationKey = $conf->global->SMSINTERVENTION_APPLICATION_KEY;
$applicationSecret = $conf->global->SMSINTERVENTION_APPLICATION_SECRET;
$consumerKey = $conf->global->SMSINTERVENTION_CONSUMER_KEY;

if ($applicationKey == '' || $applicationSecret == '' || $consumerKey == '') {
    $errorSms = 'SMS_CONSTANTE_EMPTY';
}

// Récupère le contenu du SMS
$smsText = '';
if (is_null($errorSms)) {
    $contentSms = unserialize($conf->global->SMSCOMMANDE_SMS_CONTENT);
    if ($contentSms == false || !is_array($contentSms) || !isset($contentSms[$commande->array_options['options_suivi']]) || empty($contentSms[$commande->array_options['options_suivi']])) {
        $errorSms = 'SMS_CONTENT_EMPTY';
    } else {
        $smsText = $contentSms[$commande->array_options['options_suivi']];
        
        // On remplace la ref client et la ref commande
        $smsText = str_replace('_REFCMDE_', $commande->ref, $smsText);
        $smsText = str_replace('_REFCLT_', $societe->code_client, $smsText);
    }
}

// Récupère le numéro sur lequel on envoie le SMS
$telSms = '';
if (is_null($errorSms)) {
    if (!preg_match($regexTel, $commande->array_options['options_num_a_prevenir'])) {
        // Sinon on recherche dans la fiche du tiers (société)
        
        if (!preg_match($regexTel, $societe->phone)) {
            $sql = "SELECT fk_socpeople
                    FROM llx_element_contact ec
                    INNER JOIN llx_c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
                    WHERE tc.active = 1
                    AND tc.code='CUSTOMER'
                    AND tc.element='commande'
                    AND ec.element_id = ".$commande->id;
             $resql=$db->query($sql);

             if ($resql) {
                $obj = $db->fetch_object($resql);
                $contact_suivi_id = $obj->fk_socpeople;
                if (!is_null($contact_suivi_id)) {
                    require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
                    $contact = new Contact($db);
                    $contact->fetch($contact_suivi_id);
                    if (!preg_match($regexTel, $contact->phone_mobile)) {
                        $errorSms = 'SMS_NO_MOBILE';
                    } else {
                        $telSms = $contact->phone_mobile;
                    }
                } else {
                    $errorSms = 'SMS_NO_MOBILE';
                }
            } else {
                $errorSms = 'SMS_NO_MOBILE';
            }
        } else {
            $telSms = $societe->phone;
        }
    } else {
        $telSms = $commande->array_options['options_num_a_prevenir'];
    }
}

// Envoi du SMS
if (is_null($errorSms)) {
    // on formate le numéro au format internationnal
    $telSms = substr($telSms, 0, 1) == '0' ? '+33'.substr($telSms, 1, strlen($telSms)-1) : $telSms;
    try {
        $sms = new sms($applicationKey, $applicationSecret, $consumerKey);
        if (!$sms->sendSMS($smsText, $telSms)) {
            $errorSms = 'SMS_SEND_FAILED';
        }
    } catch (Exception $ex) {
        $errorSms = 'SMS_CONNEXION_FAILED';
    }
}

// Retour
if (is_null($errorSms)) {
    //setEventMessages($langs->trans("SMS_SEND"), null, 'mesgs');
    //Historique
    setEventMessages($langs->trans("SMS_SEND"), null, 'mesgs');
    try {
        require_once dol_buildpath('/smscommande/class/smscommandehistory.class.php');
        $myobject=new Smscommandehistory($db);

        $myobject->fk_commande = $commande->id;
        $myobject->fk_user = $user->id;
        $myobject->status_commande = $commande->array_options['options_suivi'];
        $myobject->num_envoi = $telSms;
        $myobject->content = $smsText;

        $id=$myobject->create($user);
    } catch (Exception $ex) {
        //setEventMessages($ex->getMessage(), null, 'errors');
    }
    
    
} else {
    setEventMessages($langs->trans($errorSms), null, 'errors');
}