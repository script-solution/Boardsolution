<?php
$LANG["faq_q_board_logo"] = "Wo kann ich das Board-Logo &auml;ndern?";
$LANG["faq_q_moderators"] = "Was sind Moderatoren?";
$LANG["faq_q_gzip"] = "Was ist und was bringt GZip?";
$LANG["faq_q_link_to_hp"] = "Wie kann ich in die Board-Navigation einen Link zu meiner Homepage einf&uuml;gen?";
$LANG["faq_q_status_messages"] = "Wie kann ich die \"Aktion erfolgreich...\"-Seiten abschalten?";
$LANG["faq_q_add_bbcode"] = "Wie kann ich weitere BBCode-Tags hinzuf&uuml;gen?";
$LANG["faq_q_subforums"] = "Wie kann ich einstellen wieviele Unterforen-Links angezeigt werden?";
$LANG["faq_q_reduce_userdata"] = "Wie kann ich User-Daten, die bei den Beitr&auml;gen angezeigt werden, entfernen?";
$LANG["faq_q_templates"] = "Was sind templates?";
$LANG['faq_q_logout'] = "Ich kann mich nicht ausloggen. Wie kann ich das beheben?";
$LANG['faq_q_emails_spam'] = "Wieso bekommen einige User die Aktivierungs-Email nicht?";
$LANG['faq_q_bs_api'] = "Wie kann ich Informationen aus dem Board auf meiner Homepage anzeigen?";
$LANG['faq_q_bbceditor_extra_tags'] = 'Wie kann ich zus&auml;tzliche Tags im Java-BBCodeEditor hinzuf&uuml;gen?';

$LANG["faq_a_board_logo"] = "Das Board-Logo ist mittels CSS eingestellt. Sie k&ouml;nnen dies in der Designanpassung &auml;ndern. Also zun&auml;chst bei <b>Theme-Verwaltung</b> -> <b>Themes</b> bei dem gew&uuml;nschten Theme auf \"Editieren\" klicken. Dann links bei <b>Gruppen</b> die Gruppe <b>Haupt-Klassen</b> -> <b>Board-Logo</b> ausw&auml;hlen und dort den Pfad des Bildes &auml;ndern oder was immer Sie m&ouml;chten.";
$LANG["faq_a_moderators"] = "Moderatoren sind daf&uuml;r da um dem/den Administrator(en) ein wenig Arbeit abzunehmen. Sie k&ouml;nnen f&uuml;r jedes Forum, welches Themen enth&auml;lt, ein oder mehrere Moderatoren unter <b>Forum-Verwaltung</b> -> <b>Moderatoren/b> festlegen.<br />
Diese k&ouml;nnen das Forum kontrollieren, indem sie beispielsweise falsch einsortierte Themen in das richtige Forum verschieben, undeutliche Themen-titel ver&auml;ndern oder (ausversehen) mehrfach erstellte Themen/Beitr&auml;ge l&ouml;schen usw.<br />
Die M&ouml;glichkeiten, die die Moderatoren haben, k&ouml;nnen Sie bei <b>Allgemein</b> -> <b>Einstellungen</b> -> <b>Moderatoren</b> einstellen.<br />
Moderatoren sind keine eigene Usergruppe, sondern nur ein zus&auml;tzlicher \"Status\". D.h. f&uuml;r einen User, welcher in einem Forum Moderator ist, gelten im gesamten Board die Rechte der Usergruppe \"User\". Jedoch hat er/sie in dem einen Forum, welches er/sie moderiert, besondere Rechte, n&auml;mlich die Rechte der Moderatoren.";
$LANG["faq_a_gzip"] = "<b>GZip ist eine Komprimierung</b>. Diese kann im Board verwendet werden um den HTML-Code, welchen das Board erstellt, am Ende zu komprimieren. Dadurch wird der HTML-Code sehr viel kleiner und der Nutzer muss weniger herunterladen.<br />
Logischerweise ist dies <b>besonders bei langsamen Internetverbindungen</b> ein <b>gro&szlig;er Geschwindigkeitsgewinn</b>.<br />
Es unterst&uuml;tzen allerdings nicht alle Server GZip. Daf&uuml;r muss das PHP-Modul Zlib installiert sein.<br />
Sie k&ouml;nnen aber GZip im Adminbereich einfach kurz aktivieren um zu testen ob es funktioniert. Sofern das Board \"normal\" geladen wird und keine kryptischen Zeichen zu sehen sind, l&auml;uft alles wie es soll.<br />
Sofern Ihr Server es unterst&uuml;tzt ist es empfehlenswert es zu aktivieren.<br />
<br />
Zus&auml;tzlich gibt es noch die M&ouml;glichkeit <b>GZip f&uuml;r den Adminbereich</b> zu aktivieren.<br />
Dies kann (da es nicht alle Server unterst&uuml;tzen) in der config/userdef.php gemacht werden.<br />
Und zwar muss daf&uuml;r der Wert <b>BS_ENABLE_ADMIN_GZIP</b> auf <b>true</b> gesetzt werden. (Siehe Zeile 135)";
$LANG["faq_a_link_to_hp"] = "Dies k&ouml;nnen Sie in den Templates machen.<br />
Hier ein Beispiel um einen Link zur Homepage http://www.domain.de mit dem Titel \"Homepage\" einzuf&uuml;gen:
<ul>
	<li>Zuerst den <b>Template-Editor</b> &ouml;ffnen und das gew&uuml;nschte Theme ausw&auml;hlen.</li>
	<li>Dort in das <b>templates</b>-Verzeichnis wechseln und bei der Datei <b>headline.htm</b> auf \"Editieren\" klicken.</li>
	<li>Ca. bei Zeile 25 der Datei sehen Sie folgendes:<br />"
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
	<li>Dort k&ouml;nnen Sie einen weiteren Men&uuml;-Punkt in folgender Art und Weise einf&uuml;gen:<br />"
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
	<li>Wobei die ID des Links (hier \"link20\") eindeutig sein muss. D.h. wenn Sie mehrere Links hinzuf&uuml;gen m&ouml;chten, m&uuml;ssten Sie immer einen anderen, bisher nicht verwendeten Wert nehmen.<br />
Dann nur noch abspeichern und danach sollte der soeben erstellte Men&uuml;punkt sichtbar sein.</li>
</ul>";
$LANG["faq_a_status_messages"] = "Sie k&ouml;nnen in der <b>config/actions.php</b> festlegen nach welcher Aktion eine sogenannte \"Status-Seite\" angezeigt werden soll.<br />
Um z.B. die Status-Seite nach dem Editieren eines Beitrags zu deaktivieren, muss Zeile 245 der config/actions.php:<br />
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
BS_ACTION_EDIT_POST => true,
</pre>
in:
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
BS_ACTION_EDIT_POST => false,
</pre>
ge&auml;ndert werden.";
$LANG["faq_a_add_bbcode"] = "Die <b>BBCode-Tags k&ouml;nnen alle zentral</b> in der <b>config/bbcode.php bearbeitet und erweitert</b> werden.<br />
Hier ein Beispiel um einen \"&lt;pre&gt;-Tag\" hinzuzuf&uuml;gen, also einen Tag um vorformatierten Text einzuf&uuml;gen, d.h. einen Text, der genauso angezeigt wird, wie angegeben:<br />
&Ouml;ffnen Sie bitte zun&auml;chst die config/bbcode.php. Bei Zeile 300 finden Sie folgendes:
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
Unter diesen Abschnitt werden wir den neuen Tag einf&uuml;gen.<br />
Die <b>Definition f&uuml;r den &lt;pre&gt;-Tag</b> sieht folgenderma&szlig;en aus:<br />
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
Erkl&auml;rungen zu den einzelnen Werten stehen am Anfang der config/bbcode.php.<br />
<br />
Nun k&ouml;nnen wir den Tag bereits benutzen, allerdings fehlt noch ein Button um ihn bequemer verwenden zu k&ouml;nnen.<br />
Diesen f&uuml;gen wir ebenfalls in die config/bbcode.php ein. Diesmal weiter unten in der Datei.
Wir wollen den Button an das Ende der ersten Zeile einf&uuml;gen. Daher bauen wir ihn nach folgendem ein:
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
Die Definition des Buttons sieht folgenderma&szlig;en aus:
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
		'pre' => array(
			'type' => 'button',
			'style' => '',
			'access_key' => '',
			'prompt_text' => ''
		),
</pre>
<br />
Jetzt muss nur noch ein Eintrag in die Sprach-Dateien f&uuml;r den Hover-Effekt bei den BBCode-Buttons gemacht werden.
Und zwar muss <b>f&uuml;r jeden Tag ein der Eintrag \"bbcode_help_&lt;tag_name&gt;\"</b> vorhanden sein.<br />
In diesem Fall muss also in die Datei(en) language/&lt;sprache&gt;/lang_index.php folgendes eingef&uuml;gt werden:<br />
<pre style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
\$LANG['bbcode_help_pre'] = 'Vorformatierter Text: [pre]Ihr Text[/pre]';
</pre>
( Nat&uuml;rlich mit, je nach Sprache, variierendem Wert )<br />
<br />
Jetzt sollten Sie den hingef&uuml;gten Tag noch bei Adminbereich -&gt; Einstellungen -&gt; Formatierung f&uuml;r die gew&uuml;nschten Bereiche freischalten.<br />
Das wars :-)";
$LANG["faq_a_subforums"] = "Auch das k&ouml;nnen Sie in der <b>config/userdef.php<b> einstellen. Der Wert, der daf&uuml;r verantwortlich ist, ist folgender:<br />
<div style=\"margin-top: 10px; margin-bottom: 5px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;\">
define('BS_FORUM_SMALL_SUBDIR_DISPLAY',3);
</div>
Hier einfach die Zahl am Ende so &auml;ndern wie Sie es gerne h&auml;tten.<br />
Diese Einstellung hat nur einen Effekt sofern in den Einstellungen \"Alle Unterforen anzeigen\" auf \"Nein\" gestellt ist!";
$LANG["faq_a_reduce_userdata"] = "Sie k&ouml;nnen einmal bei <b>Zus&auml;tzliche Profilfelder</b> einige Angaben entfernen.<br />
Falls Sie noch mehr Angaben entfernen m&ouml;chten, k&ouml;nnen Sie dies in den Templates machen. Damit Sie nicht lange suchen m&uuml;ssen, hier ein Beispiel:<br />
Nehmen wir an, dass nicht angezeigt werden soll, wann der User sich registriert hat.
<ul>
	<li>Zuerst den <b>Template-Editor</b> &ouml;ffnen und das gew&uuml;nschte Theme ausw&auml;hlen.</li>
	<li>Dort in das <b>templates</b>-Verzeichnis wechseln und bei der Datei <b>posts.htm</b> auf \"Editieren\" klicken.</li>
	<li>ca. in der Mitte der Datei sehen Sie die User-Daten, die angezeigt werden:<br />"
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
 <li>Um das Datum der Registrierung zu entfernen muss der Teil folgenderma&szlig;en ge&auml;ndert werden:<br />"
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
	<li>Jetzt nur noch abspeichern und dann ist die &Auml;nderung fertig.</li>
</ul>";
$LANG["faq_a_templates"] = "Templates enthalten den <b>(X)HTML-Code</b> der ausgegeben wird, d.h. das <b>Design des Boards</b>. Der Sinn der Templates ist den PHP-Code vom HTML-Code zu trennen, damit es zum einen &uuml;bersichtlicher ist und zum anderen die Nutzer oder besser gesagt die Administratoren der Boards das Design anpassen k&ouml;nnen ohne PHP-Kenntnisse zu besitzen.<br />
Die Templates k&ouml;nnen hier im Adminbereich in der Sektion <b>Template-Editor</b> bearbeitet werden.<br />
<br />
Die Templates enthalten nicht nur HTML-Code sondern auch <b>Variablen</b>, die bei der Ausgabe mit einem Wert ersetzt werden. Diese Variablen sind mit geschweiften Klammern gekennzeichnet, z.B. <b>{variablen_name}</b><br />
Zus&auml;tzlich gibt es noch Bedingungen und Schleifen, die folgenderma&szlig;en aussehen: {IF ...}, {ELSE}, {ENDIF}, {LOOP ...}, {ENDLOOP}<br />
Auf diese werde ich hier aber nicht n&auml;her eingehen, da dies zu weit f&uuml;hren w&uuml;rde.";
$LANG['faq_a_logout'] = 'Wenn Sie auf Logout klicken und auf der n&auml;chsten Seite immer noch eingeloggt
sind, ist wahrscheinlich der <b>Cookie-Pfad</b> oder die <b>Cookie-Domain</b> falsch eingestellt.<br />
Sie k&ouml;nnen dies bei <b>Einstellungen -> Allgemein</b> &auml;ndern. Beide Werte sind dort erkl&auml;rt.<br />
Nachdem Sie dies ge&auml;ndert haben, <b>l&ouml;schen</b> Sie bitte einmal die <b>Cookies von Boardsolution</b> (Sie fangen mit 
BS_COOKIE_PREFIX [siehe config/userdef.php] an, was standardm&auml;&szlig;ig "bs_" ist).<br />
Danach sollte das Ausloggen funktionieren.';
$LANG['faq_a_emails_spam'] = 'Falls einige User sich beschweren, dass sie die Aktivierungs-Email nicht erhalten, 
k&ouml;nnte der Grund daf&uuml;r sein, dass deren Email-Provider einen eingebauten Spam-Filter hat (der User
wei&szlig; m&ouml;glicherweise nichts davon), welcher die Emails rausfiltert und der User die Email daher nicht
erh&auml;lt.<br />
Wenn das so ist, k&ouml;nnen Sie den User auch manuell im Adminbereich aktivieren.';
$LANG['faq_a_bs_api'] = 'Wenn Sie ein paar Informationen aus dem Board auf Ihrer Homepage oder irgendwo anders anzeigen
m&ouml;chten, k&ouml;nnen Sie daf&uuml;r die <b>BS-API</b> benutzen, wenn Sie m&ouml;chten. Dies gibt Ihnen ein einfaches Interface
zu den wichtigsten (oder besser gesagt, den wom&ouml;glich am ehesten benutzten) Informationen aus dem Board.
Zum Beispiel kann man damit die User, die gerade im Board unterwegs sind, die letzten Themen oder die Anzahl
der Foren bekommen.<br />
Bitte werfen Sie einen Blick auf das Beispiel (<b>extern/example.php</b>) um zu verstehen wie die API genutzt werden kann.<br />
Wenn Sie alle verf&uuml;gbaren Daten in der API wissen m&ouml;chten, dann schauen Sie bitte in die Module (extern/modules). Alle Daten stehen
dort in den Feldern der Klassen (die var $... Statements am Anfang).';
$LANG['faq_a_bbceditor_extra_tags'] = 'Sie k&ouml;nnen die "Extra-Tags" des Java-BBCodeEditors in
<b>bbceditor/extra_tags.xml</b> konfigurieren. Dort sind bereits 4 bestehende Tags: "post", "topic", "att" und "attimg".<br />
Sie k&ouml;nnen diese Datei beliebig erweitern. Beachten Sie, dass es auch m&ouml;glich ist neue Buttons hinzuzuf&uuml;gen (mit Extra-Tags).<br />
Wenn Sie etwas wie "gui.a.b..." sehen, ist dies ein Eintrag in den Sprach-Dateien des Java-BBCodeEditors (bbceditor/language/&lt;sprache&gt;.txt).<br />
<br />
Lassen Sie uns nun einen zus&auml;tzlichen Tag in der Datei hinzuf&uuml;gen. Zuerst kopieren wir eines der existierenden Items und editieren es f&uuml;r unseren Tag:
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
Beachten Sie, dass der Tag "hasParameter" angibt ob der BBCode-Tag einen Parameter hat oder nicht. Also ob
der Tag aussieht wie [tag=parameter]text[/tag] oder [tag]text[/tag].<br />
Jetzt m&uuml;ssen wir nur noch die Sprach-Eintr&auml;ge, die wir oben angegeben haben, in die Sprach-Dateien einf&uuml;gen (bbceditor/language/&lt;sprache&gt;.txt):
<pre style="margin-top: 10px; padding: 4px; border: 1px solid #999999; background-color: #FFFFFF; font-family: Courier New, Monospace; font-size: 12px;">'
.htmlspecialchars('
gui.dialog.extra.example.comboName = "Beispiel Tag"
gui.dialog.extra.example.title = "Beispiel Tag"
gui.dialog.extra.example.parameter.title = "ParameterName:"
gui.dialog.extra.example.desc = "Beschreibung des Beispiel-Tags"
')
.'</pre>
Das wars! Nun k&ouml;nnen wir den soeben erstellten Tag im Editor verwenden.<br />
<b>Aber beachten Sie, dass Sie evtl. den Browser neustarten m&uuml;ssen, falls Sie bereits den Editor im Browser benutzt haben, denn viele Browser speichern Java-Applets zwischen.</b>';
?>
