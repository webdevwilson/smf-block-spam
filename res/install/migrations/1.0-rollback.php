<?php

if (!defined('SMF'))
	die('something fishy going on');

db_query("DROP TABLE `{$db_prefix}spam_messages`", __FILE__, __LINE__);
db_query("DELETE FROM `{$db_prefix}settings`
    WHERE `variable` IN ('blockSpamCaughtMessages','blockSpamAkismetKey','blockSpamPostsThreshold')", __FILE__, __LINE__);

?>