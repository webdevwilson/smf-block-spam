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

    // The main admin page
    if (isset($_REQUEST['action']) || $_REQUEST['action'] == 'main') {


        // If we're saving an API key
        if (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'saveAPIKey') {
            // Save the setting
            updateSettings(array(
                'akismetAPIKey' => isset($_POST['apikey']) ? strtr(htmlspecialchars($_POST['apikey'], ENT_QUOTES), array("\r" => '', "\n" => '', "\t" => '')) : '0',
            ));
            $context['akismet_message'] = $txt['akismet_apikey_saved'];
        }
        // Delete all spam messages
        elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'deleteall') {
            // Delete the posts...
            db_query("DELETE FROM {$db_prefix}messages WHERE spam = 1", __FILE__, __LINE__);
            // ... and the topics...
            db_query("DELETE FROM {$db_prefix}topics WHERE spam = 1", __FILE__, __LINE__);
            // TODO: Update statistics so they don't go weird
            // Tell user everything was OK :)
            $context['akismet_message'] = $txt['akismet_deleted'];
        }
        // Oh boy... We made a mistake... Those posts aren't spam!!
        // Never fear, help is on its way! :-)
        elseif (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'notspam') {
            // Nothing chosen? Huh?!
            if (!isset($_POST['notspam']))
                $context['akismet_message'] = $txt['akismet_error_notspam'];
            else {
                // Require the Akismet class
                require_once($sourcedir . '/Akismet.class.php');
                // Create a new instance of the class
                $akismet = new Akismet($scripturl, $modSettings['akismetAPIKey']);

                // Make sure all values are numeric
                foreach ($_POST['notspam'] as $key => $notSpam)
                    $_POST['notspam'][$key] = (int) $notSpam;

                // Make them into a nice list for the SQL queries
                $notspam_query = 'ID_MSG = ' . implode(' OR ID_MSG = ', $_POST['notspam']);
                // Also, a list for the topic query
                $notspam_query2 = 'ID_FIRST_MSG = ' . implode(' OR ID_FIRST_MSG = ', $_POST['notspam']);

                // Firstly, mark all the posts as non-spam
                db_query("
						UPDATE {$db_prefix}messages
						SET spam = 0
						WHERE $notspam_query", __FILE__, __LINE__);
                // Also, if it was a topic, mark the topic as non-spam as well
                db_query("
						UPDATE {$db_prefix}topics
						SET spam = 0
						WHERE $notspam_query2", __FILE__, __LINE__);

                // -- Now, time to tell Akismet about this! --
                // Get the post information
                $result = db_query("
								SELECT ID_MSG, subject, posterName, posterEmail, posterIP, body
								FROM {$db_prefix}messages
								WHERE {$notspam_query}", __FILE__, __LINE__);
                // Loop through all the posts
                while ($row = mysql_fetch_assoc($result)) {
                    // Set all stuff to pass to Akismet
                    $akismet->setAuthor($row['posterName']);
                    $akismet->setAuthorEmail($row['posterEmail']);
                    $akismet->setUserIP($row['posterIP']);
                    $akismet->setContent($row['body']);
                    $akismet->setPermalink($scripturl . '?topic=' . $row['ID_MSG']);
                    $akismet->setType('smf-notspam');

                    // Actually mark it as non-spam
                    $akismet->submitHam();
                }

                // Tell the user everything was OK
                $context['akismet_message'] = $txt['akismet_notspam_saved'];
            }
        }
    }
    // Start with an empty spam array
    $context['akismet_spam'] = array();

    // Get all the spam messages
    $result = db_query("
					SELECT ID_MSG, subject, posterName, posterEmail, posterIP, body, posterTime
					FROM {$db_prefix}messages
					WHERE spam = 1
					ORDER BY posterTime DESC", __FILE__, __LINE__);
    // Set the spam count
    $context['spam_count'] = mysql_num_rows($result);
    // For each spam post
    while ($row = mysql_fetch_assoc($result)) {
        $context['akismet_spam'][$row['ID_MSG']] = array(
            'ID_MSG' => $row['ID_MSG'],
            'subject' => $row['subject'],
            'posterName' => $row['posterName'],
            'posterEmail' => $row['posterEmail'],
            'posterIP' => $row['posterIP'],
            'posterTime' => $row['posterTime'],
            'date' => timeformat($row['posterTime']),
            'body' => $row['body'],
        );
        // Censor it
        censorText($context['akismet_spam'][$row['ID_MSG']]['subject']);
        censorText($context['akismet_spam'][$row['ID_MSG']]['body']);

        // For backwards compatibility use doUBBC instead of parse_bbc
        $context['akismet_spam'][$row['ID_MSG']]['body'] = doUBBC($context['akismet_spam'][$row['ID_MSG']]['body']);
    }

    // Set the page title
    $context['page_title'] = $txt['akismet_conf'];
    // Load the template
    loadTemplate('Akismet');
}