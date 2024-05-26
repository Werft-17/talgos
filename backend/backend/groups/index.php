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

global $THEME;

// get classes instance
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTWIG = lib_twig_box::getInstance();


//	Get all groups
$all_groups = [];
$database->execute_query(
	"SELECT `group_id`,`name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` != '1' ORDER BY `name`",
	true,
	$all_groups,
	true
);

//	Get all templates
$all_templates = [];
$database->execute_query(
	'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" ORDER BY `name`',
	true,
	$all_templates,
	true
);

//	Get all modules
$all_modules = [];
$database->execute_query(
	'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "page" ORDER BY `name`',
	true,
	$all_modules,
	true
);

//	Get all admin-tools
$all_tools = [];	
$database->execute_query(
	'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "tool" ORDER BY `name`',
	true,
	$all_tools,
	true
);

$page_values = [
	'THEME' => $THEME,
	'alternative_url'	=> THEME_URL."/backend/backend/groups/",
	'action_url'	=> ADMIN_URL."/groups/",	
	'perm_modify'	=> $admin->get_permission('groups_modify'),
	'perm_delete'	=> $admin->get_permission('groups_delete'),
	'perm_add'		=> $admin->get_permission('groups_add'),
	'group_id'		=> -1,
	'all_groups'	=> $all_groups,
	'all_tools'		=> $all_tools,
	'all_modules'	=> $all_modules,	
	'all_templates'	=> $all_templates,
	'last_saved_group_id'   => ($_SESSION['last_saved_group_id'] ?? 0)
];


echo $oTWIG->render(
	"@theme/groups_add.lte",
	$page_values
);
 
$admin->print_footer();
