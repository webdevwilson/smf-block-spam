<?php

/* * ********************************************************************************
 * BlockSpam.template.php                                                          *
 * Template for ${project.description} administration page                         *
 * **********************************************************************************
  ${header.txt}
 * ******************************************************************************** */

// administration page
function template_main() {
    global $txt, $modSettings, $scripturl, $context;
    echo '
			<table border="0" width="100%" cellspacing="0" cellpadding="4" align="center" class="tborder">
				<tr class="titlebg">
					<td>', $txt['blockspam_plugin'], '</td>
				</tr>
				<tr class="windowbg2">
					<td>',
                                            $txt['blockspam_admin_top'],
                                            ((isset($context['blockspam_message'])) ? '<span style="color: red; font-weight: bold;">' . $context['blockspam_message'] . '</span><br />' : ''), '
                                        </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
                                            <tr>
                                                <td>
                                                    <a href="', $scripturl, '" />', $txt['blockspam_flagged_posts'], '
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <a href="', $scripturl, '" />', $txt['blockspam_settings'], '
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>';
    
    if( !isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'messages') {
        echo '
                                <tr class="catbg">
                                    <td>', $txt['blockspam_flagged_posts'], '</td>
                                </tr>
                                <tr class="windowbg2">
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <thead>
                                                <tr>
                                                    <th>', $txt['blockspam_flagged_name'], '</th>
                                                    <th>', $txt['blockspam_flagged_date'], '</th>
                                                    <th>', $txt['blockspam_flagged_subject'], '</th>
                                                    <th>', $txt['blockspam_flagged_body'], '</th>
                                                </tr>
                                            </thead>
                                            <tbody>
            ';
        foreach ($context['blockspam_flagged_posts'] as $p) {
            echo '
                                                <tr>
                                                    <td>', $p['posterName'], '</td>
                                                    <td>', $p['date'], '</td>
                                                    <td>', $p['subject'], '</td>
                                                    <td>', $p['body'], '</td>
                                                </tr>
                ';
        }
        echo '                              </tbody>
                                        </table>
                                    </td>
                                </tr>';

    } else if ($_REQUEST['sa'] == 'settings') {
        echo '                  <tr class="catbg">
					<td>', $txt['blockspam_settings'], '</td>
				</tr>
                                <form action="', $scripturl, '?action=blockspam&do=updateSettings" method="post">
                                <tr class="windowbg2">
                                    <td>',
                                        $txt['blockspam_apikey'], ': ',
                                        '<input type="text" name="s[blockSpamAkismetKey]" size="32" value="', $modSettings['blockSpamAkismetKey'], '" /><br />',
                                        '<span class="smalltext">', $txt['blockspam_apikey_message'], '</span><br />',
                                    '</td>
                                </tr>
                                <tr>
                                    <td>',
                                    $txt['blockspam_post_threshold'],
                                    '<input type="text" name="s[blockSpamPostsThreshold]" size="32" value="', $modSettings['blockSpamPostsThreshold'], '" /><br />',
                                    '<span class="smalltext">', $txt['blockspam_post_threshold_message'], '</span><br />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="submit" value="', $txt['blockspam_settings_submit'], '" />
                                    </td>
                                </tr>
                                </form>';
    }
    echo '
                            </table>';
}