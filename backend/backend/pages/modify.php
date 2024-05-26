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


$oREQUEST = LEPTON_request::getInstance();
$database = LEPTON_database::getInstance();
$TEXT = LEPTON_core::getGlobal("TEXT");

// input validation on $_GET
$input_fields = [
	'page_id'		=> ['type' => 'integer', 'default' => -1],
	'section_id'	=> ['type' => 'integer', 'default' => -1]
];

$valid_fields = $oREQUEST->testGetValues($input_fields);
$page_id	= intval($valid_fields['page_id']);
$section_mod= intval($valid_fields['section_id']);

$display_details = true;

// Create new LEPTON_admin object
$admin = LEPTON_admin::getInstance();

	// Get permissions
	if (! $admin->get_page_permission($page_id,'admin'))
	{
		$admin->print_error($MESSAGE['PAGES_INSUFFICIENT_PERMISSIONS']);
	}

	// Get page details
	$current_page = $admin->get_page_details($page_id);

	// Get display name of person who last modified the page
	$user = $admin->get_user_details($current_page['modified_by']);


//	Get all pages as (array-) tree
LEPTON_handle::register( "page_tree" );

$all_pages = [];
$fields = ['page_id','page_title','menu_title','parent','position','visibility','link'];
page_tree( 0, $all_pages, $fields );

// get template used for the displayed page (for displaying block details)
if ( SECTION_BLOCKS )
{
	$result = $database->get_one("SELECT `template` from `" . TABLE_PREFIX . "pages` WHERE `page_id` = ".$page_id);
	if ($result != NULL) 
	{
		$page_template = ($result == '') ? DEFAULT_TEMPLATE : $result;
		// include template info file if exists
		if (file_exists(LEPTON_PATH . '/templates/' . $page_template . '/info.php'))
		{
			include_once(LEPTON_PATH . '/templates/' . $page_template . '/info.php');
		}
	}
}

// check for admin rights
$temp = $admin->getValue('groups_id', 'string', 'session',',');
if(!is_array($temp))
{
	$bHasAdminPrivilegs = ($temp == 1) ? true : false;
} 
else 
{
	$bHasAdminPrivilegs = ( true == in_array( 1, $temp ) ) ? true : false;
}

// Get all sections for this page
$all_sections = [];
$database->execute_query(
	'SELECT `section_id`, `module`, `block`, `name` FROM `'.TABLE_PREFIX.'sections` WHERE `page_id` = '.$page_id.' ORDER BY `position` ASC',
	true,
	$all_sections,
	true
);

// build page detail
$all_section_ids = array();
if ( !empty($all_sections) )
{

	foreach( $all_sections as &$section)
	{
		global $section_id;
		$section_id = $section['section_id'];

		// collect section_id
		$all_section_ids[] = $section_id;

		// Have permission?
		$module = $section['module'];
		if( (true == $bHasAdminPrivilegs) || (is_numeric(array_search($module, $_SESSION['MODULE_PERMISSIONS']))))
		{
			// Include the modules editing script if it exists
			if ( file_exists(LEPTON_PATH.'/modules/'.$module.'/modify.php'))
			{
				if (isset($block[$section['block']]) && trim(strip_tags(($block[$section['block']]))) != '')
				{
					$section['block_name'] = htmlentities(strip_tags($block[$section['block']]));
				} 
				else
				{
					if ($section['block'] == 1)
					{
						$section['block_name'] = $TEXT['MAIN'];
					} 
					else
					{
						$section['block_name'] = '#' . (int) $section['block'];
					}
				}

				ob_start();
				require LEPTON_PATH.'/modules/'.$module.'/modify.php';
				$section['content'] = ob_get_clean();
			}
		}
	}

	//  handle last edit section
	if (( $section_mod >= 0 ) && ( in_array( $section_mod, $all_section_ids )))
	{
		// use the section id passed from section maintenance
		$_SESSION['last_edit_section'] = $section_mod;
	}
	elseif (( ! isset($_SESSION['last_edit_section']) ) || ( ! in_array( $_SESSION['last_edit_section'], $all_section_ids )))
	{
		// last section is not set or invalid: use first section
		$_SESSION['last_edit_section'] = $all_section_ids[0];
	}

} 
else 
{
	//  No sections on this page
	$_SESSION['last_edit_section'] = 0;
}

// Collect vars
$page_values = [
    'page'                  => $current_page,
    'MODIFIED_BY'           => $user['display_name'],
    'MODIFIED_BY_USERNAME'  => $user['username'],
    'MODIFIED_WHEN'         => talgos::formatTime($current_page['modified_when']),
    'SEC_ANCHOR'            => SEC_ANCHOR,
    'MANAGE_SECTIONS'       => MANAGE_SECTIONS,
    'leptoken'              => get_leptoken(),
    'last_edit_section'     => $_SESSION['last_edit_section'],
    'allowedPageSettings'   => ( (false == $bHasAdminPrivilegs) ? LEPTON_admin::getUserPermission("page_settings") : true ),
    'all_pages'             => $all_pages,
    'all_sections'          => $all_sections,
    'display_details'       => $display_details,
    'oTALG'                 => talgos::getInstance() // TALGOS enhancement
];

// get TWIG engine
$oTWIG = lib_twig_box::getInstance();

echo $oTWIG->render(
    "@theme/pages_modify.lte",
    $page_values
);

$admin->print_footer();
