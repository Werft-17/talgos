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


// get classes instance
$oTWIG = lib_twig_box::getInstance();
$admin = LEPTON_admin::getInstance();
$database = LEPTON_database::getInstance();
$oTALGOS = talgos::getInstance();

// Get all groups (inkl. 1 == Administrators
$all_groups = [];
$database->execute_query(
    "SELECT * FROM `".TABLE_PREFIX."groups`",
    true,
    $all_groups,
    true
);

// Get all page-modules
$all_page_modules = [];
$database->execute_query(
    "SELECT * FROM `".TABLE_PREFIX."addons` WHERE `type` = 'module' AND `function` = 'page' order by `name`",
    true,
    $all_page_modules,
    true
);

// start the page search
$search_result = [];
$title_checked   = 0;
$page_checked    = 0;
$section_checked = 0;
$addon_checked   = 0;

$search_performed = false;

if ( isset($_POST['search_scope']))
{
    $search_performed = true;

    switch( strtolower( $_POST['search_scope'] ))
    {
        case 'section':
            $section_checked = 1;
            //  Section_id as to be an integer.
            $iSearchTerm = intval( $_POST['terms'] );
            // find result
            $temp_page_id = intval($database->get_one("SELECT `page_id` FROM `".TABLE_PREFIX."sections` WHERE `section_id` = ".$iSearchTerm ));
            // query
            $sTempQuery = "SELECT * FROM `".TABLE_PREFIX."pages` WHERE `page_id` = ".$temp_page_id." ";
            break;

        case 'page':
            $page_checked = 1;
            $iSearchTerm = intval( $_POST['terms'] );
            // find result
            $sTempQuery  = "SELECT * from `".TABLE_PREFIX."pages` WHERE `page_id` = ".$iSearchTerm;
            break;

        case 'title':
            $title_checked = 1;
            $sSearchTerm = strip_tags( trim($_POST['terms']), "" );
            // handle quotes
            $sSearchTerm = str_replace(
                ["'", "\""],
                ["\'", "\\\""],
                $sSearchTerm
            );
            $sTempQuery  = "SELECT * from `".TABLE_PREFIX."pages` WHERE `page_title` LIKE '%".$sSearchTerm."%' ";
            break;

        case 'module':
        case 'addon':
            $addon_checked = 1;
            // AddOn name has to be a string
            $sSearchTerm = strip_tags( trim($_POST['terms']), "" );
            // handle quotes
            $sSearchTerm = str_replace(
                ["'", "\""],
                ["\'", "\\\""],
                $sSearchTerm
            );

            $sTempQuery  = ( "" <> $sSearchTerm )
                ? "SELECT * from `".TABLE_PREFIX."pages` AS p JOIN `".TABLE_PREFIX."sections` as s WHERE (`module` LIKE '%".($sSearchTerm)."%') AND (s.page_id = p.page_id)"
                : ""
                ;
                
            break;
        
        default:
            $sTempQuery = "";
            break;

    }

    if ($sTempQuery != "" )
    {
        // find result
        $database->execute_query(
            $sTempQuery, 
            true,
            $search_result,
            true
        );
    }    
} else {
    $title_checked   = 1;
}

//  Get all pages as (array-) tree
LEPTON_handle::register( "page_tree" );

$all_pages = [];

$fields = ['page_id','page_title','level','menu_title','parent','position','visibility','link'];

page_tree( 0, $all_pages, $fields );

$oTALGOS->setRememberState( $all_pages );

// preselect a page_id?
$preselect_page = (isset($_GET['page_id']) ? $_GET['page_id'] : 0 );

$page_values = [
    'oTALG'         => $oTALGOS,
	'MANAGE_SECTIONS' => MANAGE_SECTIONS,	
    'section_check' => $section_checked,
    'page_check'    => $page_checked,
    'title_check'   => $title_checked,
    'addon_checked' => $addon_checked,
    'search_values' =>  ($_POST['terms'] ?? ""),
    'perm_pages_add'=> $admin->get_permission('pages_add'),
    'all_groups'    => $all_groups,
    'all_page_modules' => $all_page_modules,
    'leptoken'      => get_leptoken(),
    'all_pages'     => $all_pages,
    'search_result' => $search_result,
    'search_performed' => $search_performed,
    'preselect_page'    => $preselect_page,
    'alternative_url'   => THEME_URL.'/backend/backend/pages/',
    'action_url'        => ADMIN_URL.'/pages/'
];

echo $oTWIG->render(
    "@theme/pages.lte",
    $page_values
);

$admin->print_footer();
