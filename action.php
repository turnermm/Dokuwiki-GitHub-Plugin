<?php
/**
 * Example Action Plugin:   Example Component.
 *
 * @author     Samuele Tognini <samuele@cli.di.unipi.it>
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once DOKU_PLUGIN.'action.php';

class action_plugin_dwcommits extends DokuWiki_Action_Plugin {
    
    /**
     * Register its handlers with the DokuWiki's event controller
     */
    function register(&$controller) {
        $controller->register_hook('PARSER_CACHE_USE', 'BEFORE', $this,
                                   'touch_cache');
    }

    /**
     * Hook js script into page headers.
     *
     * @author Myron Turner <turnermm02@shaw.ca>
     */
    function touch_cache(&$event, $param) {
     global $ID;
     $auto = $this->getConf('auto_id');
   
     if(preg_match('/' . $auto.'/',$ID)){         
         $event->preventDefault();        
      }
    }
        

}
