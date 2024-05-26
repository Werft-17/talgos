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
 * @license         https://gnu.org/licenses/gpl.html
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


// Get template name
if(!isset($_POST['file']) OR $_POST['file'] == "") 
{
	$add = (isset($_GET['leptoken']) ? "?leptoken=".$_GET['leptoken'] : "" );
	die( header("Location: index.php".$add) );

} 
else 
{	
	$file = preg_replace("/\W-_/i", "", addslashes($_POST['file']));  // fix secunia 2010-92-1
}

// Check if the template exists
if(!file_exists(LEPTON_PATH.'/templates/'.$file)) 
{
	$add = (isset($_GET['leptoken']) ? "?leptoken=".$_GET['leptoken'] : "" );
	die( header("Location: index.php".$add) );
}

// get classes instance
$admin = LEPTON_admin::getInstance();
$oTWIG = lib_twig_box::getInstance();

// get values
$current_template = [];
$database->execute_query(
	"SELECT * FROM `".TABLE_PREFIX."addons` WHERE type = 'template' AND directory = '".$file."'",
	true,
	$current_template,
	false
);

// check if a template description exists for the displayed backend language
if(file_exists(LEPTON_PATH.'/templates/'.$file.'/languages/'.LANGUAGE .'.php'))
{
	require_once LEPTON_PATH.'/templates/'.$file.'/languages/'.LANGUAGE .'.php';
	if(isset($template_description))
	{
		// Override the module-description with correct desription in users language
		$current_template['description'] = $template_description;
	}
	else	
	{
		$current_template['description'] = $current_template['description'];
	}
}


// Insert language text and messages
$page_values = [ 'current'	=>$current_template ];

echo $oTWIG->render(
	"@theme/templates_details.lte",
	$page_values
);

$admin->print_footer();
