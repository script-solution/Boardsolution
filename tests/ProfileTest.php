<?php
/**
 * Unittest
 * 
 * @package			test
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class ProfileTest extends BaseTest
{
  function testPersInfo()
  {
  	$this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=infos");
  	$this->ensureAdmin();
    $this->click("link=Profil");
    $this->waitForPageToLoad("30000");
    $this->type("user_email", "abc");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte gib Deine Email-Adresse an."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_email", "");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte gib Deine Email-Adresse an."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_email", "ich@hier.de");
    $this->type("add_hp", "http://www.meinepage.de");
    $this->type("add_Hobbys", "keine");
    $this->type("add_irc", "hierundda");
    $this->type("add_icq", "123456789");
    $this->type("add_wohnort", "zu hause");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Dein Profil wurde erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
    		$this->assertEquals("http://www.meinepage.de", $this->getValue("add_hp"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
      	$this->assertEquals("http://www.keine.de", $this->getValue("add_Hobbys"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
      	$this->assertEquals("hierundda", $this->getValue("add_irc"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
      	$this->assertEquals("123456789", $this->getValue("add_icq"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
      	$this->assertEquals("zu hause", $this->getValue("add_wohnort"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testSignature()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=infos");
  	$this->ensureAdmin();
    $this->click("link=Signatur");
    $this->waitForPageToLoad("30000");
    $this->type("bbcode_area1", "Meine neue Signatur");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Signatur wurde erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Vorschau:\n    Meine neue Signatur"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("Meine neue Signatur", $this->getValue("bbcode_area1"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Signatur wurde erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("", $this->getValue("bbcode_area1"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Vorschau:"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testConfig()
  {
  	$settings = array(
  		"timezone" => "label=Samoa",
    	"lang" => "label=Deutsch (Sie-Version)",
  		"theme" => "label=Script-solution",
  		"email_display_mode" => "label=Adresse unkenntlich machen",
  		"allow_board_emails_0" => true,
    	"default_email_notification_1" => true,
  		"email_notification_type" => "label=Täglich",
  		"emails_include_post_1" => true,
    	"enable_pm_email_1" => true,
    	"ghost_mode_1" => true,
    	"startmodule" => "label=Foren",
  		"posts_order" => "label=Neueste Beiträge zuerst",
  		"default_font" => "label=Arial",
  		"attach_signature_0" => true,
    	"bbcode_mode" => "label=Java-Applet"
  	);
  	
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=config");
  	$this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Einstellungen");
  	$this->waitForPageToLoad("30000");
    foreach($settings as $name => $value)
    {
    	if($value === true)
    		$this->click($name);
    	else
    		$this->select($name,$value);
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Deine Einstellungen wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    foreach($settings as $name => $value)
    {
    	try
    	{
	    	if($value === true)
	    		$this->assertTrue($this->isChecked($name));
	    	else
	    	{
	    		list(,$label) = explode('=',$value);
	    		$this->assertEquals($label,$this->getSelectedLabel($name));
	    	}
    	}
    	catch (PHPUnit_Framework_AssertionFailedError $e) {
      	array_push($this->verificationErrors, $e->toString());
			}
    }

  	$settings = array(
  		"timezone" => "label=Berlin",
    	"lang" => "label=Standard",
  		"theme" => "label=Standard",
  		"email_display_mode" => "label=Komplett verstecken",
  		"allow_board_emails_1" => true,
    	"default_email_notification_0" => true,
  		"email_notification_type" => "label=Sofort",
  		"emails_include_post_0" => true,
    	"enable_pm_email_0" => true,
    	"ghost_mode_0" => true,
    	"startmodule" => "label=Portal",
  		"posts_order" => "label=Älteste Beiträge zuerst",
  		"default_font" => "label=- Keine -",
  		"attach_signature_1" => true,
    	"bbcode_mode" => "label=Fortgeschrittener Modus"
  	);
    foreach($settings as $name => $value)
    {
    	if($value === true)
    		$this->click($name);
    	else
    		$this->select($name,$value);
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Deine Einstellungen wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testAvatars()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=avatars");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    
    // TODO bad workaround :/
    $str = "/opt/lampp/htdocs/ipb_203/style_avatars/Batman.gif";
    $ext = FWS_FileUtils::get_extension($str);
		$name = '1_'.FWS_Date::get_formated_date('YmdHis').'.'.$ext;
    BS_DAO::get_avatars()->create($name,1);
    
    copy($str,"/opt/lampp/htdocs/scriptsolution/Boardsolution/uploads/".$name);
    
    $this->click("//div[1]/ul/li[5]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Verwenden");
    $this->waitForPageToLoad("30000");
    $this->click("link=Avatar entfernen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Kein Bild vorhanden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick=\"var cb = document.getElementById('avatar_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Avatare wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    
    unlink("/opt/lampp/htdocs/scriptsolution/Boardsolution/uploads/".$name);
  }

  function testUserPWChg()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=chpw");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//div[1]/ul/li[6]/a");
    $this->waitForPageToLoad("30000");
    $this->type("user_name", "admin2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Dein Passwort und / oder Username wurde erfolgreich geändert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Du hast noch 3 Usernamen-Änderung(en) übrig."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("admin2", $this->getValue("user_name"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("current_password", "admin");
    $this->type("new_password", "admin2");
    $this->type("new_password_conf", "admin2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Dein Passwort und / oder Username wurde erfolgreich geändert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->type("user_name", "admin");
    $this->type("current_password", "admin2");
    $this->type("new_password", "admin");
    $this->type("new_password_conf", "admin");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Dein Passwort und / oder Username wurde erfolgreich geändert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testFavForums()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=favforums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//div[1]/ul/li[7]/a");
    $this->waitForPageToLoad("30000");
    $this->click("fav_0");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die favorisierten Foren wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('fav_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('fav_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("fav_1");
    $this->click("fav_2");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die favorisierten Foren wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('fav_2'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('fav_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('fav_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die favorisierten Foren wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testForumSubscr()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=favforums");
   	$this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Foren");
    $this->waitForPageToLoad("30000");
    $this->click("link=Alle Foren abonnieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Du hast erfolgreich alle Foren abonniert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Testforum » Forum ohne Erfahrung"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Gast Forum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Testforum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Anzeige: 1 - 3 , Gesamt: 3"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick=\"var cb = document.getElementById('subscr_2'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('subscr_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die gewählten Abonnements wurden erfolgreich entfernt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testTopicSubscr()
  {
  	$this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=topics");
   	$this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("link=exact:Dürfen Gäste hier Umfragen starten?");
    $this->waitForPageToLoad("30000");
    $this->click("link=Abonnieren");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zu meinen Abonnements");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('subscr_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('subscr_0'); cb.checked = cb.checked ? false : true;\"]");
    try {
        $this->assertTrue($this->isTextPresent("Dürfen Gäste hier Umfragen starten?"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick=\"var cb = document.getElementById('subscr_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die gewählten Abonnements wurden erfolgreich entfernt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testPMOverview()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pmoverview");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//li[12]/a");
    $this->waitForPageToLoad("30000");
    $this->click("outbox_1");
    $this->click("//input[@value='Absenden' and @type='submit']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die PMs wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("PM Ausgang (2 von 2)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick=\"var cb = document.getElementById('inbox_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("operation", "label=PMs ungelesen markieren");
    $this->click("inbox_submit_btn");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ungelesene PM 1 Anhang ich schreib mir gern selbst :P"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("inbox_0");
    $this->select("operation", "label=PMs gelesen markieren");
    $this->click("inbox_submit_btn");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Gelesene PM 1 Anhang ich schreib mir gern selbst :P"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testPMInbox()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pminbox");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=PM Eingang");
    $this->waitForPageToLoad("30000");
  }

  function testPMOutbox()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pmoutbox");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=PM Ausgang");
    $this->waitForPageToLoad("30000");
  }

  function testBanlist()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pmbanlist");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//li[15]/a");
    $this->waitForPageToLoad("30000");
    $this->type("user_name_input", "test");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der von Dir angegebene User ist schon auf Deiner Bannliste"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_name_input", "Zweitadmin");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der von Dir angegebene User wurde erfolgreich auf Deine Banliste gesetzt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("entry_0");
    $this->click("entry_0");
    $this->click("entry_1");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die von Dir ausgewählten User wurden erfolgreich von Deiner Banliste entfernt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der von Dir angegebene User wurde nicht gefunden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_name_input", "ba");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der von Dir angegebene User wurde nicht gefunden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
  
  function testPMSearch()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pmsearch");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//li[16]/a");
    $this->waitForPageToLoad("30000");
    $this->type("keyword", "test");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Mit dem/den Suchbegriff(en) \"test\" wurden 2 PMs gefunden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//li[16]/a");
    $this->waitForPageToLoad("30000");
    $this->type("keyword", "ich schreib");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Mit dem/den Suchbegriff(en) \"schreib\" wurden 2 PMs gefunden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//li[16]/a");
    $this->waitForPageToLoad("30000");
    $this->type("user_input_field", "test");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Mit dem/den Suchbegriff(en) \"\" von/an \"test\" wurden 1 PMs gefunden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("inbox_0");
    $this->click("inbox_submit_btn");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die PMs wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }

  function testPMCompose()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=userprofile&sub=pmcompose");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//li[17]/a");
    $this->waitForPageToLoad("30000");
    $this->type("new_receiver", "zweitadmin");
    $this->click("add_receiver_id");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zweitadmin"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("remove_recv_0");
    $this->click("remove_recv_0");
    $this->waitForPageToLoad("30000");
    $this->type("new_receiver", "Zweitadmin");
    $this->click("add_receiver_id");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zweitadmin"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst der Private Message einen Titel geben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("pm_title", "mein titel");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "nagut, mein text");
    $this->click("//img[@alt=':-P']");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die PM wurde erfolgreich an \"Zweitadmin\" gesendet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zu meiner Inbox");
    $this->waitForPageToLoad("30000");
    $this->click("link=PM Ausgang");
    $this->waitForPageToLoad("30000");
    $this->click("link=mein titel");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("nagut, mein text :-P"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>