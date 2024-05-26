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
$database = LEPTON_database::getInstance();
$admin = LEPTON_admin::getInstance();
$oTWIG = lib_twig_box::getInstance();

// test for admin-rights
$temp = $admin->getValue('groups_id', 'string', 'session',',');
if(!is_array($temp))
{
    $bIsAdmin = ($temp == 1) ? true : false;
} 
else 
{
    $bIsAdmin = ( true == in_array( 1, $temp ) ) ? true : false;
}

$all_tools = [];
$database->execute_query(
	"SELECT `directory`,`name`,`description` FROM `".TABLE_PREFIX."addons` WHERE `type` = 'module' AND `function` = 'tool' AND `directory` ".($bIsAdmin == true ? 'not' : '')." in ('".(implode("','",$_SESSION['MODULE_PERMISSIONS']))."') order by `name`",
	true,
	$all_tools,
	true	
);

foreach($all_tools as &$tool) 
{
	// check if a module description exists for the displayed backend language
	$module_description = false;
	$language_file = LEPTON_PATH.'/modules/'.$tool['directory'].'/languages/'.LANGUAGE .'.php';
	if(true === file_exists($language_file)) 
	{
		require( $language_file );
	}
	
	if ($module_description !== false) 
	{
		$module_description = str_replace(
			["{LEPTON_URL}", "{{ LEPTON_URL }}"],
			LEPTON_URL,
			$module_description
		);
		$tool['description'] = $module_description;
	}
}

$page_values = [
	'TOOL_NONE_FOUND' => (empty($all_tools)) ? $TEXT['NONE_FOUND'] : "",
	'all_tools'	=> $all_tools
];

echo $oTWIG->render(
	"@theme/admintools.lte",
	$page_values
);
 
$admin->print_footer();
