<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Myron Turner <turnermm02@shaw.ca>
 */

// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';

// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Dokuwiki Commits'; 

$lang['btn_init'] = 'Initialize';
$lang['btn_update'] = 'Update';

$lang['invalid'] = 'invalid input detected!';
$lang['input_year'] = 'Year';
$lang['month'] = 'Month';
$lang['day'] = 'Day';

$lang['header_init']= 'Initialize Sqlite Database';
$lang['header_update'] = 'Update Database';
$lang['explain_init'] = 'Year, month, and day from which to initialize. With each change of date, a new database is created.';
$lang['explain_update'] = 'This function will add commits to the Sqlite database'.
                          '  beginning at the Year, month, and day entered below. ' .
                          ' It will not overwrite previously entered data.';

$lang['header_git'] = 'Update Git';
$lang['explain_git'] = 'These functions can be used to bring your Git up-to-date';

$lang['header_git_merge'] = 'Merge fetched commits';
$lang['btn_merge'] = 'Merge';

$lang['header_git_fetch'] = 'Fetch remote commits';
$lang['btn_fetch'] = 'Fetch';

$lang['header_git_commit'] = 'Add and Commit local files';
$lang['btn_commit'] = 'Commit';

$lang['header_additional'] = 'Advanced Git Options';
$lang['sql_opts'] = 'Update/Create SQL Database';
$lang['git_opts'] = 'Update Git';	
$lang['git_advanced_opts'] = $lang['header_additional'] ;
$lang['git_repos'] = 'Repos/Branches';

$lang['header_git_pull'] = 'Update git (pull)';
$lang['btn_pull'] = 'Pull';

$lang['header_git_status'] = 'Check current status of git';
$lang['btn_status'] = 'Check';

$lang['btn_branch'] =  'Change branch'; //'Branch';
$lang['branch_names'] = 'Select and Change Branch';

$lang['btn_repos'] =  'Change Repo'; //'Branch';
$lang['repo_names'] = 'Select and Change Repo';
