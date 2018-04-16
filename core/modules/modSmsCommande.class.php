<?php
/* SMS Commande - Send SMS since Commande card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \defgroup smscommande Send SMS since Commande CARD
 * \file    core/modules/modSmsCommande.class.php
 * \ingroup smscommande
 * \brief   ActionsSmsCommande
 *
 * Send SMS since Commande CARD
 */

include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

/**
 *  Description and activation class for module MyModule
 */
class modSmsCommande extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
    public function __construct($db) {
        global $langs,$conf;

        $this->db = $db;
        
        $this->numero = 432412;
                
        $this->rights_class = 'SmsCommande';

        $this->family = "Inovea Conseil";
	$this->special = 0;

        $this->module_position = 500;

        $this->name = "smscommande";

        // Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
        $this->description = "Module432412Desc";
        $this->editor_name = 'Inovea Conseil';
        $this->editor_url = 'https://www.inovea-conseil.com';

        $this->version = '1.0';

        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        
        $this->picto='inoveaconseil@smscommande';

        $this->module_parts = array(
            'css' => array('/smscommande/css/SmsCommande.css'),
            'hooks' => array(
                'ordercard',
            )
        );

        $this->dirs = array();

        // Config pages. Put here list of php page, stored into dolitest/admin directory, to use to setup module.
        $this->config_page_url = array("setup.php@smscommande");

        // Dependencies
        $this->hidden = false;
        $this->depends = array();
        $this->requiredby = array();
        $this->conflictwith = array();
        $this->phpmin = array(5,0);
        $this->need_dolibarr_version = array(3,7);
        $this->langfiles = array("smscommande@smscommande");
        
        $this->const = array();

        $this->tabs = array();

        if (! isset($conf->smscommande) || ! isset($conf->smscommande->enabled)) {
                $conf->smscommande=new stdClass();
                $conf->smscommande->enabled=0;
        }
        
        // Dictionaries
        $this->dictionaries=array();
        $this->boxes = array();	

        // Cronjobs
        $this->cronjobs = array();

        // Permissions
        $this->rights = array();
        $r=0;
        $this->rights[$r][0] = 43241201;
        $this->rights[$r][1] = $langs->trans("RightsSMSC");
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'send';
        $r++;

        $this->menu = array();
        $r=0;
        $r=1;
    }

    /**
     * Init function
     *
     * @param      string	$options    Options when enabling module ('', 'noboxes')
     * @return     int             	1 if OK, 0 if KO
     */
    public function init($options='')
    {
        $sql = array();

        $this->_load_tables('/smscommande/sql/');
        
        //VERIF EXTRAFIELD COMMANDE
        $extrafields = new ExtraFields($this->db);
        $extrafields_commande = $extrafields->fetch_name_optionals_label('commande');
        $pos = count($extrafields_commande);
        
        // N° à prévenir
        if (!isset($extrafields_commande['num_a_prevenir'])) {
            $extrafields->addExtraField('num_a_prevenir', 'N° à prévenir', 'phone', $pos++, null, 'commande', 0, 0, '', 0, true, '', 0, 0);
        }
        
        // Statut
        if (!isset($extrafields_commande['suivi'])) {
            $params = array(
                'options' => array(
                    1 => 'En attente',
                    2 => 'A traiter',
                    3 => 'Traitement en cours',
                    4 => 'A facturer',
                    5 => 'Terminée',
                    6 => 'Annulée',
                )
            );
            $extrafields->addExtraField('suivi', "Suivi commande", 'select', $pos++, null, 'commande', 0, 0, '', $params, true, '', 0, 0);
        }
        

        return $this->_init($sql, $options);
    }

    /**
     * Function called when module is disabled.
     * Remove from database constants, boxes and permissions from Dolibarr database.
     * Data directories are not deleted
     *
     * @param      string	$options    Options when enabling module ('', 'noboxes')
     * @return     int             	1 if OK, 0 if KO
     */
    public function remove($options = '')
    {
        $sql = array();

        return $this->_remove($sql, $options);
    }

}

