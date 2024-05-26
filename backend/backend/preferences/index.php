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

//	Initial page addition
$ref = initial_page::getInstance();
$info = $ref->get_user_info( $_SESSION['USER_ID'] );
	
$options = [
	'pages'			=> true,
	'tools'			=> in_array('1',LEPTON_core::getValue('groups_id','string','session',',')) ? true : false,
	'backend_pages' => in_array('1',LEPTON_core::getValue('groups_id','string','session',',')) ? true : false,
];
	
$select = $ref->get_single_user_select( $_SESSION['USER_ID'], 'init_page_select', $info['init_page'], $options);
	
$initial_page_language = $ref->get_language();
	

//	read user-info from table users
$current_user = [];
$database->execute_query(
	'SELECT * FROM `'.TABLE_PREFIX.'users` WHERE `user_id` = '.(int)$admin->getValue('user_id', 'integer', 'session').' ',	
	true,
	$current_user,
	false
);


// read available languages from table addons
$languages = [];
$database->execute_query(
	'SELECT `directory`,`name` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "language" ORDER BY `directory`',
	true,
	$languages,
	true
);

$page_values = [		
	'current_user'	=> $current_user,
	'languages'		=> $languages,
	'LANGUAGE'		=> LANGUAGE,
	'timezone_table'	=> LEPTON_basics::get_timezones(),
	'timezone'			=> isset( $_SESSION[ 'TIMEZONE_STRING' ] ) ? $_SESSION[ 'TIMEZONE_STRING' ] : DEFAULT_TIMEZONESTRING,
	'time_formats'		=> LEPTON_basics::get_timeformats(),
	'time_format'		=> TIME_FORMAT,
	'date_formats'		=> LEPTON_basics::get_dateformats(),
	'date_format'		=> DATE_FORMAT,
	'EMPTY_STRING'		=> '',
	'ACTION_URL' => ADMIN_URL.'/preferences/save.php',
	'FORM_NAME' => 'preferences_save',
	 //	Initial Page addition
	'INIT_PAGE_SELECT' => $select,
	'INIT_PAGE_LABEL' => $initial_page_language['label_default']
];
	
echo $oTWIG->render(
	"@theme/preferences.lte",
	$page_values
);

$admin->print_footer();
