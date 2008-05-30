<?php
$LANG["faq_q_board_logo"] = "Where do I change the Board-logo?";
$LANG["faq_q_moderators"] = "What are moderators?";
$LANG["faq_q_gzip"] = "What is GZip and why should I enable it?";
$LANG["faq_q_link_to_hp"] = "How can I add a link to my homepage in the \"Board-navigation\"?";
$LANG["faq_q_status_messages"] = "How is it possible to disable the \"Action successfull...\"-pages?";
$LANG["faq_q_add_bbcode"] = "How can I add additional BBCode-tags?";
$LANG["faq_q_subforums"] = "How can I configure how many subforum-links are displayed?";
$LANG["faq_q_reduce_userdata"] = "How can I remove some displayed user-data in the posts?";
$LANG["faq_q_templates"] = "What are templates?";
$LANG['faq_q_logout'] = "I can't logout. How can I fix that?";
$LANG['faq_q_emails_spam'] = "Why do some user don't receive the activation-email?";
$LANG['faq_q_bs_api'] = "How can I display some information from the Board on my homepage?";
$LANG['faq_q_bbceditor_extra_tags'] = 'How can I add some extra-tags to the Java-BBCodeEditor?';

$LANG["faq_a_board_logo"] = "The board-logo is configured via CSS. You can change this in the design-administration. First click on <b>Theme administration</b> -> <b>Themes</b> and click at the theme you like to edit on \"Edit\". There you can see on the left side the groups with a category <b>Main-classes</b> and the item <b>Board-logo</b>. Please click on this link and change the path on the right side to the value you want to.";
$LANG["faq_a_moderators"] = "Moderators are usefull to disengage the administrators. You can add one or more moderators at <b>Forum-administration</b> -> <b>Moderators</b> for each forum which contains topics.<br />
Moderators control forums by for example moving topics which does not fit into a forum to the right forum, edit unclear topic-titles or delete duplicative topics or posts.<br />
You can edit the permissions of the moderators at <b>General</b> -> <b>Settings</b> -> <b>Moderators</b>.<br />
Moderators are not an usergroup but just an additional \"status\". That means an user who moderates one forum has the permissions of the usergroup \"user\" in the whole board. But he/she has in the forum which he/she moderates special rights; the rights of moderators.";
$LANG["faq_a_gzip"] = "<b>GZip is a compression</b>. This can be used in the board to compress the HTML-code which has been created by the board. This reduces the size of the HTML-code very much so that the user has less to download.<br />
Of course this <b>increases the speed very much, especially with slow internetconnections</b>.<br />
But not all servers support GZip. This requires the PHP-module Zlib.<br />
But you can simply activate GZip in the adminarea to test if it works. As long as the board loads \"normal\" and you don't see any cryptical characters everything is like it should be.<br />
If your server supports it it is recommended to activate it.<br />
<br />
Additionally you have the opportunity to activate <b>GZip for the adminarea</b>.<br />
This can (because not all serves support it) be done in the config/userdef.php.<br />
You have to change the value of <b>BS_ENABLE_ADMIN_GZIP</b> to <b>true</b> to enable it. (See line 135)";
$LANG["faq_a_link_to_hp"] = "You can do this in the templates.<br />
Here is an example for adding a link to the homepage http://www.domain.com with the title \"Homepage\":
<ul>
	<li>Please open the <b>template-editor</b> and choose the theme you wish to edit.</li>
	<li>Switch to the <b>templates</b>-directory and click on \"Edit\" at the file <b>headline.htm</b>.</li>
	<li>Approximately at line 25 you can see the following:<br />"
 .'<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
 .htmlspecialchars('<table width="100%" cellpadding="2" cellspacing="1" style="table-layout: fixed;">
	<tr>
		{LOOP top_links as id => link}
		<td title="{link:title}" class="bs_top_menu"
				onmouseover="document.getElementById(\'link{id}\').style.textDecoration = \'underline\';"
				onmouseout="document.getElementById(\'link{id}\').style.textDecoration = \'none\';"
				align="center" style="border: 1px solid #777777;"
				onclick="document.location.href = \'{link:url}\';">
				<a id="link{id}" class="bs_top_menu" href="{link:url}">{link:text}</a>
		</td>
		{ENDLOOP}
	</tr>
</table>')
 .'</pre>'
 ."</li>
	<li>Here you can add another menu-point by changing that part in the following way:<br />"
 .'<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
 .htmlspecialchars('<table width="100%" cellpadding="2" cellspacing="1" style="table-layout: fixed;">
	<tr>
		<td title="Homepage" class="bs_top_menu"
				onmouseover="document.getElementById(\'link20\').style.textDecoration = \'underline\';"
				onmouseout="document.getElementById(\'link20\').style.textDecoration = \'none\';"
				align="center" style="border: 1px solid #777777;"
				onclick="document.location.href = \'http://www.domain.de\';">
				<a id="link20" class="bs_top_menu" href="http://www.domain.de">Homepage</a></td>
		{LOOP top_links as id => link}
		<td title="{link:title}" class="bs_top_menu"
				onmouseover="document.getElementById(\'link{id}\').style.textDecoration = \'underline\';"
				onmouseout="document.getElementById(\'link{id}\').style.textDecoration = \'none\';"
				align="center" style="border: 1px solid #777777;"
				onclick="document.location.href = \'{link:url}\';">
				<a id="link{id}" class="bs_top_menu" href="{link:url}">{link:text}</a>
		</td>
		{ENDLOOP}
	</tr>
</table>')
 .'</pre>'
 ."</li>
	<li>Note that the ID of the link (here \"link20\") has to be unique. That means that if you want to add multiple links you have to use a different, not yet existing ID for each link.<br />
Finally you have to save the changes. That's it :)</li>
</ul>";
$LANG["faq_a_status_messages"] = "You can configure in the <b>config/actions.php</b> after which action a so called \"Status-page\" should be displayed.<br />
For example to disable the status-page after editing a post you have to change line 245 in the config/actions.php:<br />
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
BS_ACTION_EDIT_POST => true,
</pre>
to:
<div style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
BS_ACTION_EDIT_POST => false,
</div>";
$LANG["faq_a_add_bbcode"] = "You can <b>edit and enlarge all BBCode-tags</b> centralized in the <b>config/bbcode.php</b>.<br />
Here an example to add a \"&lt;pre&gt;-tag\", that means a tag to enter a preformated text which is a text that will be displayed exactly as entered:<br />
At first please open the config/bbcode.php. At line 300 you will find the following:
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
...
	'right' => array(
		'tag' => 'right',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '&lt;div align=\"right\"&gt;&lt;!--TEXT--&gt;&lt;/div&gt;',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array(
			'inline' => true,
			'url' => true
		)
	),
...
</pre>
Below this seciton we will insert the new tag.<br />
The <b>definition for the &lt;pre&gt;-tag</b> looks like the following:<br />
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
	'pre' => array(
		'tag' => 'pre',
		'type' => 'block',
		'content' => 'text',
		'replacement' => '&lt;pre&gt;&lt;!--TEXT--&gt;&lt;/pre&gt;',
		'param' => 'no',
		'allow_nesting' => false,
		'allowed_content' => array()
	),
</pre>
You can find explanations to each value at the top of the config/bbcode.php.<br />
<br />
Now we can already use the tag but we have no button for it yet.<br />
We will insert the button in the config/bbcode.php, too. This time further below in the file.
We want to insert the tag at the end of the first line. Therefore we put it behind this one:
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
...
		'size' => array(
			'type' => 'combo',
			'options' => array(
				9 => 'size_verysmall',
				10 => 'size_small',
				12 => 'size_middle',
				14 => 'size_big',
				18 => 'size_verybig'
			),
			'prompt_text' => 'prompt_size'
		),
...
</pre>
The definition of the button looks like the following:
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
		'pre' => array(
			'type' => 'button',
			'style' => '',
			'access_key' => '',
			'prompt_text' => ''
		),
</pre>
<br />
Finally we have to add an entry to the language-files for the hover-effect at the BBCode-buttons.
<b>Every tag requires the entry \"bbcode_help_&lt;tag_name&gt;\"</b>.<br />
Therefore we have to add the following to the file(s) language/&lt;language&gt;/lang_index.php:<br />
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
\$LANG['bbcode_help_pre'] = 'Preformated text: [pre]your text[/pre]';
</pre>
( Of course, depending on the language, with the corresponding value )<br />
<br />
Now you should add the tag at Adminarea -&gt; Settings -&gt; Formating to the areas in which you want to allow the tag.<br />
That was it :-)";
$LANG["faq_a_subforums"] = "This is also configurable in the <b>config/userdef.php</b>. The value which is responsible for this is the following:<br />
<div style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
define('BS_FORUM_SMALL_SUBDIR_DISPLAY',3);
</div>
Just change the number at the line end to any value you like.<br />
This configuration only takes effect if you have enabled the setting \"Show all sub-forums\" in \"General configuration\"!";
$LANG["faq_a_reduce_userdata"] = "Note that you can also remove some informations at <b>Additional profilefields</b>.<br />
If you want to remove more displayed userdata you can do this in the templates. Here is an example:<br />
Assume that we don't want to display when is user has registered.
<ul>
	<li>Please open the <b>template-editor</b> and choose the theme you wish to edit.</li>
	<li>Switch to the <b>templates</b>-directory and click on \"Edit\" at the file <b>posts.htm</b>.</li>
	<li>Approximately in the middle of the content you can see the following:<br />
	".'<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
.htmlspecialchars('
<table width="100%" cellpadding="1" cellspacing="1">
	{IF post:show_avatar}
	<tr>
		<td class="{post:left_table_class}" colspan="2">
		<img alt="" src="{post:avatar}" align="middle" />
		</td>
	</tr>
	{ENDIF}
	{IF post:view_ip}
	<tr>
		<td class="{post:left_table_class}" width="25%">IP:</td>
		<td class="{post:left_table_class}" width="75%">{post:user_ip}</td>
	</tr>
	{ENDIF}
	<tr>
		<td class="{post:left_table_class}" width="25%">{post:rank_title}:</td>
		<td class="{post:left_table_class}" width="75%">{post:rank_images}{post:user_status}</td>
	</tr>
	{IF post:show_reg_time}
	<tr>
		<td class="{post:left_table_class}" width="25%">{glocale.lang(\'registerdate\')}:</td>
		<td class="{post:left_table_class}" width="75%">{post:register_time}</td>
	</tr>
	{ENDIF}
	{LOOP post:add_fields as field}
	<tr>
		<td class="{post:left_table_class}" width="25%">{field:field_name}:</td>
		<td class="{post:left_table_class}" width="75%">{field:field_value}</td>
	</tr>
	{ENDLOOP}
	{IF post:user_id != 0}
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" class="{post:left_table_class}">{post:stats_ins_bottom}</td>
	</tr>
	{ELSE}
	<tr>
		<td class="{post:left_table_class}" width="25%">{glocale.lang(\'email\')}:</td>
		<td class="{post:left_table_class}" width="75%">{post:an_email_ins}</td>
	</tr>
	{ENDIF}
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>')
 ."</pre>
 </li>
 <li>To remove the date of registration you have to change this to the following:<br />"
 	.'<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
.htmlspecialchars('
<table width="100%" cellpadding="1" cellspacing="1">
	{IF post:show_avatar}
	<tr>
		<td class="{post:left_table_class}" colspan="2">
		<img alt="" src="{post:avatar}" align="middle" />
		</td>
	</tr>
	{ENDIF}
	{IF post:view_ip}
	<tr>
		<td class="{post:left_table_class}" width="25%">IP:</td>
		<td class="{post:left_table_class}" width="75%">{post:user_ip}</td>
	</tr>
	{ENDIF}
	<tr>
		<td class="{post:left_table_class}" width="25%">{post:rank_title}:</td>
		<td class="{post:left_table_class}" width="75%">{post:rank_images}{post:user_status}</td>
	</tr>
	{LOOP post:add_fields as field}
	<tr>
		<td class="{post:left_table_class}" width="25%">{field:field_name}:</td>
		<td class="{post:left_table_class}" width="75%">{field:field_value}</td>
	</tr>
	{ENDLOOP}
	{IF post:user_id != 0}
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2" class="{post:left_table_class}">{post:stats_ins_bottom}</td>
	</tr>
	{ELSE}
	<tr>
		<td class="{post:left_table_class}" width="25%">{glocale.lang(\'email\')}:</td>
		<td class="{post:left_table_class}" width="75%">{post:an_email_ins}</td>
	</tr>
	{ENDIF}
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>')
 ."</pre>
	</li>
	<li>Finally you have to save the changes.</li>
</ul>";
$LANG["faq_a_templates"] = "Templates contain the <b>(X)HTML-Code</b> of the board which means the <b>design of the board</b>. The templates are usefull because it gives me the opportunity to divide the PHP-Code from the HTML-Code. This makes not only the code more clearly but also gives the administrators of the board the opportunity to change the design without having any experience in PHP-programming.<br />
The templates can be changed here in the adminarea in the section <b>Template-editor</b>.<br />
<br />
The templates don't only contain the HTML-Code but also <b>variables</b> which will be replaced with values before the template will be printed out. These variables are marked with braces, e.g. <b>{variable_name}</b><br />
Additionally there are conditions and loops which look like the following: {IF ...}, {ELSE}, {ENDIF}, {LOOP ...}, {ENDLOOP}<br />
I will not go into further details here because it isn't really important, I think.";
$LANG['faq_a_logout'] = 'If you click logout and you are still logged in on the next page probably the
<b>cookie-path</b> or <b>cookie-domain</b> is not configured correctly.<br />
You can adjust these settings at <b>Settings -> General</b>. Both ones are explained there.<br />
After you have changed the settings please <b>delete the cookies of Boardsolution</b> (they start with
BS_COOKIE_PREFIX [see config/userdef.php] which is by default "bs_").<br />
Now the logout should work.';
$LANG['faq_a_emails_spam'] = 'If some user complain about not receiving the activation email the reason could
be that their email-provider has a buildin spam-filter (the user might not know about) which filters this
emails, so that they don\'t receive the email.<br />
If so you can also activate this user manually in the adminarea.';
$LANG['faq_a_bs_api'] = 'If you want to provide some information from the board on your homepage or somewhere else
you can use the <b>BS-API</b> if you like. This gives you a simple interface to the most important (or lets say, the most
probably used information) data from the board. For example you can print the currently online user or the
last few topics or the number of forums and so on.<br />
Please take a look at the example (<b>extern/example.php</b>) to understand how to use the API.<br />
If you want to know all available data in the API you can take a look at the modules (extern/modules). All data is stored in the fields
of the classes (the var $... statements at the beginning).';
$LANG['faq_a_bbceditor_extra_tags'] = 'You can configure the "extra-tags" of the Java-BBCodeEditor in
<b>bbceditor/extra_tags.xml</b>. There are already four existing tags: "post", "topic", "att" and "attimg".<br />
You can extend this file as you like. Note that it is also possible to add new buttons (with extra-tags).<br />
If you see something like "gui.a.b..." it is an entry in the language-files of the Java-BBCodeEditor (bbceditor/language/&lt;language&gt;.txt).<br />
<br />
Let us add an additional tag to the file. At first we copy one of the other items and edit it for our tag:
<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
.htmlspecialchars('
<item name="example">
	<buttonID>b1</buttonID>
	<comboName>gui.dialog.extra.example.comboname</comboName>
	<dialogTitle>gui.dialog.extra.example.title</dialogTitle>
	<parameterTitle>gui.dialog.extra.example.parameter.title</parameterTitle>
	<hasParameter>true</hasParameter>
	<description>gui.dialog.extra.example.desc</description>
</item>
')
.'</pre>
Note that the tag "hasParameter" specifies wether the BBCode-tag has a parameter or not. That means
if the tag looks like [tag=parameter]text[/tag] or like [tag]text[/tag].<br />
Now we have to add our language-entries specified above to the language-files (bbceditor/language/&lt;language&gt;.txt):
<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
.htmlspecialchars('
gui.dialog.extra.example.comboName = "Example tag"
gui.dialog.extra.example.title = "Example tag"
gui.dialog.extra.example.parameter.title = "ParameterName:"
gui.dialog.extra.example.desc = "Description of the example tag"
')
.'</pre>
That was it! Now we can use our just created tag in the editor.<br />
<b>But note that you may have to restart your browser if you have already used the editor in it
because most browsers cache it.</b>';
?>
