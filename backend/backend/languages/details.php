<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
 * @author          LEPTON Project
 * @copyright       2010-2024 LEPTON Project
 * @link            https://lepton-cms.org
 * @license         https://gnu.org/licenses/gpl-3.0.html
 * @license_terms   please see LICENSE and COPYING files in your package
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


$leptoken = (isset($_GET['leptoken'])) ? $_GET['leptoken'] : "";

// Get language name
if(!isset($_POST['code']) OR $_POST['code'] == "") 
{
	header("Location: index.php?leptoken=".$leptoken);
	exit(0);
} 
else 
{
	$code = $_POST['code'];
}

// fix secunia 2010-93-2
if (!preg_match('/^[A-Z]{2}$/', $code)) 
{
	header("Location: index.php?leptoken=".$leptoken);
	exit(0);
}

// get classes instance
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTWIG = lib_twig_box::getInstance();


// get values
$current_language = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."addons` WHERE type = 'language' AND directory = '".$code."'",
	true,
	$current_language,
	false
);
	
// Insert language text and messages
$page_values = ['current'	=>$current_language ];

echo $oTWIG->render(
	"@theme/languages_details.lte",
	$page_values
);

$admin->print_footer();
