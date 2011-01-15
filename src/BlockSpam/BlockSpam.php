<?php

function BlockSpamCheckMessage() {

    global $scripturl, $user_info, $msgOptions, $posterOptions, $topicOptions, $modSettings;

    require_once( dirname(__FILE__) . '/Akismet.class.php' );

    // If the subject is 'viagra-test-123', then mark it as spam (this is a test)
    $isTest = $msgOptions['subject'] == 'viagra-test-123';
    if ($user_info['posts'] < $modSettings['blockSpamPostThreshold'] || $isTest) {

        $apiKey = $modSettings['blockSpamAkismetKey'];
        if ($apiKey && $apiKey != '') {

            $akismet = new Akismet($scripturl, $apiKey);
            if ($msgOptions['subject'] == 'viagra-test-123') {
                $akismet->setAuthor('viagra-test-123');
            } else {
                $akismet->setAuthor($posterOptions['name']);
            }
            $akismet->setAuthorEmail($posterOptions['email']);
            $akismet->setContent($msgOptions['body']);
            if (!empty($topicOptions['id']))
                $akismet->setPermalink($scripturl . '?topic=' . $topicOptions['id']);
            $akismet->setType('smf-post');

            $postedSpam = $akismet->isSpam();

            if ($postedSpam === true) {
                db_query("UPDATE {$db_prefix}settings SET value = value + 1 WHERE variable = 'blockSpamCaughtMessages'", __FILE__, __LINE__);
            }
            return $postedSpam === true;
        }
    }
}