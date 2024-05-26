<?php

/**
 * This file is part of LEPTON Core, released under the GNU GPL
 * Please see LICENSE and COPYING files in your package for details, specially for terms and warranties.
 * 
 * NOTICE:LEPTON CMS Package has several different licenses.
 * Please see the individual license in the header of each single file or info.php of modules and templates.
 *
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


// Get classesinstance
$admin = LEPTON_admin::getInstance();
$oTWIG = lib_twig_box::getInstance();
$database = LEPTON_database::getInstance();

//  [1] Get all templates
$all_templates = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."addons` WHERE `type` = 'template' order by `name`",
	true,
	$all_templates,
	true
);


/**
 *	Collecting all page values here
 */
$page_values = [
	'all_templates'	=> $all_templates,
	'url_modules'	=> $admin->get_permission('modules'),
	'url_languages'	=> $admin->get_permission('languages'), 
	'perm_admin'	=> $admin->get_permission('admintools'),
	'perm_install'	=> $admin->get_permission('templates_install'),
	'perm_uninstall'=> $admin->get_permission('templates_uninstall'),
	'perm_view'		=> $admin->get_permission('templates_view'),
	'action_url'	=> ADMIN_URL."/templates/",
	'alternative_url'	=> THEME_URL."/backend/backend/templates/",
	'leptoken'      => get_leptoken()
];

echo $oTWIG->render(
	"@theme/templates.lte",
	$page_values
);

$admin->print_footer();
