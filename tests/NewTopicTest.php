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

class NewTopicTest extends BaseTest
{
  function testNewTopic()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst das Feld 'Themenname' und das Textfeld ausfüllen!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("topic_name", "abc");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "na gut");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Das Thema wurde erfolgreich gestartet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("na gut"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Thema: \"abc\" [ Seite 1 ]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Testforum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "noch ein thema");
    $this->click("t3");
    $this->click("allow_posts_0");
    $this->click("subscribe_topic_1");
    $this->click("important_1");
    $this->type("bbcode_area1", "ein wichtiges thema");
    $this->click("tag_b_1");
    $this->click("//img[@alt='=)']");
    $this->click("preview");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Vorschau\nein wichtiges thema =)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("ein wichtiges thema =)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Thema: \"noch ein thema\" [ Seite 1 ]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Testforum");
    $this->waitForPageToLoad("30000");
    $this->click("id_1");
    $this->select("topic_action", "label=Thema editieren");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "abc2");
    $this->click("important_1");
    $this->click("allow_posts_0");
    $this->click("t1");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Das Thema wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Thema 	Wichtig: abc2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=admin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Beiträge: 	4\nPunkte: 	10"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("3 	4"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag noch ein thema"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=admin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Beiträge: 	2\nPunkte: 	4"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("1 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag Mein Thema"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->click("link=Forum ohne Erfahrung");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "thema ohne erfahrung");
    $this->click("t8");
    $this->type("bbcode_area1", "test");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Das Thema wurde erfolgreich gestartet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Thema: \"thema ohne erfahrung\" [ Seite 1 ]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Neuling mit 4 Punkte, 3 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Thema löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=admin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Beiträge: 	2\nPunkte: 	4"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>