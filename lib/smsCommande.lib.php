<?php
/* SMS Commande - Send SMS since ORDER card
 * Copyright (C) 2017       Inovea-conseil.com     <info@inovea-conseil.com>
 */

/**
 * \file    lib/smsCommande.lib.php
 * \ingroup smscommande
 * \brief   ActionsSmsCommande
 *
 * Show admin header
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function smsCommandeAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("smscommande@smscommande");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/smscommande/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;
        $head[$h][0] = dol_buildpath("/smscommande/admin/smscontent.php", 1);
	$head[$h][1] = $langs->trans("ContentSMS");
	$head[$h][2] = 'contentsms';
	$h++;
        $head[$h][0] = dol_buildpath("/smscommande/admin/smscommandehistory_list.php", 1);
	$head[$h][1] = $langs->trans("History");
	$head[$h][2] = 'historysms';
	$h++;
        
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'smscommande');

	return $head;
}