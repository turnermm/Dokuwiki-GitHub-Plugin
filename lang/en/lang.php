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
//$lang['btn_dbinfo'] = 'DB Info';
//$lang['header_dbinfo'] = 'All registered databases and associated info: ';
$lang['maintenance'] = 'Maintenance';
$lang['header_remote_url'] = 'Set URL of Remote Repository';
$lang['explain_remote_url'] = 'You can either set it manually or let dwcommit set it for you. If already set, it will appear in the text-box.';
$lang['btn_remote_url'] = 'Get Remote URL';
$lang['btn_set_remote'] = "Set Remote URL";
$lang['remote_url_text'] = "Remote URL";

$lang['header_git'] = 'Update Git';
$lang['explain_git'] = 'These functions can be used to bring your Git up-to-date';

$lang['header_git_merge'] = 'Merge fetched commits';
$lang['btn_merge'] = 'Merge';

$lang['header_git_fetch'] = 'Fetch remote commits';
$lang['btn_fetch'] = 'Fetch';

$lang['header_git_commit'] = 'Commit local files';
$lang['btn_commit'] = 'Commit';
$lang['header_git_add'] = 'Add local files';
$lang['btn_add'] = 'Add';


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
$lang['btn_repos'] =  'Change Repo'; //'Repo';
$lang['repo_names'] = 'Select and Change Repo';

$lang['repro_switched'] = 'Repo changed to';
$lang['git_info'] = 'Help';
$lang['header_git_query'] = 'Database Queries';
$lang['git_query'] = 'Query';
$lang['header_git_MsgArea'] = 'Message Area';
$lang['btn_msg_small'] = 'Smaller';
$lang['btn_msg_big'] = 'Bigger';
$lang['btn_close_all'] = 'Close All';
$lang['current_db'] = 'Current Database: ';

$lang['q_srch_term'] = 'Search Term';
$lang['q_srch_type'] = 'Search Type';
$lang['q_author'] = 'Author';
$lang['q_branch'] = 'Branch';
$lang['q_start_date'] = 'Start Date';
$lang['q_end_date'] = 'End Date';
$lang['q_submit'] = 'Submit Query';
$lang['q_date_fmt'] = 'Date Format';
$lang['q_output'] = 'Output';
$lang['q_table'] = 'Table';
$lang['q_plain'] = 'Plain text';
$lang['div_close'] = 'Close';

$lang['prune'] = 'Prune Entries';
$lang['prune_del'] = 'Delete Entries';
$lang['prune_restore'] = 'Restore Backup';
$lang['db_file'] = 'Database File:';
$lang['git_local'] = "Local Git: ";
$lang['git_missing'] = "Local Git Missing: ";
$lang['remote_url'] = 'Remote URL: ';
$lang['explain_maint'] ="Check for either pruning or deletion";
