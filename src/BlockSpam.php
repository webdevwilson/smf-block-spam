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

require_once($sourcedir . '/BlockSpam/BlockSpam.php');

// administration
function BlockSpam() {

    global $txt, $context, $db_prefix, $sourcedir, $scripturl, $modSettings;

    isAllowedTo('moderate_forum');
    adminIndex('blockspam');

    // Set the page title
    $context['page_title'] = $txt['blockspam_settings'];

    if (!isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'messages') {

        $context['blockspam_view'] = 'messages';

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

                $i = 0;
                $ids = implode(',', array_keys($_POST['p']));
                $results = db_query("SELECT * FROM {$db_prefix}spam_messages WHERE ID_MSG IN ({$ids})", __FILE__, __LINE__);
                while ($msg = mysql_fetch_array($results, MYSQL_ASSOC)) {

                    $spamId = $msg['ID_MSG'];

                    // submit ham
                    BlockSpamSubmitHam($msg['body'], $msg['posterName'], $msg['posterEmail'], $msg['ID_BOARD'], $msg['ID_TOPIC']);

                    // insert in to messages
                    db_query("INSERT INTO {$db_prefix}messages (`ID_TOPIC`, `ID_BOARD`, `posterTime`, `ID_MEMBER`,
                                                                `ID_MSG_MODIFIED`, `subject`, `posterName`, `posterEmail`,
                                                                `posterIP`, `smileysEnabled`, `modifiedTime`, `modifiedName`, `body`, `icon` )
                              VALUES (" . $msg['ID_TOPIC'] . ', ' . $msg['ID_BOARD'] . ', ' . $msg['posterTime'] . ', '
                            . $msg['ID_MEMBER'] . ', ' . $msg['ID_MSG_MODIFIED'] . ', \'' . addslashes($msg['subject']) . '\', \''
                            . addslashes($msg['posterName']) . '\', \'' . $msg['posterEmail'] . '\', \'' . $msg['posterIP'] . '\', '
                            . $msg['smileysEnabled'] . ', ' . $msg['modifiedTime'] . ', \'\',\'' . addslashes($msg['body']) . '\', \'' . $msg['icon'] . '\')', __FILE__, __LINE__);
                    $msg['ID_MSG'] = db_insert_id();

                    $newTopic = false;
                    if ($msg['ID_TOPIC'] == 0) {

                        $newTopic = true;

                        // create topic when necessary
                        db_query("INSERT INTO {$db_prefix}topics (`ID_BOARD`, `ID_FIRST_MSG`, `ID_LAST_MSG`, `ID_MEMBER_STARTED`, `ID_MEMBER_UPDATED`)
                                  VALUES (" . $msg['ID_BOARD'] . ', ' . $msg['ID_MSG'] . ', ' . $msg['ID_MSG'] . ', ' . $msg['ID_MEMBER'] . ', ' . $msg['ID_MEMBER'] . ')', __FILE__, __LINE__);
                        $msg['ID_TOPIC'] = db_insert_id();

                        db_query("UPDATE {$db_prefix}messages SET ID_TOPIC = {$msg['ID_TOPIC']} WHERE ID_MSG = {$msg['ID_MSG']}", __FILE__, __LINE__);
                    } else {

                        // determine if this message is the ID_LAST_MSG of an existing topic
                        $results = db_query("SELECT {$db_prefix}messages.posterTime FROM {$db_prefix}topics
                                                LEFT JOIN {$db_prefix}messages ON {$db_prefix}topics.ID_LAST_MSG = {$db_prefix}messages.ID_MSG
                                             WHERE {$db_prefix}topics.ID_TOPIC = {$msg['ID_TOPIC']}", __FILE__, __LINE__);
                        list($lastPostTime) = mysql_fetch_array($results, MYSQL_NUM);
                        if ($msg['posterTime'] > $lastPostTime) {
                            db_query("UPDATE {$db_prefix}topics SET ID_LAST_MSG = {$msg['ID_MSG']}
                                      WHERE ID_TOPIC={$msg['ID_TOPIC']}", __FILE__, __LINE__);
                        }
                    }

                    // increment the member's post count
                    db_query("UPDATE {$db_prefix}members SET posts = posts + 1 WHERE ID_MEMBER={$msg['ID_MEMBER']}", __FILE__, __LINE__);

                    db_query("DELETE FROM {$db_prefix}spam_messages WHERE ID_MSG={$spamId}", __FILE__, __LINE__);

                    $i++;
                }
                $context['blockspam_message'] = str_replace('##NUM_DELETED##', $i, $txt['blockspam_marked_not_spam']);
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

        $context['blockspam_view'] = 'settings';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            updateSettings($_POST['s'], true);
            $context['blockspam_message'] = $txt['blockspam_settings_updated'];
        }
    }

    loadTemplate('BlockSpam');
}