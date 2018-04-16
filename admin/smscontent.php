<?php
/* SMS Commande - Send SMS since COmmande card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    admin/smscontent.php
 * \ingroup smscommande
 * \brief   smscommande content sms setup page.
 *
 * Set SMS content
 */

// Load Dolibarr environment
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/smsCommande.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Translations
$langs->load("smscommande@smscommande");
$langs->load("admin");
$langs->load("other");

// Access control
if (! $user->admin) {
	accessforbidden();
}

// Extrafield
$extrafields = new ExtraFields($db);
$extrafields->fetch_name_optionals_label('commande');
$param=$extrafields->attribute_param['suivi'];

// Parameters
$action = GETPOST('action', 'alpha');

if ($action == 'setvalue' && $user->admin)
{
    $new_content_sms = array();
    
    foreach ($param['options'] as $k=>$v) {
        $new_content_sms[$k] = GETPOST('SMSCONTENT_'.$k,'alpha');
    }
    
    $db->begin();
    $result=dolibarr_set_const($db, "SMSCOMMANDE_SMS_CONTENT",  serialize($new_content_sms),'chaine',0,'',$conf->entity);
    if (! $result > 0) $error++;
	
	if (! $error)
  	{
  		$db->commit();
                setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
  	}
  	else
  	{
  		$db->rollback();
		dol_print_error($db);
    }
}
$content_sms = unserialize($conf->global->SMSCOMMANDE_SMS_CONTENT) === FALSE ? array() : unserialize($conf->global->SMSCOMMANDE_SMS_CONTENT);
/*
 * Actions
 */
$form=new Form($db);

/*
 * View
 */
$page_name = "smsCommandeContentSetup";
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
	'contentsms',
	$langs->trans("Module432412Name"),
	0,
	"smscommande@smscommande"
);

// Setup page goes here
echo $langs->trans("smsCommandeContentSetupPage");




print '<br>';
print '<br>';

print '<table class="noborder" width="100%">';

$var=true;
print '<tr class="liste_titre">';
print '<td width="20%">'.$langs->trans("CommandeStatus").'</td>';
print '<td>'.$langs->trans("ContentSMS").'</td>';
print "</tr>\n";

foreach ($param['options'] as $k => $v) {
    $var=!$var;
    print '<tr '.$bc[$var].'><td>';
    print $v.'</td><td>';
    print '<div class="maxwidth500">';
    print '<textarea class="flat centpercent" rows="4" name="SMSCONTENT_'.$k.'">';
    print isset($content_sms[$k]) ? $content_sms[$k] : '';
    print '</textarea>';
    print '<br>';
    print '<div class="float smsHelp" data-textarea="SMSCONTENT_'.$k.'">';
    print '<span class="nbCharSMS">';
    print isset($content_sms[$k]) ? strlen($content_sms[$k]) : 0;
    print '</span> '.$langs->trans("character");
    print ' (<span class="nbSMS">';
    print isset($content_sms[$k]) ? ceil(strlen($content_sms[$k])/160) : 0;
    print '</span> SMS)';
    print '</div>';
    print '<div class="floatright">';
    print $form->textwithpicto($langs->trans("help"),$langs->trans("helpSMSContentDesc"));
    print '</div>';
    print '</div>';
    print '</td></tr>';
}

print '</table>';



// Page end
dol_fiche_end();

print '<div class="center"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></div>';

print '</form>';
llxFooter();
