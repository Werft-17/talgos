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
$database = LEPTON_database::getInstance();

$MENU = LEPTON_core::getGlobal("MENU");
$THEME = LEPTON_core::getGlobal("THEME");

// As we are "calling" a static method more than two times we are using an instance here for the reference for the class
$LEPTON_basics = LEPTON_basics::getInstance();

//    temp. hack to get page to run    
if (!isset($_POST['modify']) && ($_POST['group_id'] = ''))
{
    header("Location: index.php");
    exit(0);
} 
else 
{
    $group_id = intval($_POST['group_id']);
}

if(isset($_SESSION['last_saved_group_id']))
{
    $group_id = $_SESSION['last_saved_group_id'];
}

$current_group = [
    'system_permissions' => "",
    'module_permissions' => "",
    'template_permissions' => "",
    'language_permissions' => "",
    'backend_access' => 1
];

if ( (isset($_POST['modify']) && ($_POST['group_id'] > 1)) || (isset($group_id) && ($group_id > 1)))
{
    // Get group values
    $database->execute_query(
        "SELECT * FROM `".TABLE_PREFIX."groups` WHERE `group_id` = ".$group_id,
        true,
        $current_group,
        false
    );
} 

//    Get the system permissions
$system_lookups = [
    'pages'     => array('view', 'add', 'add_level_0','settings', 'modify','delete'),
    'media'     => array('view','upload','rename','delete','create'),
    'modules'   => array('view','install','uninstall'),
    'templates' => array('view','install','uninstall'),
    'languages' => array('view','install','uninstall'),
    'settings'  => array('basic','advanced','backend_access'),
    'users'     => array('view','add','modify','delete'),
    'groups'    => array('view','add','modify','delete'),
    'admintools' => array('view')
];

$group_system_permissions = explode(',', $current_group['system_permissions']);
    
$system_permissions = [];

foreach($system_lookups as $sys_key => $subkeys)
{
    $sub_keys = [];        
    foreach($subkeys as $item) 
	{
        $sub_keys[] = [
            'name' => $sys_key."_".$item,
            'label'    => $LEPTON_basics->get_backend_translation( strtoupper($item) ),
            'checked' => in_array( $sys_key."_".$item, $group_system_permissions ) ? 1 : 0
        ];
    }
        
    $system_permissions[] = [
        'name'    => $sys_key,
        'label'   => $MENU[ (strtoupper($sys_key)) ],
        'checked' => in_array( $sys_key, $group_system_permissions ) ? 1 : 0,
        'sub_keys'=> $sub_keys
    ];
}

//    Get the module permissions
$all_modules = [];
$database->execute_query(
    'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "page" ORDER BY `name`',
    true,
    $all_modules,
    true
);
    
$group_module_permissions = explode(',', $current_group['module_permissions']);
    
$module_permissions = [];
foreach($all_modules as &$module)
{
    $module_permissions[] = array(
        'name'    => $module['name'],
        'directory' => $module['directory'],
        'permission' => in_array($module['directory'], $group_module_permissions) ? 1 : 0
    );
}

// Get the admin-tools permissions
$all_tools = [];    
$database->execute_query(
    'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "module" AND `function` = "tool" ORDER BY `name`',
    true,
    $all_tools,
    true
);
    
$admintools_permissions = [];
foreach($all_tools as &$tool)
{
    $admintools_permissions[] = [
        'name'    	=> $tool['name'],
        'directory' => $tool['directory'],
        'permission'=> in_array($tool['directory'], $group_module_permissions) ? 1 : 0
    ];
}

// Get the templates permissions
$all_templates = [];
$database->execute_query(
    'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "template" ORDER BY `name`',
    true,
    $all_templates,
    true
);

$group_template_permissions = explode(',', $current_group['template_permissions']);

$template_permissions = [];
foreach($all_templates as &$template)
{
    $template_permissions[] = [
        'name'    	=> $template['name'],
        'directory' => $template['directory'],
        'permission'=> in_array($template['directory'], $group_template_permissions) ? 1 : 0
    ];
}

// Get the language permissions
$all_languages = [];
$database->execute_query(
    'SELECT `name`,`directory` FROM `'.TABLE_PREFIX.'addons` WHERE `type` = "language" ORDER BY `name`',
    true,
    $all_languages,
    true
);
    
$group_language_permissions = explode(',', $current_group['language_permissions']);

$language_permissions = [];
foreach($all_languages as &$language)
{
    $language_permissions[] = [
        'name'    	=> $language['name'],
        'directory' => $language['directory'],
        'permission'=> in_array($language['directory'], $group_language_permissions) ? 1 : 0
    ];
}    

// Get/Build secure-hash for the js-calls
LEPTON_handle::register('random_string');

$hash = array(
    'h_name'  => random_string(16),
    'h_value' => random_string(24)
);

$_SESSION['backend_group_h'] = $hash['h_name'];
$_SESSION['backend_group_v'] = $hash['h_value'];

$page_values = [
        'perm_modify'   => $admin->get_permission('groups_modify'),
        'THEME' 		=> $THEME,
        'current_group' => $current_group,
        'action_url'    => ADMIN_URL."/groups/",    
        'system_permissions' => $system_permissions,
        'module_permissions' => $module_permissions,
        'admintools_permissions' => $admintools_permissions,
        'template_permissions'   => $template_permissions,
        'language_permissions'   => $language_permissions,        
        'hash'		=> $hash,
        'FORM_NAME' => "groups_".random_string(12)
];

echo $oTWIG->render(
    "@theme/groups_modify.lte",
    $page_values
);

$admin->print_footer();
