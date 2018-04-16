<?php
/* SMS Commande - Send SMS since Commande card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    admin/setup.php
 * \ingroup smscommande
 * \brief   smscommande module setup page.
 *
 * Set SMS API keys
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/smsCommande.lib.php';
require_once '../lib/sms.php';
//require_once "../class/myclass.class.php";
// Translations
$langs->load("smscommande@smscommande");
$langs->load("admin");
$langs->load("other");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

if ($action == 'setvalue' && $user->admin)
{
    $db->begin();
    $result=dolibarr_set_const($db, "SMSINTERVENTION_APPLICATION_KEY",GETPOST('SMSINTERVENTION_APPLICATION_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
    $result=dolibarr_set_const($db, "SMSINTERVENTION_APPLICATION_SECRET",GETPOST('SMSINTERVENTION_APPLICATION_SECRET','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
    $result=dolibarr_set_const($db, "SMSINTERVENTION_CONSUMER_KEY",GETPOST('SMSINTERVENTION_CONSUMER_KEY','alpha'),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
	
	if (! $error)
  	{
  		$db->commit();
                
                //Test de la connexion à l'API
                try {
                    $sms = new sms(dolibarr_get_const($db, "SMSINTERVENTION_APPLICATION_KEY"), dolibarr_get_const($db, "SMSINTERVENTION_APPLICATION_SECRET"), dolibarr_get_const($db, "SMSINTERVENTION_CONSUMER_KEY"));
                    if ($sms->connexion()) {
                        if ($sms->getCredit() > 0) {
                            setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
                        } else {
                            setEventMessages($langs->trans("SMS_CREDIT_EMPTY"), null, 'errors');
                        }
                        
                    } else {
                        setEventMessages($langs->trans("SMS_CONNEXION_FAILED"), null, 'errors');
                    }
                } catch (Exception $ex) {
                    setEventMessages($langs->trans("SMS_CONNEXION_FAILED"), null, 'errors');
                }
  	}
  	else
  	{
  		$db->rollback();
		dol_print_error($db);
    }
}

/*
 * Actions
 */

/*
 * View
 */
$page_name = "smsCommandeSetup";
$morejs=array("/smscommande/js/smsCommande.js");
llxHeader('', $langs->trans($page_name),'','','','',$morejs,'',0,0);

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';

// Configuration header
$head = smsCommandeAdminPrepareHead();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("Module432412Name"),
	0,
	"smscommande@smscommande"
);

// Setup page goes here
echo $langs->trans("smsCommandeSetupPage");


print '<br>';
print '<br>';

print '<table class="noborder" width="100%">';

$var=true;
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("AccountParameter").'</td>';
print '<td>'.$langs->trans("Value").'</td>';
print "</tr>\n";

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("SMSINTERVENTION_APPLICATION_KEY").'</td><td>';
print '<input size="64" type="text" name="SMSINTERVENTION_APPLICATION_KEY" value="'.$conf->global->SMSINTERVENTION_APPLICATION_KEY.'" required="required">';
print ' &nbsp; '.$langs->trans("Example").': 1BbbbbbbAAAAAA6010XdazwbwDsb25cM4qByYg_u';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("SMSINTERVENTION_APPLICATION_SECRET").'</td><td>';
print '<input size="64" type="text" name="SMSINTERVENTION_APPLICATION_SECRET" value="'.$conf->global->SMSINTERVENTION_APPLICATION_SECRET.'" required="required">';
print ' &nbsp; '.$langs->trans("Example").': 1BbbbbbbAAAAAAADLJuXsuTOgW6s6JmbfmZoT7BbfP';
print '</td></tr>';

$var=!$var;
print '<tr '.$bc[$var].'><td>';
print $langs->trans("SMSINTERVENTION_CONSUMER_KEY").'</td><td>';
print '<input size="64" type="text" name="SMSINTERVENTION_CONSUMER_KEY" value="'.$conf->global->SMSINTERVENTION_CONSUMER_KEY.'" required="required">';
print ' &nbsp; '.$langs->trans("Example").': 1BbbbbbbAAAAAAADLJuXsuTOgW6s6JmbfmZoT7BbfP';
print '</td></tr>';

print '</table>';



// Page end
dol_fiche_end();

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></div>';

print '</form>';
llxFooter();