<?php
/**
 * Plugin Skeleton: Displays "Hello World!"
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
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
    private $current_page;

    function __construct() {  
        $this->helper =& plugin_load('helper', 'dwcommits');        
        $this->db =  $this->helper->_getDB();
        $this->helper->set_branches();
        $this->helper->set_repros();
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

    $dwc_Divs = array(
        'init'=>"dcw_db_update",'update'=>"dcw_db_update",
        'fetch'=>"dwc_git_advanced",'merge'=>"dwc_git_advanced",'commit'=>"dwc_git_advanced",'add'=>"dwc_git_advanced",
        'status'=>"dcw_update_git", 'pull'=>"dcw_update_git",'remote_url'=>"dcw_update_git",
        'branch'=>"dwc_repos_div",'repro'=>"dwc_repos_div",
        'info'=>"dwc_info_div",
        'query'=>"dwc_query_div",
        'prune' =>"dwc_prune_div", 'restore'=>"dwc_prune_div",'prune_del'=>"dwc_prune_div"    
    );

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
        case 'remote_url':
        case 'add':
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

             $retv = $this->helper->populate($start_timestamp); 
             if(is_array($retv)) {
               list($num,$recs) = $retv;
               $this->output = "Records written to database: $num. Records in database: $recs.";
             }
            break; 
        case 'status' :
            $status = "";
            if(!$this->helper->get_status()) {
              $status = $this->helper->get_status_msg();
            }
            else $status = $this->helper->get_status_msg();
            $this->output = $status; 
            break;
        case 'repro':
           //path switched in helper constructor
           $this->output = $this->getLang('repro_switched') . ':' . $_REQUEST['dwc__repro'];     
           break; 
        case 'query':            
            list($arr,$q) = $this->helper->select_all(); 
            $this->output = "<b>$q</b><br />";  
            if($arr) {                
                if($_REQUEST['output_type'] == 'plain') {
                 $this->output .= $this->helper->format_result_plain($arr);
                }
                else {
                   $this->output .= $this->helper->format_result_table($arr);
                }
            }
            else $this->output .= "no result";
           break;
        case 'set_remote_url':            
           $this->output = $this->helper->set_githubURL($_REQUEST['remote_url_name']); 
           break;
        case 'prune': 
              $this->output = $this->helper->prune(false);
            break;
        case 'prune_del':        
              $this->output = $this->helper->prune(true);
          break;
        case 'restore':        
              $this->output = $this->helper->restore_backup();
          break;

      }    

    $this->current_page = $dwc_Divs[key($_REQUEST['cmd'])];

  $this->submitted = "";
  $this->submitted = $this->current_page . '<br />' . key($_REQUEST['cmd'])  . '<pre>' . print_r($_REQUEST,1) . '</pre>';  
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

      /*  Navigation Bar  */
      ptln('<DIV class="dwc_navbar">');
      ptln('<table cellspacing="4">'); 
      ptln('<tr><td><a href="javascript:dwc_toggle_div(\'dcw_db_update\'); void 0;">' . $this->getLang('sql_opts') . '</a>');      
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_div(\'dcw_update_git\'); void 0;">' . $this->getLang('git_opts') . '</a>');
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_div(\'dwc_git_advanced\'); void 0;">' . $this->getLang('git_advanced_opts') . '</a>');
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_div(\'dwc_repos_div\'); void 0;">' . $this->getLang('git_repos') . '</a>');
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_div(\'dwc_query_div\'); void 0;">' . $this->getLang('git_query') . '</a>');
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_div(\'dwc_prune_div\'); void 0;">' . $this->getLang('maintenance') . '</a>');

      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_toggle_info(\'dwc_info_div\'); void 0;">' . $this->getLang('git_info') . '</a>');
      ptln('<td>&nbsp;&nbsp;<a href="javascript:dwc_close_all(); void 0;">' . $this->getLang('btn_close_all') . '</a>');
      ptln('</table>');
      ptln('</DIV>');        
      /*  Form  */
      ptln('<form action="'.wl($ID).'" method="post">');
      
      // output hidden values to ensure dokuwiki will return back to this plugin
      ptln('  <input type="hidden" name="do"   value="admin" />');
      ptln('  <input type="hidden" name="page" value="'.$this->getPluginName().'" />');
      ptln('  <input type="hidden" name="dwc__branch" id="dwc__branch" value="'.  $this->helper->selected_branch() .'" />');
      ptln('  <input type="hidden" name="dwc__repro" id="dwc__repro" value="'. $this->helper->selected_repro()   .'" />');

     /* Initialize Sqlite Database */
      ptln('<DIV id="dcw_db_update" class="dwc_box">');

      ptln('<DIV CLASS="dwc_help_btn">');
      ptln('<a href="javascript:dwc_help(\'updatecreate_sql_database\'); void 0;">');
      ptln($this->getLang('git_info') .' </a>');
      ptln('<a href="javascript:dwc_toggle_div(\'dcw_db_update\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
     
      ptln('</DIV>');
      
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

      ptln('</DIV>');

     /* Update git */
      ptln('<DIV id="dcw_update_git" class="dwc_box">'); 
      ptln('<DIV CLASS="dwc_help_btn">');
      ptln('<a href="javascript:dwc_help(\'update_git\'); void 0;">');
      ptln($this->getLang('git_info') .' </a>');
      ptln('<a href="javascript:dwc_toggle_div(\'dcw_update_git\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
      ptln('</DIV>');

      ptln('<b>'. $this->getLang('header_git') . '</b>');
      ptln('<br /><TABLE cellspacing="4">');      
      ptln('<tr><td colspan="5">' . $this->getLang('explain_git') . '</td>');

      /* Check Git Status  */
      ptln('<tr><td>&nbsp;&nbsp;<input type="submit" name="cmd[status]"  value="'.$this->getLang('btn_status').'" /></td>');
      ptln('<td>' . $this->getLang('header_git_status') . '</td>');
      ptln('<td>&nbsp;</td>');
      ptln('<td>&nbsp;&nbsp;<input type="submit" name="cmd[pull]"  value="'.$this->getLang('btn_pull').'" /></td>');
      ptln('<td>' . $this->getLang('header_git_pull') . '</td>');
      ptln('</table>');

     /* Get and Set Remote URL */
      ptln('<b>' . $this->getLang('header_remote_url')  .'</b><br />');
      ptln($this->getLang('explain_remote_url') . '<br />');
      ptln('&nbsp;&nbsp;<input type="submit" name="cmd[remote_url]"  value="'.$this->getLang('btn_remote_url').'" />');
      ptln('&nbsp;&nbsp;' . $this->getLang('remote_url_text') 
           . '&nbsp;<input type="text" name="remote_url_name" size="80"  value="'
           . $this->helper->get_remote_url() .'" />');
      ptln('&nbsp;&nbsp;<input type="submit" name="cmd[set_remote_url]"  value="'.$this->getLang('btn_set_remote').'" />');

      ptln('</DIV>');

     /* Advanced Git Options */
      ptln('<DIV class="dwc_box" id="dwc_git_advanced">');
      ptln('<DIV CLASS="dwc_help_btn">');
      ptln('<a href="javascript:dwc_toggle_div(\'dwc_git_advanced\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
      ptln('</DIV>');

      ptln('<DIV class="dwc_advancedtop" id="dwc_advancedtop">');
      ptln($this->getLang('header_additional'));  
      ptln('</DIV>');

      ptln('<TABLE cellspacing="4" border="0">');
  
      /* Fetch  */
      ptln('<tr><td align="right"><input type="submit"  name="cmd[fetch]"  value="'.$this->getLang('btn_fetch').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_fetch') . '</td>');
      ptln('<td>&nbsp;&nbsp;</td>');

      /* Merge  */
      ptln('<td align="right"><input type="submit" name="cmd[merge]"  value="'. $this->getLang('btn_merge').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_merge'));


      ptln('<td>&nbsp;&nbsp;<td align="right"><input type="submit" name="cmd[add]"  value="'.$this->getLang('btn_add').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_add'));
      ptln('<td>&nbsp;&nbsp;</td>'); 
   
     /* Add and Commit  */
      ptln('<td>&nbsp;&nbsp;<td align="right"><input type="submit" name="cmd[commit]"  value="'.$this->getLang('btn_commit').'" />');
      ptln('<td>&nbsp;' . $this->getLang('header_git_commit'));
      ptln('<td>&nbsp;&nbsp;</td>'); 
      ptln('</table>');
      ptln('</DIV>');   

      ptln('<DIV class="dwc_box" id="dwc_repos_div">');
      ptln('<DIV CLASS="dwc_help_btn">');
      ptln('<a href="javascript:dwc_toggle_div(\'dwc_repos_div\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
      ptln('</DIV>');

      /* Branches and Repos*/
      ptln('<TABLE cellspacing="4" border="0">');
      ptln('<tr><td colspan="2" align="right">');

         /* Current Sqlite DB Name */
      ptln($this->getLang('current_db') . $this->helper->current_dbname());
      ptln('<tr><td colspan="2">&nbsp;'); //Row spacer
     
      /*Repos */
      ptln('<tr><th align="center" colspan="2">' . $this->getLang('repo_names') . '&nbsp;&nbsp;&nbsp;</th>'  );
      ptln('<tr><td align="left"><input type="submit" name="cmd[repro]"  value="'.$this->getLang('btn_repos').'" />');
      ptln('<td>&nbsp;<Select onchange="dwc_repro(this)">');
      $this->helper->get_repros();
      ptln('</Select>'); 

      ptln('<tr><td colspan="2">&nbsp;'); //Row spacer

      /*Branches */
      ptln('<tr><th align="center" colspan="2">' . $this->getLang('branch_names') . '&nbsp;&nbsp;&nbsp;</th>'  );
      ptln('<tr><td align="left"><input type="submit" name="cmd[branch]"  value="'.$this->getLang('btn_branch').'" />');
      ptln('<td>&nbsp;<Select onchange="dwc_branch(this)">');
      $this->helper->get_branches();
      ptln('</Select>');    

      ptln('</table>');

      ptln('</DIV>');   

       /*Query */


       ptln('<DIV class="dwc_box" id="dwc_query_div">');

       ptln('<DIV CLASS="dwc_help_btn">');
       ptln('<a href="javascript:dwc_help(\'query\'); void 0;">');
       ptln($this->getLang('git_info') .' </a>');
       ptln('<a href="javascript:dwc_toggle_div(\'dwc_query_div\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
       ptln('</DIV>');

       ptln('<div class="dwc_msgareatop">');
       ptln($this->getLang('header_git_query'). '<br >');
       ptln('</DIV>');
       ptln('<TABLE CELLSPACING="4"  border="1" CLASS="dwc_dbq">');

       ptln('<TR><TD ALIGN="RIGHT">' . $this->getLang('q_srch_term') . ' 1 <input type="text" value="" name="dwc_query[terms_1]"></TD>');
       ptln('&nbsp;&nbsp;<TD>&nbsp;&nbsp;' . $this->getLang('q_srch_term') . ' 2 <input type="text" value="" name="dwc_query[terms_2]"></TD>'); 
       ptln('<TD ALIGN="LEFT">&nbsp;&nbsp;' . $this->getLang('q_srch_type') . '&nbsp;&nbsp;OR <input type="RADIO" value="OR" CHECKED name="dwc_query[OP_1]">');
       ptln('&nbsp;&nbsp;AND <input type="RADIO" value="AND" name="dwc_query[OP_1]"></TD>');

       ptln('<TR><TD ALIGN="RIGHT">'. $this->getLang('q_author') .'&nbsp;&nbsp;<input type="text" value="" name="dwc_query[author]">');
       ptln('<td COLSPAN="1">&nbsp;&nbsp;' . $this->getLang('q_srch_type') . '&nbsp;&nbsp;OR <input type="radio" value="OR" name="dwc_query[OP_2]">');   
       ptln('&nbsp;&nbsp;AND <input type="radio" value="AND" CHECKED name="dwc_query[OP_2]"></TD>');   
       
       ptln('<TD>&nbsp;&nbsp;'. $this->getLang('q_branch') . '&nbsp;&nbsp;<SELECT name="dwc_query[branch]">');
       ptln($this->helper->get_branches());
       ptln('<option value="any" selected>any</option>'); 
       ptln('</SELECT>');

       ptln('<TR><TD ALIGN="RIGHT">' .$this->getLang('q_start_date') .' <input type="text" value="" name="dwc_query[d1]"></TD>');
       ptln('<TD>&nbsp;&nbsp;' . $this->getLang('q_end_date') .' <input type="text" value="" name="dwc_query[d2]"></TD>');
       ptln('<TD>&nbsp;&nbsp;' . $this->getLang('q_date_fmt') . '&nbsp;&nbsp;MM-DD-YYYY</TD>');      

       ptln('<TR><TD>' .$this->getLang('q_output') . ':&nbsp;&nbsp;<input type ="radio" name="output_type" checked value="table">' . $this->getLang('q_table'));
       ptln('&nbsp;&nbsp;<input type ="radio" name="output_type" value="plain">' . $this->getLang('q_plain'));
       ptln('<TD COLSPAN="2" ALIGN="RIGHT">&nbsp;&nbsp;<input type="submit" value = "Submit query" name="cmd[query]">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>');

       ptln('</TABLE>');
       ptln('</DIV>');
   
       ptln('<DIV class="dwc_box" id="dwc_prune_div">');   
       ptln('<DIV CLASS="dwc_help_btn">');
       ptln('<a href="javascript:dwc_help(\'maintenance\'); void 0;">');
       ptln($this->getLang('git_info') .' </a>');
       ptln('<a href="javascript:dwc_toggle_div(\'dwc_prune_div\'); void 0;">' . $this->getLang('div_close') .' </a>' ); 
       ptln('</DIV>');     
       ptln('<b>' .$this->getLang('explain_maint') . '</b><br /><br />');
       echo  $this->helper->db_data();      
       ptln('<br /><input type="submit" value = "' . $this->getLang('prune') . '" name="cmd[prune]">');
       ptln('&nbsp;&nbsp;&nbsp;<input type="submit" value = "' . $this->getLang('prune_del') . '" name="cmd[prune_del]">');
       ptln('&nbsp;&nbsp;&nbsp;<input type="submit" value = "' . $this->getLang('prune_restore') . '" name="cmd[restore]">');
       ptln('</DIV>');
       ptln('</form>');


     /* Info Div */
       ptln('<DIV class="dwc_box" id="dwc_info_div">');
       ptln('<DIV class="dwc_box" id="combined_info_div">');
       ptln('Database Name: ' . $this->helper->current_dbname() . '<br>'); 
       ptln('Current Repro: ' . $this->helper->selected_repro() . '<br />'); 
       ptln('Branch: ' . $this->helper->selected_branch() . '<br>'); 
       ptln('Remote Url: ' . $this->helper->get_remote_url() . '<br>'); 
       ptln('</DIV>');
       $help_file = $this->locale_xhtml('dwc_admin');
       $help_file = preg_replace('/~~CLOSE~~/ms','<DIV class="closer"><a href="javascript:dwc_toggle_info(\'dwc_info_div\'); void 0;">' . $this->getLang('div_close') .'</a></DIV>',$help_file); 
       echo $help_file;      
       ptln('</DIV>');

   /* Message Area */
      ptln('<br /><div class="dwc_msgareatop" id="dwc_msgareatop">');     
      ptln($this->getLang('header_git_MsgArea'));     

      ptln('<DIV CLASS="dwc_help_btn">');
      ptln('<a href="javascript:msg_area_bigger(); void 0;">');
      ptln($this->getLang('btn_msg_big') .' </a>');
      ptln('&nbsp;&nbsp;&nbsp;<a href="javascript:msg_area_smaller(); void 0;">');
      ptln($this->getLang('btn_msg_small') .' </a>');
      ptln('</DIV>');

      ptln('</DIV>');

      ptln('<DIV class="dwc_msgarea" id="dwc_msgarea">');     
      ptln('<p>'.$this->output.'</p>');
      ptln('</DIV>');
     
       if($this->submitted) {
        //   ptln($this->submitted);
       }
     ptln('<script language="javascript">');
     ptln('dwc_toggle_div("' . $this->current_page . '");');
     ptln('</script>');
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

