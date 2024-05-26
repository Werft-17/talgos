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
$database = LEPTON_database::getInstance();
$oTWIG = lib_twig_box::getInstance();
LEPTON_handle::register('directory_list','random_string');

//	Get all users
$all_users = [];
$database->execute_query(
	"SELECT `user_id`,`username`,`display_name` FROM `".TABLE_PREFIX."users` WHERE `user_id` != '1' ORDER BY `display_name`,`username`",
	true,
	$all_users,
	true
);

//	Get all groups
$all_groups = [];
$database->execute_query(
	"SELECT `group_id`,`name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` != '1'",
	true,
	$all_groups,
	true
);

// Add "administrators" to the groups
$all_groups[] = array(
	'group_id' => 1,
	'name'	=> "Administrators"
);
 

//	Generate an unique username field name
$username_fieldname = 'username_'.random_string(16);

//	Generate a unique hash for the js-ajax-calls
$hash = [
	'h_name' => random_string(16),
	'h_value' => random_string(24)
];

//	Set the session vars for the hash
$_SESSION['backend_users_h'] = $hash['h_name'];
$_SESSION['backend_users_v'] = $hash['h_value'];


$media_dirs = [];
$skip = LEPTON_PATH.MEDIA_DIRECTORY."/";
directory_list(
	LEPTON_PATH.MEDIA_DIRECTORY,
	false,
	0,
	$media_dirs,
	$skip
);


//	if no User is given, we construct an empty 'default' one for the 'add user' form here:
$user = [
	'user_id'	=> -1,
	'username'	=> "",
	'display_name'	=> "",
	'email'			=> "you@none.tld",
	'password'		=> "",
	'home_folder'	=> "",
	'time_format'	=> TIME_FORMAT,
	'date_format'	=> DATE_FORMAT,
	'active'		=> 1
];

$page_values = [
	'alternative_url'	=> THEME_URL."/backend/backend/users/",
	'action_url'	=> ADMIN_URL."/users/",	
	'perm_modify'	=> $admin->get_permission('users_modify'),
	'perm_delete'	=> $admin->get_permission('users_delete'),
	'perm_add'		=> $admin->get_permission('users_add'),	
	'all_users'	 	=> $all_users,
	'all_groups' => $all_groups,
	'media_dirs' => $media_dirs,
	'NEWUSERHINT'=> sprintf($TEXT['NEW_USER_HINT'], AUTH_MIN_LOGIN_LENGTH, AUTH_MIN_PASS_LENGTH),
	'form_name'	=> "user_".random_string(16),
	'username_fieldname' => $username_fieldname,
	'current_user'	=> $user,
	'hash'	=> $hash
];

echo $oTWIG->render(
	"@theme/users_add.lte",
	$page_values
);
 
$admin->print_footer();
