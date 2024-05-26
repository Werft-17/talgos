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

if(!isset($_POST['modify']) OR !is_numeric($_POST['user_id']) OR $_POST['user_id'] == 1) 
{
	header("Location: index.php");
	exit(0);
}

// Set parameter 'action' as alternative to javascript mechanism
if(isset($_POST['modify']))
{
	$_POST['action'] = "modify";
}

// get classes instance
$admin = LEPTON_admin::getInstance();
$oTWIG = lib_twig_box::getInstance();
LEPTON_handle::register('directory_list','random_string');

//	Get all users
$current_user = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."users` WHERE `user_id` = ".$_POST['user_id']." ORDER BY `display_name`,`username`",
	true,
	$current_user,
	false
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
$all_groups[] = [
	'group_id' => 1,
	'name'	=> "Administrators"
];

// get names of groups for current_user
$user_groups = [];
$groups_names = explode(",",$current_user['groups_id']);

foreach ($groups_names as $group_temp) 
{
	 $temp_name = $database->get_one("SELECT `name` FROM `".TABLE_PREFIX."groups` WHERE `group_id` = ".$group_temp."" );
	 $user_groups[$group_temp] =  $temp_name;
}


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

$page_values = [
	'alternative_url'	=> THEME_URL."/backend/backend/users/",
	'action_url'	=> ADMIN_URL."/users/",	
	'perm_modify'	=> $admin->get_permission('users_modify'),
	'perm_delete'	=> $admin->get_permission('users_delete'),
	'perm_add'		=> $admin->get_permission('users_add'),	
	'all_groups' => $all_groups,
	'user_groups' => $groups_names,
	'media_dirs' => $media_dirs,
	'form_name'	=> "user_".random_string(16),
	'username_fieldname' => $username_fieldname,
	'current_user'	=> $current_user,
	'hash'	=> $hash
];

echo $oTWIG->render(
	"@theme/users_modify.lte",
	$page_values
);
 
$admin->print_footer();
