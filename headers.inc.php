<?php

/**
 *  @template       Talgos Backend-Theme
 *  @version        see info.php of this template
 *  @author         LEPTON project and others for Algos theme, LEPTON project for Talgos theme
 *  @copyright      2010-2024 LEPTON Project
 *  @license        GNU General Public License
 *  @license terms  see info.php of this template
 *
 *
 */
 


$mod_headers = array(
    'backend' => array(
        'css' => array(
            array(
                'media' => 'all',
                'file'  => 'modules/lib_jquery/jquery-ui/jquery-ui.min.css'
            ),
            array(
                'media' => 'all',
                'file'  => 'templates/talgos/backend/backend/custom.css'
            )
        ),
        'js' => array(
            'modules/lib_jquery/jquery-core/jquery-core.min.js',
            'modules/lib_jquery/jquery-core/jquery-migrate.min.js',
            'modules/lib_jquery/jquery-ui/jquery-ui.min.js'
        )
    )
);

/**
 *  Keep in mind that EN is the default language of the "datepicker"
 *  and there is NO "datepicker-en.js" file inside the jq-ui framework at all,
 *  we are only in the need to load this one if there is a diffenten language here
 *  as en - or the matching file for e.g. "NO", or "XN" doesn't exists.
 */
$sTempDatePickerLang = lib_jquery::SetJQueryLanguage('datepicker');
if( $sTempDatePickerLang != "en")
{
    $mod_headers["backend"]["js"][] = 'modules/lib_jquery/jquery-ui/i18n/datepicker-'.$sTempDatePickerLang.'.js';
}
