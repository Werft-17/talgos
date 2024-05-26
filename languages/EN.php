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


 
//Modul Description
$template_description 	= 'Enhanced backend theme for LEPTON CMS';

$THEME = [
    'ADMIN_ONLY'         => 'Settings for administrator only',
    'BACKEND_ACCESS' => 'Backend Access',
    'COPY'               => 'Copy',
    'NO_SHOW_THUMBS'     => 'Hide thumbnails',
    'TEXT_HEADER'        => 'Set maximum imagesize for a folder<br><small><i>(resizing on new uploads only)</i></small>',
    'CHANGE_LANGUAGE_NOTICE' => 'Please note: to change the site language you must go to the "Settings" section',
    'CANNOT_DELETE'      => 'Cannot delete User - User got statusflags 32.',
    'NOTICE'             => 'NOTICE:',
    'PHP_INFO'           => 'Your current PHP Version is: ',
    'PHP_VERSION'        => 'PHP Version',
    'UPDATE'             => 'A later LEPTON version is released! Current Version: ',
    'WYSIWYG_HISTORY'    => 'WYSIWYG History',
    'SECTION_ID'         => 'ID',
    'SECTION_NAME'       => 'Name'
];
