<?php

/* * ********************************************************************************
 * BlockSpam.php                                                                   *
 * ${project.description} administration page                         *
 * **********************************************************************************
  ${header.txt}
 * ******************************************************************************** */

// If not called by SMF, it's bad!
if (!defined('SMF'))
    die('Hacking attempt...');

// administration
function BlockSpam() {

    global $txt, $context, $db_prefix, $sourcedir, $scripturl, $modSettings;

    isAllowedTo('moderate_forum');
    adminIndex('blockspam');

    // Set the page title
    $context['page_title'] = $txt['blockspam_settings'];

    if ( !isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'messages' ) {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if ($_POST['do'] == $txt['blockspam_delete_all']) {

                db_query("DELETE FROM {$db_prefix}spam_messages", __FILE__, __LINE__);
                $numDeleted = db_affected_rows();
                $context['blockspam_message'] = str_replace('##NUM_DELETED##', $numDeleted, $txt['blockspam_spam_deleted']);
                
            } else if ($_POST['do'] == $txt['blockspam_delete']) {

                $ids = implode(',', array_keys($_POST['p']));
                db_query("DELETE FROM {$db_prefix}spam_messages WHERE ID_MSG IN ({$ids})", __FILE__, __LINE__);
                $numDeleted = db_affected_rows();
                $context['blockspam_message'] = str_replace('##NUM_DELETED##', $numDeleted, $txt['blockspam_spam_deleted']);
            
                
            } else if ($_POST['do'] == $txt['blockspam_not_spam']) {
                
            }
        }

        // get count of spam messages
        $result = db_query("SELECT COUNT(*) FROM {$db_prefix}spam_messages", __FILE__, __LINE__);
        $row = mysql_fetch_array($result);
        $context['blockspam_count'] = $row[0];
        
        // get messages
        $sql = "SELECT * FROM {$db_prefix}spam_messages LIMIT {$_REQUEST['start']}, {$modSettings['defaultMaxMembers']}";
        $context['blockspam_flagged_posts'] = array();
        $context['blockspam_flagged_postids'] = array();
        $result = db_query($sql, __FILE__, __LINE__);
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $context['blockspam_flagged_posts'][] = $row;
            $context['blockspam_flagged_postids'][] = $row['ID_MSG'];
        }
        $context['blockspam_page_index'] = constructPageIndex($scripturl . '?action=blockspam;sa=messages;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $context['blockspam_count'], $modSettings['defaultMaxMembers']);
        
    } else if ($_REQUEST['sa'] == 'settings') {
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            updateSettings($_POST['s'], true);
            $context['blockspam_message'] = $txt['blockspam_settings_updated'];
        }
    }

    loadTemplate('BlockSpam');
}