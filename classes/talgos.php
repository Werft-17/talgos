<?php

/**
 *  @template       Talgos Backend-Theme
 *  @version        see info.php of this template
 *  @author         LEPTON project and others for Algos theme, LEPTON project for Talgos theme
 *  @copyright      2010-2024 LEPTON Project 
 *  @license        GNU General Public License
 *  @license terms  see info.php of this template
 *
 */

class talgos extends LEPTON_template
{
    public $alternative_url = THEME_URL.'/backend/backend/pages/';
    public $action_url = ADMIN_URL.'/pages/';

    /**
     *  Default state of a subfolder/subpage:
     *      0 = closed/collapsed,
     *      1 = display open
     *
     *  @var    integer $defaultState   The default-status of a subdirectory im the view.
     */
    public $defaultState = 0;

    public static $instance;
    
    public function initialize()
    {
    
    }

    public function setRememberState( &$allPages )
    {
        foreach($allPages as &$ref)
        {
            $ref['tree_status'] = ($_COOKIE["p".$ref['page_id']] ?? $this->defaultState);
            
            $this->setRememberState( $ref['subpages'] );
        }
    }
}
