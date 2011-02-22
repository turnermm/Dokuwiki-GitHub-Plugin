<?php
/**
 * Plugin Color: Sets new colors for text and background.
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
if(!defined('DW_COMMITS')) define('DW_COMMITS',DOKU_INC.'lib/plugins/dwcommits/');
@touch (DOKU_INC .'inc/parser/xhtml.php');
@touch(DOKU_INC .'conf/local.php');


/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_dwcommits extends DokuWiki_Syntax_Plugin {

 private $helper;
 private $db;
 private $output;
    function __construct() {  
        $this->helper =& plugin_load('helper', 'dwcommits');        
    
    }

    /**
     * return some info
     */
    function getInfo(){
        return array(
            'author' => 'Myron Turner',
            'email'  => 'turnermm02@shaw.ca',
            'date'   => '20011-02-19',
            'name'   => 'dwcommits Syntax Plugin',
            'desc'   => 'Output git database query',
            'url'    =>  '',
        );
    }

     
    function getType(){ return 'container'; }
    function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }   
    function getSort(){ return 158; }
    function getPType(){ return 'block';  }

    function connectTo($mode) {
      $this->Lexer->addEntryPattern('~~DWCOMMITS.*?',$mode,'plugin_dwcommits'); 
    }
    function postConnect() { $this->Lexer->addExitPattern('DWCOMMITS~~','plugin_dwcommits'); }


    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler){
        switch ($state) {
          case DOKU_LEXER_ENTER :
                return array($state, "");

          case DOKU_LEXER_UNMATCHED :  
                    if(!$this->parse_match($match) ) {
                      return array($state, ' db parsing failed ');
                    }
                    return array($state, $match);
          case DOKU_LEXER_EXIT :
                    return array($state, '');
        }
        return array();
    }

    /**
     * Create output
     */
    function render($mode, &$renderer, $data) {
        if($mode == 'xhtml'){
            list($state,$match) = $data;
            switch ($state) {
              case DOKU_LEXER_ENTER :      
                $renderer->doc .= "<p>"; break;
                break;
                
              case DOKU_LEXER_UNMATCHED :              
               $renderer->doc .=  $this->output;             
              case DOKU_LEXER_EXIT :    
                $renderer->doc .= "</p>"; 
                break;
            }
            return true;
        }
        return false;
    }


   function parse_match($match) {
      
      $result = array();
      $matches = explode("\n", trim($match)); 
      foreach($matches as $entry)  {
           list($field,$val) = explode(':',$entry);
           $field = trim($field);  
           $val = trim($val);  
           if(preg_match('/^\s*#/',$field)) continue;
           switch($field) {
            	case 'DATABASE':   
                      $remote_url = $this->helper->setup_syntax($val);
                      $remote_url .= " db=" . $val;
                       $val = "";                       
            	        break;
            	case 'TERM_1':
                        $field = 'terms_1';   

            	        break;
            	case 'AND_OR_TERM_2': 
                        $field = 'OP_1';
                        if(!$val) $val = 'OR';

            	        break;
            	case 'TERM_2':
                        $field = 'terms_1'; 

            	        break;
            	case 'AND_OR_AUTHOR':
                        $field = 'OP_2';
                        if(!$val) $val = 'AND';
            	        break;
            	case 'AUTHOR':
                        $field = 'author';
            	        break;
            	case 'DATE_1':
                        $field = 'd1';
            	        break;
            	case 'DATE_1':
                        $field =  'd2';
            	        break;
            	case 'BRANCH':
                         $field = 'branch';
            	        break;
           }
                if($val) $result[$field]=$val;
      }
           $this->db =  $this->helper->_getDB();         

           list($arr,$q) = $this->helper->select_all($result); 
           if($arr) {
               $this->output = "<b>Query: $q</b><br />";
              // $this->output .= $this->helper->format_result_plain($arr,$result);
               $this->output .=  $this->helper->format_result_table($arr,$result);
               return true;
           }

  
        return false;

    
   }
}
?>
