<?php
/* SMS Commande - Send SMS since Commande card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    class/actions_smscommande.class.php
 * \ingroup smscommande
 * \brief   ActionsSmsCommande
 *
 * Display button to send SMS
 */

require_once DOL_DOCUMENT_ROOT . '/core/lib/functions.lib.php';

/**
 * Class ActionsSmsCommande
 */
class ActionsSmsCommande
{
    /**
     * @var DoliDB Database handler
     */
    private $db;

    /**
     *  Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
        global $conf,$langs, $user, $db;
        
        $langs->load("smscommande@smscommande");
        
        $regexTel = '/^(\+33|0)(6|7)([0-9]{8})/';
        
        $errorSms = NULL;
        
        // Vérifie les droits
        if (!isset($user->rights->SmsCommande) || !isset($user->rights->SmsCommande->send) || !$user->rights->SmsCommande->send) {
            return 0;
        }
        
        // Vérifie les constantes SMS et si la connexion se fait etc...
        $applicationKey = $conf->global->SMSINTERVENTION_APPLICATION_KEY;
        $applicationSecret = $conf->global->SMSINTERVENTION_APPLICATION_SECRET;
        $consumerKey = $conf->global->SMSINTERVENTION_CONSUMER_KEY;
        
        if ($applicationKey == '' || $applicationSecret == '' || $consumerKey == '') {
            $errorSms = 'SMS_CONSTANTE_EMPTY';
        }
        
        // Vérifie si un contenu est défini pour ce statut
        if (is_null($errorSms)) {
            $contentSms = unserialize($conf->global->SMSCOMMANDE_SMS_CONTENT);
            if ($contentSms == false || !is_array($contentSms) || !isset($contentSms[$object->array_options['options_suivi']]) || empty($contentSms[$object->array_options['options_suivi']])) {
                $errorSms = 'SMS_CONTENT_EMPTY';
            }
        }
        
        // Vérifie si le numéro à prévenir est un portable
        if (is_null($errorSms)) {
            if (!preg_match($regexTel, $object->array_options['options_num_a_prevenir'])) {
                // Sinon on recherche dans la fiche du tiers (société)
                require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
                $societe = new Societe($this->db);
                $societe->fetch($object->socid);
                if (!preg_match($regexTel, $societe->phone)) {
                    $sql = "SELECT fk_socpeople
                            FROM llx_element_contact ec
                            INNER JOIN llx_c_type_contact tc ON ec.fk_c_type_contact = tc.rowid
                            WHERE tc.active = 1
                            AND tc.code='CUSTOMER'
                            AND tc.element='commande'
                            AND ec.element_id = ".$object->id;
                     $resql=$db->query($sql);
                     
                     if ($resql) {
                        $obj = $this->db->fetch_object($resql);
                        $contact_suivi_id = $obj->fk_socpeople;
                        if (!is_null($contact_suivi_id)) {
                            require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
                            $contact = new Contact($this->db);
                            $contact->fetch($contact_suivi_id);
                            if (!preg_match($regexTel, $contact->phone_mobile)) {
                                $errorSms = 'SMS_NO_MOBILE';
                            }
                        } else {
                            $errorSms = 'SMS_NO_MOBILE';
                        }
                    } else {
                        $errorSms = 'SMS_NO_MOBILE';
                    }
                }
            }
        }
         
        // Vérifie qu'un SMS n'a pas déjà été envoyé sur cette commande avec ce Statut
        if (is_null($errorSms)) {
            require_once dol_buildpath('/smscommande/class/smscommandehistory.class.php');
            $myobject=new Smscommandehistory($db);
            
            $filter['t.fk_commande'] = $object->id;
            $filter['t.status_commande'] = $object->array_options['options_suivi'];
            
            $countSmshistory = $myobject->fetchAll('', '', 0, 0, $filter);
            
            if ($countSmshistory > 0) {
                $errorSms = 'SMS_STILL_SEND';
            }
        }
        
        if (is_null($errorSms)) {
            echo '<div class="inline-block divButAction"><button class="butAction smsSendBtn" id="smsSendBtn-'.$object->id.'" data-id="'.$object->id.'">'.$langs->trans("SEND_SMS").'</button></div>';
            echo '<div id="dialog-confirm-'.$object->id.'" title="'.$langs->trans("SMS_MODAL_TITLE").'" style="display:none;" data-btnSendTxt="'.$langs->trans("SEND_SMS").'" data-btnCancelTxt="'.$langs->trans("SMS_CANCEL_BTN").'">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0px 12px 20px 0;"></span>'.$langs->trans("SMS_MODAL_DESCRIPTION").'</p>
</div>';
            echo '<script>';
            echo 'if (typeof ajaxFile == "undefined") {';
            echo 'var ajaxFile="'.dol_buildpath('/smscommande/ajax/ajaxSendSms.php', 1).'";';
            echo 'var commandeId= undefined;';
            echo '$.getScript("'. dol_buildpath('/smscommande/js/smsCommandeBtn.js', 1).'");';
            echo '}';
            echo '</script>';
        } else {
            echo '<div class="inline-block divButAction"><a class="butActionRefused" title="'.$langs->trans($errorSms).'">'.$langs->trans("SEND_SMS").'</a></div>';
        }
        
        return 0;
    }
}
