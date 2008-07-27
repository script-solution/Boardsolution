<?php
$LANG['installationtitle'] = 'Installation of '.BS_VERSION;
$LANG['available'] = 'Available';
$LANG['notavailable'] = 'Not available';
$LANG['ok'] = 'OK';
$LANG['notok'] = 'Not OK';
$LANG['password'] = 'Password';
$LANG['database'] = 'Database';
$LANG['refresh'] = 'Refresh';
$LANG['next_message'] = 'Next message';
$LANG['previous_message'] = 'Previous message';
$LANG['edit_message'] = 'Edit';
$LANG['information'] = 'Information';
$LANG['position'] = 'Position';
$LANG['type'] = 'Type';
$LANG['error_occurred'] = 'The following values are missing or not correct';

$LANG['step_intro'] = 'Step 1: Important notices';
$LANG['step_type'] = 'Step 2: Installation-Type';
$LANG['step_config'] = 'Step 3: Settings';
$LANG['step_dbcheck'] = 'Step 4: Validation of the MySQL-tables';
$LANG['step_process'] = 'Step 5: Installation';
$LANG['step6'] = 'Step 6: Recalculation of the messages';
$LANG['step7'] = 'Step 7: Edit invalid messages';

$LANG['step1_explain'] = '<span style="font-weight: bold; color: #FF0000; font-size: 13px;">Please read the following notices
to prevent problems or questions!</span>';
$LANG['step2_explain'] = 'If you have already installed Boardsolution v1.2x, please choose "Update" to keep all data.<br />
If you have an older version like for example Boardsolution v1.1x you have to update step by step. That means you have
to download Boardsolution v1.22 first, install it and afterwards you can install this version.<br />
<br />
Otherwise please choose "New installation".';

$LANG['type_entry'] = 'Entry';
$LANG['type_comment'] = 'Comment';
$LANG['error_text'] = 'Error';
$LANG['edit_messages_success'] = 'The message has been edited successfully.';
$LANG['step7_success'] = 'All messages have been edited!';

$LANG['step1_message_changes_title'] = 'Message';
$LANG['step1_message_changes_text'] = 'The <b>store- and display-concept</b> of the messages (entries and comments) has
been changed since <b>Boardsolution v1.10</b>.<br />
In the previous versions the board has just saved the text written by the user in the database and it was required
to convert the text each time you wanted to display it. This was very slowly and therefore it has been changed now.<br />
In this version the board saves additionally to the text written by the user the converted text in the database.
That means that directy after you submit the message the text will be converted and the result will be stored
in the database.<br />
This <b>improves the performance of the message-display</b> noticable.<br />
<br />
But the <b>disadvantage</b> is that it is <b>no longer possible to react dynamicly to changes</b> of the smileys, badwords or other
settings which affect the messages.<br />
For this reason you have the opportunity to recalculate all messages in the adminarea at Maintenance -> Miscellaneous -> Refresh messages.';

$LANG['step1_database_changes_title'] = 'Changes to the database';
$LANG['step1_database_changes_text'] = 'Boardsolution saves the content of some MySQL-tables additionally in
an extra table to read the contents faster. This is done with the tables which will probably not have so much content and
will not be changed very often.<br />
<b>Therefore you should not change anything in the database manually</b> if you don\'t exactly know what you\'re doing!<br />
<br />
But the so called "DB-Cache" can be recalculated in the adminarea.<br />
That means if you have changed something in the database you should (depending on which table you have changed) recalculate the cache
of the table you have modified at Maintenance -> DB-Cache.<br />
<br />
Additionally some of the MySQL-tables are connected / related to each other. For example the entry-table stores
the number of comments.<br />
<b>This is another reason why I want to advise you not to change anything manually in the database!</b>';

$LANG['step1_further_settings_title'] = 'Further settings';
$LANG['step1_further_settings_text'] = 'Beside the settings in the adminarea you can find additional ones in the files
<b>config/userdef.php</b>, <b>config/actions.php</b> and <b>config/bbcode.php</b>. These are <b>detail-settings</b> which should not be very important for the "normal" usage
and are <b>primary intended for more experienced user</b>.<br />
<br />
For example you can <b>configure the BBCode</b>. In Boardsolution you are able to
adjust and extend the BBCode as you like. How this works in detail is explained (hopefully sufficient) in the install/user_config.php.<br />
<br />
Beside these there are <b>many other settings</b> you can edit in these files. But I don\'t want to explain them here in detail
because this would be too much.';

$LANG['writable'] = 'Writable';
$LANG['notwritable'] = 'Not writable';

$LANG['error']['phpversion'] = 'Your Server has to support at least PHP-version 4.1.0';
$LANG['error']['mysql'] = 'Your Server has to support at least MySQL 3.x';
$LANG['error']['chmod_cache'] = 'Please change the attributes of "cache" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_config'] = 'Please change the attributes of "config" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_config_community'] = 'Please change the attributes of "config/community.php" so that it is writable (e.g. 0666).';
$LANG['error']['chmod_config_userdef'] = 'Please change the attributes of "config/userdef.php" so that it is writable (e.g. 0666).';
$LANG['error']['chmod_themes'] = 'Please change the attributes of "themes/&lt;theme&gt;/style.css" and all files in "themes/default/templates" so that they are writable (e.g. 0666).';
$LANG['error']['chmod_themes_codes'] = array(
	1 => 'The attributes of "themes/default/style.css" are not correct',
	2 => 'The attributes of one file in "themes/default/templates" are not correct',
	3 => 'The directory "themes/default/templates" is not readable'
);
$LANG['error']['chmod_smileys'] = 'Please change the attributes of "images/smileys" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_avatars'] = 'Please change the attributes of "images/avatars" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_uploads'] = 'Please change the attributes of "uploads" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_dbbackup'] = 'Please change the attributes of "dbbackup/backups" so that it is writable (e.g. 0777).';
$LANG['error']['chmod_dbaaccess'] = 'Please change the attributes of "dbbackup" so that it is writable (e.g. 0777).';
$LANG['error']['mysql_connect'] = 'Please verify the configuration of "Host", "Login" and "Password"';
$LANG['error']['mysql_select_db'] = 'Please verify the name of the database';
$LANG['error']['admin_login'] = 'Please enter the username of the administrator.';
$LANG['error']['admin_pw'] = 'Please enter the password of the administrator.';
$LANG['error']['admin_email'] = 'Please enter the email-address of the administrator.';
$LANG['error']['board_url'] = 'Please enter the board-path.';

$LANG['gd_description'] = 'The GD-Library is not required';

$LANG['voraussetzungenerfuellt'] = 'All conditions for the installation were successfully checked.';
$LANG['noterfuellt'] = 'Not all conditions for the installation were successfully checked';

$LANG['back'] = 'Back';
$LANG['forward'] = 'Forward';
$LANG['finish'] = 'Install';

$LANG['yes'] = 'Yes';
$LANG['no'] = 'No';
$LANG['admin_login'] = 'Admin - Login';
$LANG['admin_pw'] = 'Admin - Password';
$LANG['admin_email'] = 'Admin - Email';
$LANG['board_url'] = 'Board - URL';
$LANG['board_url_desc'] = 'The absolute URL to your board. That means that if your board for example is located here: "http://www.domain.com/board/index.php", the URL would be: "http://www.domain.com/board"<br />
It\'s very important that you don\'t enter the last "/".';
$LANG['kindofinstall'] = 'Installation';
$LANG['fullinstall'] = 'New installation';
$LANG['update'] = 'Update';
$LANG['table_praefix'] = 'Table-prefix';
$LANG['btn_update'] = 'Update';

$LANG['important_tasks'] = '<b>IMPORTANT:</b> Please refresh the messages at first! You can do that at Adminarea -> Maintenance -> Miscellaneous. But before you do that make sure that all settings (Especially Settings -&gt; Formating) are ok. After that please take a look at Adminarea -&gt; Maintenance -&gt; Correct messages.';

$LANG['table_exists_error'] = 'If you want to make a new installation it is required that no MySQL-table of Boardsolution already exists in the database.<br />If you want to install another version of the board or have other reasons to use this database you can specify the table-prefix at the top of this page.';
$LANG['toboard'] = 'Go to Boardsolution';
$LANG['installation_complete'] = 'The installation was finished successfully. Please delete now the file "install.php"';
$LANG['writing_install_config_failed'] = 'Writing the file "install/config.php" failed. Please verify that the CHMOD of the file is 0666.';
$LANG['writing_install_community_failed'] = 'Writing the file "install/community.php" failed. Please verify that the CHMOD of the file is 0666.';
$LANG['writing_install_mysql_config_failed'] = 'Writing the file "install/mysql_config.php" failed. Please verify that the CHMOD of the folder "install" is 0777.';
?>