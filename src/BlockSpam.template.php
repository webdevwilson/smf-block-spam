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
					<td class="smalltext">',
                                            $txt['blockspam_admin_top'],
                                       '</td>
                                </tr>
                        </table>
                        <table border="0" width="100%" cellspacing="0" cellpadding="4" align="center">
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0" style="margin-left: 10px;">
                                            <tr>
                                                <td class="maintab_first">&nbsp;</td>
                                                <td class="maintab_back">
                                                    <a href="', $scripturl, '?action=blockspam&sa=messages" />', $txt['blockspam_flagged_posts'], '
                                                </td>
                                                <td class="maintab_back">
                                                    <a href="', $scripturl, '?action=blockspam&sa=settings" />', $txt['blockspam_settings'], '
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                        </table>';
    if( isset($context['blockspam_message']) ) {
        echo '          <p style="color: #0c0; font-weight: bold;">', $context['blockspam_message'], '</p>';
    }
    if( !isset($_REQUEST['sa']) || $_REQUEST['sa'] == 'messages') {
        echo '
                        <script type="text/javascript">
                            var blockSpam = {
                                postIds: new Array(', implode(',', $context['blockspam_flagged_postids']), '),
                                toggleAll: function() {
                                    var onOff = document.getElementById(\'blockspam_check_all\').checked;
                                    for(var i=0; i < this.postIds.length; i++) {
                                        document.getElementById("blockspam_p" + this.postIds[i]).checked = !onOff;
                                    }
                                }
                            };
                        </script>
                            <form action="', $scripturl, '?action=blockspam&sa=messages" method="post">
                            <table border="0" width="100%" cellspacing="0" cellpadding="4" align="center" class="tborder">
                                <tr class="catbg">
                                        <td align="left" colspan="8">
                                                <b>', $txt[139], ':</b> ', $context['blockspam_page_index'], '
                                        </td>
                                </tr>                                
                                <tr class="titlebg">
                                    <td>', $txt['blockspam_flagged_posts'], '</td>
                                </tr>
                                <tr>
                                    <td>
                                      <p>', str_replace('##NUM_FLAGGED##', $modSettings['blockSpamCaughtMessages'], $txt['blockspam_number_flagged']), '</p>
                                      <input type="submit" name="do" value="', $txt['blockspam_delete_all'], '" onclick="return confirm(\'', $txt['blockspam_confirm_delete_all'], '\');" /><br />
                                      <input type="submit" name="do" value="', $txt['blockspam_delete'], '" />&nbsp;
                                      <input type="submit" name="do" value="', $txt['blockspam_not_spam'], '" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="2" cellspacing="0" border="0" width="100%">
                                            <thead>
                                                <tr style="text-align: left; background-color: #ccc;">
                                                    <th style="width: 20px;"><input type="checkbox" id="blockspam_check_all" onmouseup="blockSpam.toggleAll()" /></th>
                                                    <th>', $txt['blockspam_flagged_name'], '</th>
                                                    <th>', $txt['blockspam_flagged_date'], '</th>
                                                    <th>', $txt['blockspam_flagged_subject'], '</th>
                                                    <th>', $txt['blockspam_flagged_body'], '</th>
                                                </tr>
                                            </thead>
                                            <tbody>
            ';
        if( count($context['blockspam_flagged_posts']) == 0 ) {
            echo '                              <tr><td colspan="5">', $txt['blockspam_no_flagged_messages'], '</td></tr>';
        } else {
            foreach ($context['blockspam_flagged_posts'] as $i => $p) {
                echo '
                                                <tr class="windowbg', ( $i % 2 == 0 ? '' : '2' ), '">
                                                    <td><input type="checkbox" id="blockspam_p', $p['ID_MSG'], '" name="p[', $p['ID_MSG'], ']" /></td>
                                                    <td><a href="', $scripturl, '?action=profile;u=', $p['ID_MEMBER'], '">', $p['posterName'], '</a></td>
                                                    <td>', timeformat($p['posterTime']), '</td>
                                                    <td>', $p['subject'], '</td>
                                                    <td>', parse_bbc($p['body']), '</td>
                                                </tr>
                ';
            }
        }
        echo '                              </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            </form>';

    } else if ($_REQUEST['sa'] == 'settings') {
        echo '
                            <form action="', $scripturl, '?action=blockspam&sa=settings" method="post">
                            <table border="0" width="100%" cellspacing="0" cellpadding="4" align="center" class="tborder">
                                <tr class="titlebg">
					<td>', $txt['blockspam_settings'], '</td>
				</tr>
                                <tr class="windowbg2">
                                    <td>',
                                        $txt['blockspam_apikey'], ': ',
                                        '<input type="text" style="float: right;" name="s[blockSpamAkismetKey]" size="32" value="', $modSettings['blockSpamAkismetKey'], '" />',
                                        '<p><span class="smalltext">', $txt['blockspam_apikey_message'], '</span></p><br />',
                                    '</td>
                                </tr>
                                <tr>
                                    <td>',
                                    $txt['blockspam_post_threshold'], ': ',
                                    '<input type="text" style="float: right;" name="s[blockSpamPostsThreshold]" size="32" value="', $modSettings['blockSpamPostsThreshold'], '" />',
                                    '<p><span class="smalltext">', $txt['blockspam_post_threshold_message'], '</span></p><br />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="submit" value="', $txt['blockspam_settings_submit'], '" />
                                    </td>
                                </tr>
                            </table>
                            </form>';
    }
}