<?php

/**
 *  @template       Talgos Backend-Theme
 *  @version        see info.php of this template
 *  @author         LEPTON project and others for Algos theme, LEPTON project for Talgos theme
 *	@copyright      2010-2024 LEPTON Project
 *  @license        GNU General Public License
 *  @license terms  see info.php of this template
 *
 */

// include secure.php to protect this file and the whole CMS!
if(!defined("SEC_FILE")){define("SEC_FILE",'/framework/secure.php' );}
if (defined('LEPTON_PATH')) {	
	include LEPTON_PATH.SEC_FILE;
} else {
	$oneback = "../";
	$root = $oneback;
	$level = 1;
	while (($level < 10) && (!file_exists($root.SEC_FILE))) {
		$root .= $oneback;
		$level += 1;
	}
	if (file_exists($root.SEC_FILE)) { 
		include $root.SEC_FILE;  
	} else {
		trigger_error(sprintf("[ <b>%s</b> ] Can't include secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
	}
}
// end include secure file


 
//Modul Description
$template_description 	= 'Ein erweitertes Backend-Theme für LEPTON CMS';

$THEME = [
	'ADMIN_ONLY' 		 => 'Einstellungen nur für Administratoren',
	'BACKEND_ACCESS' => 'Backend-Zugang',
	'COPY' 				 => 'Kopieren',
	'NO_SHOW_THUMBS' 	 => 'Thumbnails verbergen',
	'TEXT_HEADER' 		 => 'Maximale Größe für den Upload von Bildern einstellen<br><small><i>(gilt nur für neue Uploads)</i></small>',
	'CHANGE_LANGUAGE_NOTICE' => 'Bitte gehen Sie auf "Einstellungen", um die verwendete Seitensprache zu ändern',
	'CANNOT_DELETE'		 => 'Löschen nicht möglich - User hat statusflags 32.',
	'NOTICE'			 => 'Achtung:',
	'PHP_INFO'			 => 'Die installierte PHP Version ist: ',
	'PHP_VERSION'		 => 'PHP-Version',
	'UPDATE' 			 => 'Eine neuere LEPTON Version ist verfügbar! Aktuelle Version: ',
	'WYSIWYG_HISTORY' 	 => 'WYSIWYG Historie',
	'SECTION_ID' 		 => 'ID',
	'SECTION_NAME' 		 => 'Name'
];
