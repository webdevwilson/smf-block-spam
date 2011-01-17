<?php

function BlockSpamCheckMessage($msgOptions, $topicOptions, $posterOptions) {

    global $scripturl, $user_info, $modSettings, $db_prefix;

    require_once( dirname(__FILE__) . '/Akismet.class.php' );

    // If the subject is 'viagra-test-123', then mark it as spam (this is a test)
    $isTest = $msgOptions['subject'] == 'viagra-test-123';
    if ($user_info['posts'] < $modSettings['blockSpamPostThreshold'] || $isTest) {

        $apiKey = $modSettings['blockSpamAkismetKey'];
        if ($apiKey && $apiKey != '') {

            $akismet = new Akismet($scripturl, $apiKey);
            if ($isTest) {
                $author = 'viagra-test-123';
            } else {
                $author = $posterOptions['name'];
            }

            if (!empty($topicOptions['id'])) {
                $permaLink = $scripturl . '?topic=' . $topicOptions['id'];
            } else {
                $permaLink = $scripturl . '?board=' . $topicOptions['board'];
            }

            $comment = array('body' => $msgOptions['body'],
                'author' => $author,
                'email' => $posterOptions['email'],
                'type' => 'smf-post',
                'permalink' => $permaLink);

            $akismet->setComment($comment);
            $postedSpam = $akismet->isSpam();
          
            if ($postedSpam === true) {
                db_query("UPDATE {$db_prefix}settings SET value = value + 1 WHERE variable = 'blockSpamCaughtMessages'", __FILE__, __LINE__);
            }
            return $postedSpam === true;
        }
    }
}