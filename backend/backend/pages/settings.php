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


// Get page id
if(!isset($_GET['page_id']) OR !is_numeric($_GET['page_id']))
{
	header("Location: index.php");
	exit(0);
} else {
	$page_id = intval($_GET['page_id']);
}

// get classes instance
$oTWIG = lib_twig_box::getInstance();
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTALG = talgos::getInstance();

// Get perms and page_details
$aCurrentPageInfo = [];
$database->execute_query(
	'SELECT * FROM `'.TABLE_PREFIX.'pages` WHERE `page_id` = '.$page_id,
	true,
	$aCurrentPageInfo,
	false
);

if(empty($aCurrentPageInfo)) 
{
	$admin->print_error($MESSAGE['PAGES_NOT_FOUND']);
}

$admin_groups = explode(',', $aCurrentPageInfo['admin_groups']);
$in_group = false;

foreach($admin->getValue('groups_id', 'string', 'session',',') as $sCurrentGroupId)
{
    if (in_array($sCurrentGroupId, $admin_groups))
    {
        $in_group = true;
    }
}

if($in_group == false)
{
    $admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
}

// Get display name of person who last modified the page
$user=$admin->get_user_details($aCurrentPageInfo['modified_by']);

$lepton_core_all_groups = [];
$database->execute_query(
	'SELECT * FROM `'.TABLE_PREFIX.'groups`',
	true,
	$lepton_core_all_groups,
	true
);

/**
 *	Get all pages as (array-) tree
 */
LEPTON_handle::register("page_tree");

//	Storage for all infos in an array
$all_pages = [];

//	Determinate what fields/keys we want to get in our 'page_tree'-array
$fields = array('page_id','page_title','menu_title','parent','position','visibility','link');

//	Get the tree here
page_tree( 0, $all_pages, $fields );

/**
 *	Get all installed languages
 */
$all_languages = [];
$database->execute_query(
	'SELECT `directory`,`name` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "language" ORDER BY `name`',
	true,
	$all_languages,
	true
);

/**
 *	Get all installed templates
 */
$all_templates = [];
$database->execute_query(
	'SELECT `directory`,`name`,`function` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" AND (`function` = "template" OR `function`="") order by `name`',
	true,
	$all_templates,
	true
);

/**
 *	Try to get the correct Menu
 */
$temp = ($aCurrentPageInfo['template'] == "") ? DEFAULT_TEMPLATE : $aCurrentPageInfo['template'];
$temp_path = LEPTON_PATH."/templates/".$temp."/info.php";
require_once $temp_path;
$all_menus = (isset($menu) ? $menu : [] );
 
$page_values = array(
	'oTALG' 	=> $oTALG,	
	'PAGE_ID' 	=> $page_id,
	'aCurrentPageInfo'	=> $aCurrentPageInfo,
	'page_values'	=> $aCurrentPageInfo, // !
	'page'          => $aCurrentPageInfo, // !
	'page_link'		=> substr($aCurrentPageInfo['link'],strripos($aCurrentPageInfo['link'],'/')+1),
	'PAGE_EXTENSION'=> PAGE_EXTENSION,
	'LANGUAGE' 		=> LANGUAGE,
	'PAGE_LANGUAGES'=> PAGE_LANGUAGES,	
	'leptoken'		=> get_leptoken(),
	'all_groups'	=> $lepton_core_all_groups,
	'all_pages'		=> $all_pages,
	'all_languages' => $all_languages,
	'all_templates' => $all_templates,
	'all_menus'		=> $all_menus,

	'DESCRIPTION' => $aCurrentPageInfo['description'],
	'KEYWORDS' => $aCurrentPageInfo['keywords'],
    'PAGE_CODE' => $aCurrentPageInfo['page_code'],
	'MODIFIED_BY' => $user['display_name'],
	'MODIFIED_BY_USERNAME' => $user['username'],
	'MODIFIED_WHEN' => talgos::formatTime($aCurrentPageInfo['modified_when']),
	'PAGE_LANGUAGE' => $aCurrentPageInfo['language']	
);

// $oTWIG->registerPath( THEME_PATH."theme","pages_settings" );
echo $oTWIG->render(
	"@theme/pages_settings.lte",
	$page_values
);

$admin->print_footer();
