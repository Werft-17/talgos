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
 * @license         https://gnu.org/licenses/gpl.html
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

$leptoken = (isset($_GET['leptoken'])) ? "?leptoken=".$_GET['leptoken'] : "";
$backlink = "../modules/index.php".$leptoken;

if (!isset($_POST['module_id']) OR $_POST['module_id'] == "")
{
	header("Location: ".$backlink);
	exit(0);
} 
else 
{
	$module_id = intval($_POST['module_id']);
	
	if ($module_id == 0) {
		header("Location: ".$backlink);
		exit(0);
	}
}

// get classes instance
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTWIG = lib_twig_box::getInstance();

// get values
$current_module = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."addons` WHERE addon_id = ".$module_id." ",
	true,
	$current_module,
	false
);

				
switch ($current_module['function']) 
{
	case NULL:
		$type_name = $TEXT['UNKNOWN'];
		break;
	case 'page':
		$type_name = $TEXT['PAGE'];
		break;
	case 'wysiwyg':
		$type_name = $TEXT['WYSIWYG_EDITOR'];
		break;
	case 'tool':
		$type_name = $TEXT['ADMINISTRATION_TOOL'];
		break;
	case 'admin':
		$type_name = $TEXT['ADMIN'];
		break;
	case 'administration':
		$type_name = $TEXT['ADMINISTRATION'];
		break;
	case 'snippet':
		$type_name = $TEXT['CODE_SNIPPET'];
		break;
	case 'library':
		$type_name = $TEXT['LIBRARY'];	
		break;
	default:
		$type_name = $TEXT['UNKNOWN'];
}


// Insert language text and messages
$page_values = [
	'current'	=>$current_module,
	'type'		=>$type_name
];

echo $oTWIG->render(
	"@theme/modules_details.lte",
	$page_values
);

$admin->print_footer();
