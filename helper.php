<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DW_COMMITS')) define('DW_COMMITS',DOKU_INC.'lib/plugins/dwcommits/');

/**
 * This is the base class for all syntax classes, providing some general stuff
 */
class helper_plugin_dwcommits extends DokuWiki_Plugin {

  private $path;
  private $status_message;
  private $branches;
  private $selected_branch;
  private $sqlite;
    function __construct() {  
       //  $this->path = DW_COMMITS . 'db/dokuwiki_updated'; 
        $this->path = DW_COMMITS . 'db/dokuwiki.1';
        $this->status_message = array();
        $this->selected_branch="";
        $this->sqlite = 0;  
    }

    /**
     * load the sqlite helper
     */
    function _getDB(){
        static $db = null;
        if ($db === null) {
            $db =& plugin_load('helper', 'sqlite');
            if ($db === null) {
                msg('The data plugin needs the sqlite plugin', -1);
                return false;
            }
            if(!$db->init('dwcommits_2',dirname(__FILE__).'/db/')){
                return false;
            }             
            $db->fetchmode = DOKU_SQLITE_ASSOC;
        }
        $this->sqlite = $db; 
        return $db;
    }
    function wearehere() {
        echo 'wearehere';
    }

   function chdir() {
      if(!chdir($this->path)) {
         if(file_exists($this->path)) {
               $this->error(1);
         }
         else $this->error(0);
         return false; 
     }
      return true;
    }

   function update_commits($which) {
        $this->status_message = array();
        if(!$this->chdir()) return false;

        if($which == 'fetch') {
            exec("git fetch origin",$retv,$exit_code);
            $status = "status: --" . $exit_code . "--";
            $this->status_message = array_merge(array(getcwd(),"git fetch origin", $status),$this->status_message,$retv);        
        }
        elseif($which == 'merge') {
            exec("git merge origin",$retv, $exit_code);
            $status = "status: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git merge origin", $status),$this->status_message,$retv);        
        }
        elseif($which == 'commit') {
            exec('git add .',$retv_add, $exit_code);
            exec('git commit -mdbupgrade',$retv, $exit_code);
            $status = "status: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git commit", $status),$this->status_message,$retv_add,$retv);        
        }
        elseif($which == 'pull') {
            exec('git pull',$retv, $exit_code);
            $status = "status: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git pull", $status),$this->status_message,$retv);        
        }
       elseif($which == 'branch') {
            $branch = $_REQUEST['dwc__branch'];
            exec("git checkout $branch",$retv, $exit_code);
            $status = "status: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git checkout $branch", $status),$this->status_message,$retv);        
            $this->set_branches();
        }
 
            if($exit_code > 0) return false;             
            return true;

   }

   function set_branches() {
       if(!$this->chdir()) return false;
       $this->branches = array();
       exec("git branch",$retv, $exit_code); 
       if($exit_code) return false;   
       foreach ($retv as $branch) {
        if(preg_match('/\*(.*)/',$branch,$matches)) {        
           $this->selected_branch = $matches[1]; 
           $this->branches[] = '<option value="' .$matches[1] . '" selected>' . $matches[1] . '</option>';
        }
        else {
            $this->branches[] = '<option value="'. $branch.'">' . $branch .'</option>';
        }
       }
        
   }

   function get_branches() {        
      echo implode("\n",$this->branches);
   }
   
   function selected_branch() {        
          if($this->selected_branch) return $this->selected_branch;
          return 'master';  
   }

   function get_status() {
     
      if(!$this->chdir()) return false;
      if(!exec("git status",$retv) ) {    
           $this->status_message = $retv;          
           $this->error(1);          
           return false;
      }
      $this->status_message = $retv;     
  
      return true;

   }
  
   function error($which) {
      $path = $this->path;
      $msgs = array(
           "Cannot find cloned Dokuwiki at $path",
           "Cannot access $path. The entire directory and all its contents must be read/write for the web server.",
           "Cannot fetch from github",
           "Unable to merge"
      );
      msg($msgs[$which],-1);
     
   }
   function get_status_msg() {
       $status = $this->status_message;
       $this->status_message = array();
      
       $current_git = "<b>Git:</b> $this->path<br/>";
       if(!is_array($status)) return $current_git;
       return $current_git . implode('<br />',$status);
      
   }

function populate($timestamp_start=0,$table='git_commits') {
   
     if(!$this->chdir()) return false;

     $months = array('Jan'=>1,'Feb'=>3,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,
              'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);

    $count = 0;
    if(!$timestamp_start) {
       $timestamp_start = mktime(0,0,0,11,11,2010);
    }

    $handle = popen("git log", "r");
    $msg = "";
    $author="";
    $timestamp=0;
    $gitid="";
    $record_done = false;
    $done = false;
    if (!$handle) {
      echo "can't open git\n";
      exit;
    }

        while (($buffer = fgets($handle, 4096)) !== false) {
      
              
           if(preg_match('/^([A-Z]\w+):(.*)/',$buffer, $matches)) {
     
               switch($matches[1]){

                 case 'Date':
                     preg_match('/(\w+)\s+(\d+)\s+(\d+):(\d+):(\d+)\s+(\d+)/',$matches[2],$date_matches);
                     list($dstr,$mon,$day,$hour,$min,$sec,$year) = $date_matches;                 
                     $timestamp = mktime ($hour,$min,$sec, $months[$mon], $day, $year);                                  
                     $count++ ;                     
                     if($timestamp < $timestamp_start) {
                       $done = true;
                    }
                    break;

                 case 'Merge':
                   break; 
              
                 case 'Author':  
                    $author = $matches[2];  
                    break;

                 default:
                    break;   
               }
           
           }
           elseif (preg_match('/^commit\s(.*)/',$buffer,$commit)) {
               if($msg) { 
                     $this->insert($author,$timestamp,$gitid,$msg,$table);
               }
               $msg = "";
               $gitid=$commit[1];          

           }
           else {
            $msg .= $buffer;
           }
          if($done) break;  
        }

    pclose($handle);

   
}

    function insert($author,$timestamp,$gitid,$msg,$table) {     

        $prefix =  substr( $gitid , 0, 15 );  
       
        if($this->sqlite->query("INSERT OR IGNORE INTO $table (author,timestamp,gitid,msg,prefix) VALUES (?,?,?,?,?)", 
                     $author,$timestamp,$gitid,$msg,$prefix)){              
              return true;
        }
        else {
          return false;
        }

    }
   


  function recreate_table($timestamp_start) {

     $this->sqlite->query("DROP TABLE git_commits");
     $this->sqlite->query('CREATE TABLE git_commits(author TEXT,timestamp INTEGER,gitid TEXT,msg TEXT, prefix TEXT, PRIMARY KEY(prefix,timestamp))');     
     $this->populate($timestamp_start);
     $results = $this->sqlite->query('select COUNT(*) as number from git_commits');         

     foreach($results as $n) {
          $retv = $n[0];
          return $retv; 
     }
  }
}