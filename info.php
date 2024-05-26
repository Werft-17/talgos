<?php

/**
 *  @template       Talgos  Backend-Theme
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


// OBLIGATORY VARIABLES
$template_directory		= 'talgos';
$template_name			= 'Talgos Theme';
$template_function		= 'theme';
$template_delete  		=  true;
$template_version		= '1.2.8';
$template_platform		= '7.x';
$template_author		= 'LEPTON project';
$template_license		= '<a href="https://gnu.org/licenses/gpl.html">GNU General Public License</a>';
$template_license_terms	= '-';
$template_description	= 'Backend theme for LEPTON CMS';
$template_guid			= '60de3b94-3659-451c-a876-b9e17b49784b';
