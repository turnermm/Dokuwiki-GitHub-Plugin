<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DW_COMMITS')) define('DW_COMMITS',DOKU_INC.'lib/plugins/dwcommits/');
global $dwc_dbg_log;

class helper_plugin_dwcommits extends DokuWiki_Plugin {

  private $path;
  private $status_message;
  private $branches;
  private $selected_branch ='master';
  private $sqlite;
  private $git = '/usr/bin/git';
  private $repros;
  private $default_repro;
  private $db_name;
  private $remote_url;
  private $commit_url;

    function __construct() {      
        global $dwc_dbg_log;
        if(isset($_REQUEST['dwc__repro']) && $_REQUEST['dwc__repro']) {
             $this->path = $_REQUEST['dwc__repro'];
        }
        else {
           $this->path = $this->get_conf_repro();
        }
        $this->default_repro = $this->path;  
        $this->status_message = array();
        $this->selected_branch="";
        $this->sqlite = 0;  
        $binary = $this->getConf('git_binary');
        if(isset($binary)) $this->git=$binary;
        $fname_hash = md5($this->path);
        $names_fname = dirname(__FILE__).'/db/dbnames.ser'; 

        if(file_exists($names_fname)) {
            $inf_str = file_get_contents ($names_fname);
            $inf = unserialize($inf_str);
            if($inf[$fname_hash]) {
                 $this->db_name=$inf[$fname_hash];
                 $this->remote_url=$this->set_githubURL();
            }
            else {
               $this->db_name=$this->new_dbname($fname_hash,$names_fname,$inf);
            }
        }
        else {
          $this->db_name=$this->new_dbname($fname_hash,$names_fname, false);
        }
        $dwc_dbg_log  =  DW_COMMITS . 'dwc_debug.log';

    }

    function new_dbname($fname_hash,$names_fname,$inf_array) {
      
       if($inf_array && $inf_array['count']) {
          $count =  $inf_array['count'] + 1;
       }
       else {
         $count = 1;   
         $inf_array=array();       
       }
       $inf_array['count'] = $count;
       $new_fname = 'dwcommits_' . $count; 
       $inf_array[$fname_hash] = $new_fname;
       $inf_array['git' . $count] = $this->path;
       file_put_contents($names_fname, serialize($inf_array));       
       return $new_fname ;
      
    }

    function save_dbnames_ser($fname,$content) {      
       if(function_exists(io_saveFile)){
          return io_saveFile($fname,$content);
       }
       else {
         return file_put_contents($names_fname, $content);   
       }
    }

    function set_githubURL($remote_url="") {
        $names_fname = dirname(__FILE__).'/db/dbnames.ser'; 
        $inf_str = file_get_contents ($names_fname);
        $inf = unserialize($inf_str);
        if(!$inf) return;
        $fname_hash = md5($this->path);  
        if(!$inf[$fname_hash]) return;
        $db = $inf[$fname_hash];
        list($base,$count) = explode('_',$db);
        $slot = 'url'.$count; 
        if($remote_url) {
           $inf[$slot] = $remote_url; 
           if($this->save_dbnames_ser($names_fname,serialize($inf))) {
                  return "$remote_url saved";
           }
           else $this->error(5);
        }
        else {
           if($inf[$slot]) return $inf[$slot]; 
        }
        
        return "";

    }

    function current_dbname() {
      return $this->db_name;
    }

   /* must be called from syntax.php before _getDB() */
    function setup_syntax($name) {
        $this->db_name = $name;
        $names_fname = dirname(__FILE__).'/db/dbnames.ser'; 
        $inf_str = file_get_contents ($names_fname);
        $inf = unserialize($inf_str);
        if(!$inf) return;
       
        list($base,$count) = explode('_',$this->db_name);
        $slot = 'url'.$count; 
        if($inf[$slot]) {
             $this->remote_url = $inf[$slot]; 
       }
       else $this->remote_url = "";
       return $this->remote_url . " slot=$slot";
    }


    function get_conf_repro(){
            $repro = $this->getConf('default_git');
            if(isset($repro) && $repro) {
                  return $repro;
            }
            else {
                return DW_COMMITS . 'db/dokuwiki';
            }
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
         
            if(!$db->init($this->db_name,dirname(__FILE__).'/db/')){
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
            exec("$this->git fetch origin",$retv,$exit_code);
            $status = "exit code: " . $exit_code . " ";
            $this->status_message = array_merge(array(getcwd(),"$this->git fetch origin", $status),$this->status_message,$retv);        
        }
        elseif($which == 'merge') {
            exec("$this->git merge origin",$retv, $exit_code);
            $status = "exit code: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"$this->git merge origin", $status),$this->status_message,$retv);        
        }
        elseif($which == 'commit') {
            exec("git commit -mdbupgrade",$retv, $exit_code);
            $status = "exit code: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git commit", $status),$this->status_message,$retv);        
            if($exit_code <=1 ) return true;             
        }
        elseif($which == 'add') {
            exec("$this->git add .",$retv_add, $exit_code);
            $status = "exit code: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git add . ", $status),$this->status_message,$retv_add); 
        }
        elseif($which == 'pull') {
            exec("$this->git pull",$retv, $exit_code);
            $status = "exit code: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git pull", $status),$this->status_message,$retv);        
        }
       elseif($which == 'branch') {
            $branch = $_REQUEST['dwc__branch'];
            exec("$this->git checkout $branch",$retv, $exit_code);
            $status = "exit code: " . $exit_code;
            $this->status_message = array_merge(array(getcwd(),"git checkout $branch", $status),$this->status_message,$retv);        
            $this->set_branches();
        }
       elseif($which == 'remote_url') {
             exec("$this->git  config --get remote.origin.url",$retv, $exit_code);   
             $this->remote_url = $retv[0];           
             $this->remote_url = preg_replace('/:(?!\/)/',"/",$this->remote_url);
             $this->remote_url = preg_replace('/^\s*.*?@/',"",$this->remote_url);
             if(!preg_match('/http/',$this->remote_url)) {
                   if(preg_match('/github/',$this->remote_url)) {
                     $this->remote_url = 'https://'. $this->remote_url;  
                   }
                   else $this->remote_url = 'http://'. $this->remote_url;  
             }

             $this->status_message = array_merge(array(getcwd(),"git checkout $branch", $status),$this->status_message,$retv);        
             if($this->remote_url) { 
                $this->status_message[] = "Remote URL: $this->remote_url";
             }
       } 
            if($exit_code > 0) return false;             
            return true;

   }

    function set_commit_url() {
       if(!$this->remote_url) return false;   
       if($this->commit_url) return $this->commit_url;
       $url = preg_replace('/\.git$/',"", $this->remote_url) . '/commit/';
       $this->commit_url=$url;
       return $url;         
    }

    function get_remote_url() {
      return $this->remote_url;
    }

   function set_repros() {
      $this->repros = array();
      $this->repros[] = $this->html_option($this->path,true);
      $conf_repro = $this->get_conf_repro();
      if($this->path != $conf_repro) {
             $this->repros[] = $this->html_option($conf_repro);
      }
      if(file_exists(DOKU_PLUGIN . 'dwcommits/conf/default.local.ini')) {  
         $ini_array = parse_ini_file(DOKU_PLUGIN . 'dwcommits/conf/default.local.ini', true);  
            foreach ($ini_array as $name=>$a) {
                if($name == 'other_gits') {
                    foreach($a as $git_dir) {
                        $this->repros[] = $this->html_option($git_dir);
                   }
                }
                if($name == 'dwc_gits') {
                    foreach($a as $git_dir) {
                        $this->repros[] = $this->html_option(DW_COMMITS_DB . $git_dir);
                   }
               }
           }
        }
   }

   function get_repros() {        
      echo implode("\n",$this->repros);
   }
  
   function set_branches() {
       if(!$this->chdir()) return false;
       $this->branches = array();
       exec("$this->git branch",$retv, $exit_code); 
       if($exit_code) return false;   
       foreach ($retv as $branch) {
        if(preg_match('/\*(.*)/',$branch,$matches)) {        
           $this->selected_branch = $matches[1]; 
             $this->branches[] = $this->html_option($matches[1],true);  
        }
        else {
            $this->branches[] = $this->html_option($branch);           
        }
       }        
   }

   function html_option($val, $selected=false) {
      $val = trim($val);
      if(!$selected) {
        return '<option value="'. $val .'">' . $val .'</option>';
      }
      return '<option value="' .$val . '" selected>' . $val . '</option>';
   }

   function get_branches() {        
      echo implode("\n",$this->branches);
   }
   
   function selected_branch() {        
          if($this->selected_branch) return $this->selected_branch;
          $this->selected_branch = 'master'; //needed for db column if and when implemented
          return 'master';  
   }

  function selected_repro() {        
          if(isset($_REQUEST['dwc__repro']) && $_REQUEST['dwc__repro']) return $_REQUEST['dwc__repro'];
          return $this->default_repro;  
   }

  /*  Seems git status sometimes returns exit code of 1 even when 0 is expected 
      So exit code > 0 can't be trusted to report genuine error. Confirmed via Google
  */
   function get_status() {
      $this->status_message = array();
      if(!$this->chdir()) return false;
         exec("$this->git status",$retv, $exit_code);    
         $this->status_message = 
              array_merge(array(getcwd(),"git status"),$this->status_message,$retv);  
        return true;

   }
  
   function error($which, $type=-1) {
      $path = $this->path;
      $msgs = array(
           "Cannot find cloned git at $path",  // 0
           "Cannot access $path. The entire directory and all its contents must be read/write for the web server.", // 1
           "Cannot fetch from github",  // 2
           "Unable to merge",  // 3
           "Bad Query Construct. Please notify the plugin author.",  // 4
           "Unable to write to dbnames.ser file.",  // 5
           "Please check your query. You seem not to have entered any search terms.", // 6
           "Unable to restore backup; you may not have a backup saved." // 7
      );
      
      msg($msgs[$which],$type);
     
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

     $months = array('Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,
              'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12);

    $count = 0;
    $start_number = 0;
    if(!$timestamp_start) {
       $timestamp_start = mktime(0,0,0,11,11,2010);
    }

     $results = $this->sqlite->query("select count(*) from git_commits");  
  
   $start_number = $this->res2single($results);  
     $since =  date('Y-m-d',$timestamp_start);
     if(!preg_match('/^\d\d\d\d-\d\d-\d\d$/',$since)) {
          $since = '2010-11-11';
     }
    $handle = popen("$this->git log --since=$since", "r");
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
     $results = $this->sqlite->query("select count(*) from git_commits");  
     $end_number = $this->res2single($results);  

     return array($end_number-$start_number, $end_number);
   
}

    function insert($author,$timestamp,$gitid,$msg,$table) {     

        $prefix =  substr( $gitid , 0, 15 );  
        
        if($this->sqlite->query("INSERT OR IGNORE INTO $table (author,timestamp,gitid,msg,prefix,gitbranch) VALUES (?,?,?,?,?,?)", 
                     $author,$timestamp,$gitid,$msg,$prefix,$this->selected_branch)){              
              return true;
        }
        else {
          return false;
        }

    }
   

  function select_all($q=array()) {
        $temp_str = "";
        $query = count($q) ? $q: $_REQUEST['dwc_query'];
        $msg = "";
        $author = "";
        $branch = "";
        $term1 = "";
        $term2 = "";
        $date_1 = "";
        $date_2 = "";

        foreach($query as $col=>$val) {
             switch ($col) {
                case 'author':
                   $author = $this->construct_term('author',$val);
                   break;
                case 'branch':
                   if($val != 'any') {
                     $branch = $this->construct_term('gitbranch',$val);
                   }
                   break;
               case 'terms_1':
                  $term1 = $this->construct_term('msg',$val);
                  break;
               case 'terms_2':
                 $term2 = $this->construct_term('msg',$val);
                 break;
               case 'd1':
                 $date_1 =  $this->get_timestamp($val);        
                 break;
               case 'd2':
                 $date_2 =  $this->get_timestamp($val);        
                 break;

             }
        }
    
        $msg = $this->construct_msg_clause($term1,$term2,$query['OP_1']);  
        $ab_clause = $this->construct_ab_clause($author,$branch,$query['OP_2'],$msg);  
        $attach = ($ab_clause || $msg) ? true : false;
        $date_clause = $this->construct_date_clause($date_1,$date_2,$attach);
        $q = $msg . $ab_clause . $date_clause;    
        if(!$q) {         
         $this->error(6,1);
         return array();
        }
        $res = $this->sqlite->query("SELECT timestamp,author,msg,gitid,gitbranch FROM git_commits WHERE $q ORDER BY timestamp DESC");
        if(!$res) {
          $this->error(4);
          return false;
        }
 
        $arr = $this->sqlite->res2arr($res);

        return array($arr,  $q);
     
  }

  function format_result_table($arr, $q=false) {

        $this->set_commit_url();
        $query = $q ? $q: $_REQUEST['dwc_query'];

        $regex = $this->get_hilite_regex($query);  

        $output = "";
        foreach($arr as $row) {
           $output .= $this->format_tablerow($row,$regex);  
           $output .= "<tr><td colspan='3' style='border-bottom: 1px solid black;'>\n";
        }
        return '<table width="85%" cellspacing="4">' . $output . '</table>';       
  }

 function format_tablerow($row,$regex) {
        $result = ""; 

        $msg = "";        
        $date = "";
        $commit = "";
        $branch = "";
        $author = "";
        global $conf; 
        foreach ($row as $col=>$val) {
            
            
            if($col == 'msg'){                                               
                $msg = hsc($val);       
                if($regex) {        
                    $msg = preg_replace($regex,"<span class='dwc_hilite'>$1</span>",$val); 
                }
                   
            }
            elseif($col == 'timestamp') {  
                $date = date("D M d H:i:s Y" ,$val);
            }
            elseif($col == 'gitid') {
                if($this->commit_url) {                 
                  $commit = $this->format_commit_url($val);                
                }
                else $commit = $val;           
            }
            elseif($col == 'gitbranch') {
                $branch = $val;
            }
           elseif($col == 'author') {
                list($name,$email) = explode('<',$val);
                $email = trim($email,'>');
                $author = "<a href ='mailto: $email' title='$email'>$name</a>";
            }
               
           
        }

        return "<tr><td rowspan='2'><td nowrap style='padding-right:2px;'>$date</td><td><b>Commit:&nbsp;&nbsp;</b> $commit</td>" .
              "<tr><td rowspan = '2' valign='top' style='padding-right:2px;'><b>Author:&nbsp;&nbsp;</b>$author<br /><b>Branch:&nbsp;&nbsp;</b>$branch</td>" .
               "<td style='padding:2px;'>$msg</td>"; 

              
  }
   
 
 function get_hilite_regex($query) {
        $term1 = $query['terms_1'];
        $term2 = $query['terms_2'];
       
        $regex = "";

        if($term1 && $term2) {
              $regex = "/($term1|$term2)/ims";       
        }
        elseif($term1) {
             $regex = "/($term1)/ims";       
        }
       return $regex;
 }
  function format_result_plain($arr, $q=false) {
        $this->set_commit_url();
        $query = $q ? $q: $_REQUEST['dwc_query'];

        $regex = $this->get_hilite_regex($query);  

        $output = "";
        foreach($arr as $row) {
           $output .= $this->format_row($row,$regex);  
           $output .= "\n---------\n";
        }

        return '<pre>' . $output . '</pre>';       
  }

  function format_row($row,$regex) {
        $result = ""; 
        
        foreach ($row as $col=>$val) {
            
            
            if($col == 'msg'){  
                $val = wordwrap($val, 80,"\n");              
                $val = hsc($val);       
                if($regex) {        
                    $val = preg_replace($regex,"<span class='dwc_hilite'>$1</span>",$val); 
                }
            }
            elseif($col == 'timestamp') {               
                $result .= "<b>Date: </b>";
                $val = date("D M d H:i:s Y" ,$val);
            }
            elseif($col == 'gitid') {
                if($this->commit_url) {
                  $result .= 'Commit (URL): '; 
                  $result .= $this->format_commit_url($val);
                  continue;
                }
                else $result .= '<b>Commit: </b>';           
            }
            elseif($col == 'gitbranch') {
                $result .= '<b>Branch: </b>';
            }
           elseif($col == 'author') {
                $result .= '<b>Author: </b>';
            }
            else {
               $result .= "<b>$col: </b>";
            }
               
            $result .= "$val\n";
        }
        return $result;   
  }
   

  function format_commit_url($val) {
     $url = $this->commit_url . $val;  
     return "<a href='$url' target='commitwin'>$val</a>\n";
  }

  function construct_term($col,$val) {
         if($val) return " $col LIKE '%$val%' ";
         return "";           
  }

  function construct_date_clause($d1,$d2, $attach) {
     $q = "";
     $op  = $attach ? 'AND' : "";
     if($d1 && $d2) {
       $q = " $op ( timestamp > $d1 AND timestamp < $d2 )";    
     }
     elseif($d1) {
        $q = " $op timestamp > $d1 ";    
     }
     elseif($d2) {
       $q = " $op timestamp < $d2 ";
     }
     return $q;

  }
  function construct_ab_clause($author,$branch,$op,$msg){
        
        $q = "";  
        if($author) {
            if($msg) {
              $OP = ($op == 'AND') ? ' AND ' : ' OR ';  
              $q = " $OP $author ";
            }
            else $q = " $author ";
        }
        if($branch) {
            if($author || $msg){
                 $q .= " AND $branch "; 
            }
            else 
               $q = " $branch ";
            
        }
        return $q; 
      
  }

  function construct_msg_clause($phrase1,$phrase2,$op) {
        if(!$phrase1) return "";
        if(!$phrase2) return $phrase1; 
        $OP = ($op == 'AND') ? ' AND ' : ' OR ';  
        return " ($phrase1) $OP ($phrase2) ";
  }

  function get_timestamp($dstr) {
      if(!$dstr) return "";  
      if(preg_match('/[a-zA-Z]/',$dstr)){
         msg('Date wasn\'t set:' . $dstr , -1);
         return false;      
      }
      
      list($month,$day,$year) = explode('-',$dstr); 
      if((strlen($month) < 2) || (strlen($year) < 4)||(strlen($day) < 2)  ) {
                msg('Incorrect date format: ' . $dstr  , -1);
      }

      if((strlen($month) + strlen($year) + strlen($day) > 8 )  ) {
                msg('Incorrect date format: ' . $dstr , -1);
      }

      return mktime (0,0,0, $month, $day, $year);
   }

   function res2single($res) {
        if(method_exists($this->sqlite,res2single)){
            return $this->sqlite->res2single($res);
        }
        $arr = $this->sqlite->res2row($res);
        list($key,$val) = each($arr);
        return($val);
}

function restore_backup() {
   $names_fname = dirname(__FILE__).'/db/dbnames.ser'; 
   $backup = $names_fname . '.prev';
   if(!file_exists($names_fname)) {
      return "backup file not found";
   }
   if(file_exists($names_fname) && file_exists($backup)) {
          @unlink($names_fname);
  }         
  else {
         $this->error(7);
         return; 
  }
  if(rename($backup,$names_fname)) {
       return "Backup restored";
   }

  $this->error(7);
   return "";
}

function prune($del) {
   $names_fname = dirname(__FILE__).'/db/dbnames.ser'; 
   $msg = "";
   $meta = DOKU_INC . 'data/meta/';
   if(file_exists($names_fname)) {
        $inf_str = file_get_contents ($names_fname);
        $inf = unserialize($inf_str);
        if(!$inf) return;           
        foreach($_REQUEST[prune] as $db=>$key) {
           unset($inf[$key]);  
           list($prefix,$index) = explode('_',$db);
           $url = 'url' . $index;
           $git = 'git' . $index;
           if(isset($inf[$url])) {
              unset($inf[$url]);
           }
           if(isset($inf[$git])) {
              unset($inf[$git]);
           }

           $sql = $meta . $db . '.sqlite';
           if($del && file_exists($sql)) {
             if(!unlink($sql)) {
                $msg .= "Could not delete $sql<br />";
               
             }
           }  
        }

       $backup = $names_fname . '.prev';
       if(file_exists($backup)) {
           @unlink($backup);
       }         
       if(rename($names_fname, $backup)) {
          $msg .= "Old data saved in backup $backup";
          if($del) {
            $msg .= "<br />This backup may contain references to deleted sqlite dbase files<br />";
          }
       }
       $this->save_dbnames_ser($names_fname,serialize($inf));
   }
 
  return $msg;
}

/* Read dbnames.ser and return data found there */
function db_data($inf = false) {
     $output = "";
     $filename = DW_COMMITS . 'db/dbnames.ser';
     if(!$inf) {
        $inf_str = file_get_contents ($filename);
        $inf = unserialize($inf_str);     
     }

    foreach($inf as $val=>$entry) {     
       if(preg_match('/dwcommits_(\d+)/',$entry, $matches)) {                         
           $output .= "<b>" . $this->getLang('db_file'). "</b> $entry";
           $output .= '&nbsp;&nbsp;&nbsp;<input type = "checkbox" value = "'. $val. '" name="prune[' .$entry. ']">';
           $output .= "<br />";
           if(($url = $this->dwc_element('url', $matches[1], $inf))!== false) {
               $output .= "<b>" . $this->getLang('remote_url'). "</b> $url<br />";
           }
        
           $git = $this->dwc_element('git', $matches[1], $inf);
           if($git !== false) {
             if(!file_exists($git)) {
                $output .= "<b>". $this->getLang('git_missing'). "</b>  $git<br />";
             }
             else $output .= "<b>". $this->getLang('git_local'). "</b> $git<br />";
           }
          $output .= "<br />";
       }
    }
    return $output;
 }
 
 function dwc_element($prefix, $suffix, $ar) {
    $inx = $prefix . $suffix;
    if(isset($ar[$inx])) {
         return $ar[$inx];
    }
    return false;
       
 }

  function recreate_table($timestamp_start) {

     $this->sqlite->query("DROP TABLE git_commits");
     $this->sqlite->query('CREATE TABLE git_commits(author TEXT,timestamp INTEGER,gitid TEXT,msg TEXT, prefix TEXT, gitbranch TEXT, PRIMARY KEY(prefix,timestamp))');     
     $this->populate($timestamp_start);
     $results = $this->sqlite->query("select count(*) from git_commits");  
     $res = $this->res2single($results);  
     return $res;
 
  }

    function write_debug($data) {
      return;
      global $dwc_dbg_log;
      static $handle;
      if(!$handle) {
      if(!$handle = fopen($dwc_dbg_log, 'a')) {
        return;
        }
      }
       if(is_array($data)) $data = print_r($data,true); 
       fwrite($handle, "$data\n");
    }
}


