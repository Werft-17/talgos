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


// Make sure people are allowed to access this page
if ( MANAGE_SECTIONS != 'true')
{
	header('Location: '.ADMIN_URL.'/pages/index.php');
	exit(0);
}

$database = LEPTON_database::getInstance();
$oREQUEST = LEPTON_request::getInstance();

// input validation on $_GET
$input_fields = [
	  'page_id'		=> ['type' => 'integer', 'default' => -1]
];

$valid_fields = $oREQUEST->testGetValues($input_fields);
$page_id	= intval($valid_fields['page_id']);
$section_id = 0;
$job = "";

// input validation on $_POST
if( isset($_POST['job']) )
{
	$input_fields = [
		'job'			=> ['type' => 'string_clean', 'default' => ''],
		'section_id'	=> ['type' => 'integer+', 'default' => -1],
		'module'		=> ['type' => 'integer+', 'default' => -1]
	];
	
	$valid_fields = $oREQUEST->testPostValues($input_fields);
	$job		= $valid_fields['job'];
	$section_id	= intval($valid_fields['section_id']);
	$addon_id	= $valid_fields['module']; 
}

// Check page id
if ( $page_id < 0 )
{
	header("Location: index.php");
	exit(0);
}
else
{
	// does this page really exists? if yes get page details
	$current_page = [];
	$sql = "SELECT * from `".TABLE_PREFIX."pages` where `page_id` = ".$page_id;
	$database->execute_query( $sql, true, $current_page, false );
	if ( empty($current_page) )
	{
		header("Location: index.php");
		exit(0);
	}

	// does this page & section really exists?
	if ( $section_id > 0 )
	{
		$temp_result = [];
		$sql = "SELECT `section_id` from `".TABLE_PREFIX."sections` where `page_id` = ".$page_id." and `section_id` = ".$section_id;
		$dbsuccess = $database->execute_query( $sql, true, $temp_result, false );
		if (empty($temp_result) )
		{
			header("Location: index.php");
			exit(0);
		}
		unset($temp_result);
	}
}

// get classes instance
$oTWIG = lib_twig_box::getInstance();
$admin = LEPTON_admin::getInstance('Pages', 'pages_modify');
$TEXT = LEPTON_core::getGlobal("TEXT");
$MESSAGE = LEPTON_core::getGlobal("MESSAGE");

// get admin rights if assigned
$bHasAdminPrivilegs = LEPTON_admin::userHasAdminRights();

// Job processing
switch( $job )
{
	case "delete":
		// nothing to do if section is missing
		if ( $section_id < 0 )	{ break; }

		// get section defaults
		$section_info = [];
		$sql = "SELECT `module` FROM `".TABLE_PREFIX."sections` WHERE `section_id` = ".$section_id;
		$dbsuccess = $database->execute_query( $sql, true, $section_info, false );
		if ( empty($section_info) )
		{
			$admin->print_error('Section not found');
		}

		// Call "delete.php" of the module
		$look_for_path = LEPTON_PATH.'/modules/'.$section_info['module'].'/delete.php';
		if ( file_exists($look_for_path))
		{
			global $page_id, $section_id; // required for deletion inside modules
			require( $look_for_path );
		}
		unset($section_info);

		// in case deleted section is set as last edit section, reset the session setting
		if (( isset($_SESSION['last_edit_section'])) && ( $_SESSION['last_edit_section'] == $section_id ))
		{
			unset($_SESSION['last_edit_section']);
		}

		// delete the section itself
		if ( $database->simple_query("DELETE FROM `".TABLE_PREFIX."sections` WHERE `section_id` = ".$section_id) )
		{
			// since 3.0.1 we use  LEPTON_order
			$order = LEPTON_order::getInstance(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
			$order->clean($page_id);

			$look_for_path = THEME_URL.'/backend/backend/pages/sections.php';
			if ( ! file_exists($look_for_path))	{ $look_for_path = ADMIN_URL.'/pages/sections.php'; }

			$admin->print_success($TEXT['SUCCESS']."\nDelete section ".$section_id, $look_for_path.'?page_id='.$page_id);
			$admin->print_footer();
			exit();
		}
		break;

	case "add":
		// nothing to do if section is missing
		if ( $addon_id < 0 )	{ break; }

		// Is the module addon-id valide? Or in other words: does the module(-name) exists?
		$temp_result = [];
		$database->execute_query( 
			"SELECT `name`, `directory` from `".TABLE_PREFIX."addons` where `addon_id` = ".$addon_id,
			true, 
			$temp_result, 
			false 
		);
		
		if (empty($temp_result) )
		{
			$admin->print_error($MESSAGE['GENERIC_MODULE_VERSION_ERROR']." [1]");
		}

		$module = $temp_result['directory'];
		unset($temp_result);

		// Got the current user the rights to "use" this module?
		if ( (false === $bHasAdminPrivilegs) && (false === in_array($module, $_SESSION['MODULE_PERMISSIONS'] )) )
		{
			$admin->print_error($MESSAGE['GENERIC_NOT_UPGRADED']." [2]");
		}

		// Include the ordering class
		$order = LEPTON_order::getInstance(TABLE_PREFIX.'sections', 'position', 'section_id', 'page_id');
		$position = $order->get_new($page_id);

		// Insert dection into DB
		$fields = [
			'page_id'	=> $page_id,
			'module'	=> $module,
			'position'	=> $position,
			'name'		=> 'no name',
			'block'		=> 1		// Attention: insert a new module-section here at block 1, see info.php
		];
		
		$database->build_and_execute( 'insert', TABLE_PREFIX.'sections', $fields );

		// Get the section id
		$section_id = $database->get_one("SELECT LAST_INSERT_ID()");

		// Call "add.php" of the module
		$look_for_path = LEPTON_PATH.'/modules/'.$module.'/add.php';
		if ( file_exists($look_for_path))
		{
			require( $look_for_path );
		}

		break;
}


// Get display name of person who last modified the page
$user = $admin->get_user_details($current_page['modified_by']);

// Convert the unix ts for modified_when to human readable form
$modified_ts = ($current_page['modified_when'] != 0)
	? date(TIME_FORMAT.', '.DATE_FORMAT, $current_page['modified_when'])
	: 'Unknown'	;

// Get permissions
$admin_groups = explode(',', $current_page['admin_groups']);
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


//	Get all pages as (array-) tree
LEPTON_handle::register( "page_tree" );

$all_pages = [];
$fields = ['page_id','page_title','menu_title','parent','position','visibility','link'];
page_tree( 0, $all_pages, $fields );

// Try to include the info.php from the current (page-) template
if(isset($block))
{
	unset($block);
}

require( LEPTON_PATH.'/templates/'.(($current_page['template'] != '') ? $current_page['template'] : DEFAULT_TEMPLATE).'/info.php' );

if(!isset($block[1]) OR $block[1] == '')
{
	// Make our own menu list
	$block[1] = $TEXT['MAIN'];
}

// Get all sections of this page for backend dropdown list
$all_sections = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."sections` WHERE `page_id` = ".$page_id." ORDER BY `position` ASC", 
	true, 
	$all_sections, 
	true 
);


$show_add = 1;
foreach($all_sections as $sec_temp)
{
	if($sec_temp['module'] == 'menu_link')
	{
		$show_add = 0;
		break;
	}	
}


// Get all page-modules for backend dropdown list
$sSelectAddition = "";
if( false === $bHasAdminPrivilegs )
{
	$sSelectAddition = "AND `name` IN ('".(implode("','", $_SESSION['MODULE_PERMISSIONS']) )."')";
}

$all_page_modules = [];
$database->execute_query(
	"SELECT `name`,`addon_id` FROM `".TABLE_PREFIX."addons` WHERE `function`='page' ".$sSelectAddition." ORDER BY `name`",
	true,
	$all_page_modules,
	true
);

if (empty($all_page_modules))	{ exit(0); }


//  Get user date format here.
$sTempDateFormat = $database->get_one("SELECT `date_format` FROM `".TABLE_PREFIX."users` WHERE `user_id`=".$_SESSION["USER_ID"]);
if( ( NULL != $sTempDateFormat ) && (strlen($sTempDateFormat) > 0) )
{
	// keep it as defined
} 
else 
{
	// use the currend date_format setting
	$sTempDateFormat = DATE_FORMAT;
}

//  Signal-Signature
LEPTON_handle::register("random_string");
$sSignature = random_string(12);

$page_vars = [
	 'page'					=> $current_page,
	 'MODIFIED_BY'			=> $user['display_name'],
	 'MODIFIED_BY_USERNAME' => $user['username'],
	 'MODIFIED_WHEN'		=> $modified_ts,
	 'SEC_ANCHOR'			=> SEC_ANCHOR,
	 'SECTION_BLOCKS'		=> SECTION_BLOCKS,
	 'show_add'				=> $show_add,	 
	 'leptoken'				=> get_leptoken(),
	 'allowedPageSettings'	=> ( (false === $bHasAdminPrivilegs) ? LEPTON_admin::getUserPermission("page_settings") : true ),
	 'all_pages'			=> $all_pages,
	 'all_sections'			=> $all_sections,
	 'all_page_modules'		=> $all_page_modules,
	 'blocks'				=> $block,
	 'DATE_FORMAT'			=> $sTempDateFormat,
	 'DATEPICKER_FORMAT'	=> lib_lepton::getToolInstance("datetools")->formatToDatepicker( $sTempDateFormat ),
	 'signatur'				=> $sSignature
];

$oTALG = talgos::getInstance();
$page_vars['oTALG'] = $oTALG;

echo $oTWIG->render(
	"@theme/pages_sections.lte",
	$page_vars
);

$admin->print_footer();
