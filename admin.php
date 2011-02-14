<?php
/**
 * Plugin Skeleton: Displays "Hello World!"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'admin.php');
 
/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_dwcommits extends DokuWiki_Admin_Plugin {
    private $output = '';
    private $submitted = false;
    private $helper;
    private $db;
 
    function __construct() {  
        $this->helper =& plugin_load('helper', 'dwcommits');        
        $this->db =  $this->helper->_getDB();
        $this->helper->set_branches();
//ini_set('display_errors',1);
//ini_set('error_reporting',E_ALL);

    }
    /**
     * return some info
     */
    function getInfo(){
      return array(
        'author' => 'me',
        'email'  => 'me@somesite.com',
        'date'   => '20yy-mm-dd',
        'name'   => 'admin plugin dwcommits',
        'desc'   => 'demonstration dwcommits',
        'url'    => 'http://www.dokuwiki.org/plugin:commits',
      );
    }
 
    /**
     * return sort order for position in admin menu
     */
    function getMenuSort() {
      return 999;
    }
    
    /**
     *  return a menu prompt for the admin menu
     *  NOT REQUIRED - its better to place $lang['menu'] string in localised string file
     *  only use this function when you need to vary the string returned
     */
 
    /**
     * handle user request
     */
    function handle() {
    
      if (!isset($_REQUEST['cmd'])) return;   // first time - nothing to do

     $this->output = 'invalid';
     $nov_11 = mktime(0,0,0,11,11,2010);  

     if (!is_array($_REQUEST['cmd'])) return;
        
      // verify valid values
      switch (key($_REQUEST['cmd'])) {
        case 'init' :
           $start_timestamp = $this->get_timestamp($_REQUEST['d']);       
           if(!$start_timestamp) $start_timestamp = $nov_11;
           $rows_done = $this->helper->recreate_table($start_timestamp);
           $this->output = 'Initialized ' . $rows_done . ' rows';
           break;
        case 'fetch' :
        case 'merge' :
        case 'commit' :
        case 'pull' :
        case 'branch':
            $status = "";
            $this->helper->update_commits(key($_REQUEST['cmd']));
            $status = $this->helper->get_status_msg();
            $this->output = $status; 
            break;

        case 'update' : 
            $start_timestamp = $this->get_timestamp($_REQUEST['dup']);
             if(!$start_timestamp){
               $start_timestamp = $nov_11;
               $this->output = 'date set to default';
             }

            $this->helper->populate($start_timestamp);          
            break; 
        case 'status' :
            $status = "";
            if(!$this->helper->get_status()) {
              $status = $this->helper->get_status_msg();
            }
            else $status = $this->helper->get_status_msg();
            $this->output = $status; 
            break;
      }    

     
    $this->submitted = true;  
    }
 
    /**
     * output appropriate html
     */
    function html() {
     
      global $ID;
      $date_str = $this->getConf('default_date');
      if(isset($date_str)) {
          list($month,$day,$year) = explode('-',$date_str);
      }
      else {
          $month ='MM'; $day='DD'; $year='YYY';
      }
      ptln('<form action="'.wl($ID).'" method="post">');
      
      // output hidden values to ensure dokuwiki will return back to this plugin
      ptln('  <input type="hidden" name="do"   value="admin" />');
      ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
      ptln('  <input type="hidden" name="dwc__branch" id="dwc__branch" value="'.  $this->helper->selected_branch() .'" />');
     
     /* Initialize Sqlite Database */
      ptln('<b>' . $this->getLang('header_init') .'</b><br />');
      ptln($this->getLang('explain_init') . '<br />');
      ptln($this->getLang('input_year').'&nbsp;&nbsp;(yyyy):  <input type="text" name="d[year]" size="4" value="'. $year .'" />&nbsp;&nbsp;'); 
      ptln($this->getLang('input_month').'&nbsp;&nbsp;(mm):  <input type="text" name="d[month]" size="2" value="' .$month . '" />&nbsp;&nbsp;'); 
      ptln($this->getLang('input_day').'&nbsp;&nbsp;(dd):  <input type="text" name="d[day]" size="2" value="' . $day .'" />&nbsp;&nbsp;'); 
      ptln('&nbsp;&nbsp;<input type="submit" name="cmd[init]"  value="'.$this->getLang('btn_init').'" />');

      /* Update Sqlite Database */
      ptln('<br /><br /><b>' . $this->getLang('header_update')  .'</b><br />');
      ptln($this->getLang('explain_update') . '<br />');
      ptln($this->getLang('input_year').'&nbsp;&nbsp;(yyyy):  <input type="text" name="dup[year]" size="4" value="'. $year .'" />&nbsp;&nbsp;'); 
      ptln($this->getLang('input_month').'&nbsp;&nbsp;(mm):  <input type="text" name="dup[month]" size="2" value="'. $month . '" />&nbsp;&nbsp;'); 
      ptln($this->getLang('input_day').'&nbsp;&nbsp;(dd):  <input type="text" name="dup[day]" size="2" value="' . $day .'" />&nbsp;&nbsp;');        
      ptln('&nbsp;&nbsp;<input type="submit" name="cmd[update]"  value="'.$this->getLang('btn_update').'" />');
      
     /* Update git */
      ptln('<br /><br /><TABLE cellspacing="4">');
      ptln('<tr><td colspan="2"><b>'. $this->getLang('header_git') . '</b></td>');
      ptln('<tr><td colspan="5">' . $this->getLang('explain_git') . '</td>');

      /* Check Git Status  */

      ptln('<tr><td>&nbsp;&nbsp;<input type="submit" name="cmd[status]"  value="'.$this->getLang('btn_status').'" /></td>');
      ptln('<td>' . $this->getLang('header_git_status') . '</td>');
      ptln('<td>&nbsp;</td>');
      ptln('<td>&nbsp;&nbsp;<input type="submit" name="cmd[pull]"  value="'.$this->getLang('btn_pull').'" /></td>');
      ptln('<td>' . $this->getLang('header_git_pull') . '</td>');


      ptln('</table>');
      ptln('<DIV class="dwc_extratop" id="dwc_extratop">');
      ptln('<a href="javascript:dwc_show_extra(); void 0;">' .$this->getLang('header_additional') . '</a>');  
      ptln('</DIV>');
      ptln('<DIV class="dwc_git_extra" id="dwc_git_extra_div">');
      ptln('<TABLE cellspacing="4" border="0">');
  
      /* Fetch  */
      ptln('<tr><td align="right"><input type="submit"  name="cmd[fetch]"  value="'.$this->getLang('btn_fetch').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_fetch') . '</td>');
      ptln('<td>&nbsp;&nbsp;</td>');

      /* Merge  */
      ptln('<td align="right"><input type="submit" name="cmd[merge]"  value="'. $this->getLang('btn_merge').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_merge'));

      ptln('<td align="center">' . $this->getLang('branch_names') . '</td>'  );
      /* Add and Commit  */
      ptln('<tr><td align="right"><input type="submit" name="cmd[commit]"  value="'.$this->getLang('btn_commit').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_commit'));
      ptln('<td>&nbsp;&nbsp;</td>');

      /* Branch  */
      ptln('<td align="right"><input type="submit" name="cmd[branch]"  value="'.$this->getLang('btn_branch').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_branch'));

      ptln('<td>&nbsp;<Select onchange="dwc_branch(this)">');
      $this->helper->get_branches();
      ptln('<td></Select>');

      ptln('</table>');
      ptln('</DIV>');
      ptln('</form>');

      ptln('<br /><div class="dwc_msgareatop">');     
      ptln('Message Area');
      ptln('</div>');
      ptln('<div class="dwc_msgarea">');     
      ptln('<p>'.$this->output.'</p>');
      ptln('</div>');
     
       if($this->submitted) {
          //ptln("<p>submitted</p>");
       }

    }

   function get_timestamp($d) {
      $dstr = implode(' ',$d);
      if(preg_match('/[a-zA-Z]/',$dstr)){
         msg('Date wasn\'t set:' . $dstr . ' Will be set to default', -1);
         return false;      
      }
      if((strlen($d['month']) < 2) || (strlen($d['year']) < 4)||(strlen($d['day']) < 2)  ) {
                msg('Incorrect date format: ' . $dstr .' Will be set to default', -1);
      }

      if((strlen($d['month']) + strlen($d['year']) + strlen($d['day']) > 8 )  ) {
                msg('Incorrect date format: ' . $dstr .' Will be set to default', -1);
      }

      return mktime (0,0,0, $d['month'], $d['day'], $d['year']);
   }
}

