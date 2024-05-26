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
$oTWIG = lib_twig_box::getInstance();

//	Pre-load the theme languages 
LEPTON_basics::getInstance();

global $TEXT, $MESSAGE, $OVERVIEW, $MENU, $THEME;
global $is_uptodate, $latest_release, $version_info;

// Check if install directory is deleted
if (file_exists(LEPTON_PATH.'/install/') )  
{
	$warning = LEPTON_tools::display('<br  />'.$THEME['NOTICE'].'<br  />'.$MESSAGE['START_INSTALL_DIR_EXISTS'].'<br />','pre','warning');
} 
else 
{
	$warning = '';
}


// Insert "Add-ons" section overview (pretty complex compared to normal)
$addons_overview = $TEXT['MANAGE'].' ';
$addons_count = 0;

if ($admin->get_permission('modules') == true)
{
	$addons_overview .= '<a href="'.ADMIN_URL.'/modules/index.php">'.$MENU['MODULES'].'</a>';
	$addons_count = 1;
}

if ($admin->get_permission('templates') == true)
{
	if($addons_count == 1) { $addons_overview .= ', '; }
	$addons_overview .= '<a href="'.ADMIN_URL.'/templates/index.php">'.$MENU['TEMPLATES'].'</a>';
	$addons_count = 1;
}

if ($admin->get_permission('languages') == true)
{
	if ($addons_count == 1) { $addons_overview .= ', '; }
	$addons_overview .= '<a href="'.ADMIN_URL.'/languages/index.php">'.$MENU['LANGUAGES'].'</a>';
}

// Insert "Access" section overview (pretty complex compared to normal)
$access_overview = $TEXT['MANAGE'].' ';
$access_count = 0;
if ($admin->get_permission('users') == true) {
	$access_overview .= '<a href="'.ADMIN_URL.'/users/index.php">'.$MENU['USERS'].'</a>';
	$access_count = 1;
}
if ($admin->get_permission('groups') == true) {
	if($access_count == 1) { $access_overview .= ', '; }
	$access_overview .= '<a href="'.ADMIN_URL.'/groups/index.php">'.$MENU['GROUPS'].'</a>';
	$access_count = 1;
}

if ($is_uptodate == false)  
{
	$lepton_release = LEPTON_tools::display('<br  />'.$THEME['UPDATE'].LEPTON_VERSION.'<br />'.$info,'pre','warning');
}

$page_values = [
//	'WELCOME_MESSAGE' => $MESSAGE['START_WELCOME_MESSAGE'],
//	'CURRENT_USER' => $MESSAGE['START_CURRENT_USER'],
	'DISPLAY_NAME'  => $admin->getValue('display_name', 'string', 'session'),
	'ADMIN_URL'     => ADMIN_URL,
	'LEPTON_URL'    => LEPTON_URL,
	'THEME_URL'     => THEME_URL,
	'NO_CONTENT'    => '<p>&nbsp;</p>',
	'WARNING'       => $warning,
	'LEPTON'        => $lepton_release ?? '',	
	// only with access
	'PHP'           => $THEME['PHP_VERSION'],
	'PHP_INFO'      => $THEME['PHP_INFO'],
	'PHP_NO' => PHP_VERSION,
	'PAGES' => $MENU['PAGES'],
	'MEDIA' => $MENU['MEDIA'],
	'ADDONS' => $MENU['ADDONS'],
	'ACCESS' => $MENU['ACCESS'],
	'PREFERENCES' => $MENU['PREFERENCES'],
	'SETTINGS' => $MENU['SETTINGS'],
	'ADMINTOOLS' => $MENU['ADMINTOOLS'],
	'HOME_OVERVIEW' => $OVERVIEW['START'],
	'PAGES_OVERVIEW' => $OVERVIEW['PAGES'],
	'MEDIA_OVERVIEW' => $OVERVIEW['MEDIA'],
	'ADDONS_OVERVIEW' => $addons_overview,
	'ACCESS_OVERVIEW' => $access_overview,
	'PREFERENCES_OVERVIEW' => $OVERVIEW['PREFERENCES'],
	'SETTINGS_OVERVIEW' => $OVERVIEW['SETTINGS'],
	'ADMINTOOLS_OVERVIEW' => $OVERVIEW['ADMINTOOLS']	
];

echo $oTWIG->render(
	"@theme/start.lte",
	$page_values
);

$admin->print_footer();
