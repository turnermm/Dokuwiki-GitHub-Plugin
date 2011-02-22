<?php
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DW_COMMITS_DB')) define('DW_COMMITS_DB',DOKU_INC.'lib/plugins/dwcommits/db/');

$dwc_dates = array('11-11-2010');
$dwc_gits = array(DW_COMMITS_DB . 'git');

if(file_exists(DOKU_PLUGIN . 'dwcommits/conf/default.local.ini')) { 

 $ini_array = parse_ini_file(DOKU_PLUGIN . 'dwcommits/conf/default.local.ini', true);  
 foreach ($ini_array as $name=>$a) {
      if($name == 'dates')$dwc_dates = array_merge($dwc_dates, $a);
      if($name == 'other_gits')$dwc_gits = array_merge($dwc_gits, $a);
      if($name == 'dwc_gits') {
           foreach($a as $git_dir) {
            $dwc_gits[] = DW_COMMITS_DB . $git_dir;
           }
      }
 }
}
$meta['default_date']  = array('multichoice','_choices' => $dwc_dates);
$meta['default_git'] = array('multichoice','_choices' => $dwc_gits);
$meta['git_binary'] = array('string');
$meta['auto_id'] = array('string');

