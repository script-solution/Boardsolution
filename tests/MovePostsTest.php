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

class MovePostsTest extends BaseTest
{
  function testMovePosts()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "move posts");
    $this->type("bbcode_area1", "asd");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 1");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 2");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 3");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Beiträge löschen / verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("td_1");
    $this->click("td_2");
    $this->click("td_3");
    $this->click("td_3");
    $this->click("link_split");
    $this->type("new_topic_name", "mein neues thema");
    $this->select("target_forum", "label=Gast Forum");
    $this->select("target_forum", "label=-- Forum ohne Erfahrung");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Beiträge wurden erfolgreich abgespalten."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Beiträge löschen / verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("link_merge");
    $this->type("topic_id", "3");
    $this->click("td_1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Beiträge wurden erfolgreich verschoben."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Testforum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("link=Noch ein Termin in nem Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Beiträge löschen / verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("td_1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//table[3]/tbody/tr/td[3]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("1 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("0 	0 	-"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("2 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>