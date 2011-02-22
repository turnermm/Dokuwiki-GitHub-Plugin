<?php
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DW_COMMITS_DB')) define('DW_COMMITS_DB',DOKU_INC.'lib/plugins/dwcommits/db/');
$conf['default_date'] =  '11-11-2010';
$conf['default_git'] =  DW_COMMITS_DB . 'git';
$conf['git_binary'] = '/usr/bin/git';
$conf['auto_id'] = 'git';


