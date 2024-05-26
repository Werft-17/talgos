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

global $TEXT;

// get classes instance
$admin = LEPTON_admin::getInstance();
$oTWIG = lib_twig_box::getInstance();

$display_none = "style=\"display: none;\"";
if(!isset($_GET['advanced']) || $admin->get_permission('admintools') != true)
{
 $display = $display_none;

} 
else 
{
	$display = '';
}

//	Insert section names and descriptions
$page_values = [
	'TEXT_RELOAD'	=> $TEXT['RELOAD'],
	'RELOAD_URL'	=> ADMIN_URL . '/addons/reload.php',
	'url_advanced'	=> $admin->get_permission('admintools')
		? '<a href="' . ADMIN_URL . '/addons/index.php?advanced">' . $TEXT['ADVANCED'] . '</a>' 
		: ''
		,
	'display_modules'	=> $admin->get_permission('modules'),
	'display_templates' => $admin->get_permission('templates'),
	'display_languages' => $admin->get_permission('languages'),	
	'display_reload' 	=> $display
];

echo $oTWIG->render(
	"@theme/addons.lte",
	$page_values
);

$admin->print_footer();
