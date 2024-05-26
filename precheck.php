<?php

/**
 *  @template       Talgos Backend-Theme
 *  @version        see info.php of this template
 *  @author         LEPTON project and others for Algos theme, LEPTON project for Talgos theme
 *  @copyright      2010-2024 LEPTON Project 
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

// Checking Requirements

$PRECHECK['LEPTON_VERSION'] = [
    'LEPTON_VERSION' => '7.2',
    'OPERATOR'       => '>='
];

$PRECHECK['ADDONS'] = [
    'lib_twig' => [
        'VERSION'   => '3.0',
        'OPERATOR'  => '>='
    ]
];
