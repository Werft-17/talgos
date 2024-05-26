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


// get classes instance
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTWIG = lib_twig_box::getInstance();


// [1] Get all Modules
$all_modules = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."addons` WHERE `type` = 'module' order by `name`",
	true,
	$all_modules,
	true
);


//	Look into the modules directory for manual_install/upgrade
$module_files = glob(LEPTON_PATH . '/modules/*', GLOB_ONLYDIR );

$modules_found = [];
foreach($module_files as &$mod) 
{
	$temp_install = $mod."/install.php";
	$temp_upgrade = $mod."/upgrade.php";
	
	if (file_exists($temp_install)) 
	{
		$modules_found[] = [
			'name'		=> basename( $mod ),
			'install'	=> $temp_install,
			'upgrade'	=> (file_exists($temp_upgrade)) ? $temp_upgrade : ''
		];
	}
}


//	Build secure-hash for the js-calls
LEPTON_handle::register('random_string');

$hash = [
	'h_name' => random_string(16),
	'h_value' => random_string(24)
];

$_SESSION['backend_module_h'] = $hash['h_name'];
$_SESSION['backend_module_v'] = $hash['h_value'];

	
$page_values = [
	'url_templates'	=> $admin->get_permission('templates'),
	'url_languages'	=> $admin->get_permission('languages'), 
	'perm_admin'	=> $admin->get_permission('admintools'),
	'perm_install'	=> $admin->get_permission('modules_install'),
	'perm_uninstall'=> $admin->get_permission('modules_uninstall'),
	'perm_view'		=> $admin->get_permission('modules_view'),
	'action_url'	=> ADMIN_URL."/modules/",
	'all_modules' 	=> $all_modules,
	'alternative_url'	=> THEME_URL."/backend/backend/modules/",
	'modules_found'		=> $modules_found,	// All modules inside the modules directory.
	'hash'	=> $hash,
	'is_advanced'   => (isset($_GET['advanced']))
];

$oTWIG->registerPath( THEME_PATH."/templates","modules" );
echo $oTWIG->render(
	"@theme/modules.lte",
	$page_values
);

// Print admin footer
$admin->print_footer();
