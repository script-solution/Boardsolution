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
class ACPForumsTest extends BaseTest
{
  function testForums()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_12");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neues Forum erstellen");
    $this->waitForPageToLoad("30000");
    $this->type("forum_name", "mein forum");
    $this->select("parent", "label=Gast Forum");
    $this->type("description", "meine beschreibung");
    $this->click("permission_thread_2__1");
    $this->click("permission_poll_2__1");
    $this->click("permission_event_2__1");
    $this->click("permission_post_2__1");
    $this->click("//div[@id='additional']/center[1]/input[1]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Das Forum wurde erfolgreich angelegt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_3']/td[4]/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("permission_thread_3__1");
    $this->click("permission_thread_2__0");
    $this->click("permission_poll_3__1");
    $this->click("permission_poll_2__0");
    $this->click("permission_event_3__1");
    $this->click("permission_event_2__0");
    $this->click("permission_post_3__1");
    $this->click("permission_post_2__0");
    $this->click("is_intern_1");
    $this->click("group_access_2__1");
    $this->click("//div[@id='additional']/center[2]/input[1]");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Enthält Themen mein forum\n	\nG 	G 	G 	G 	U"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Sortierung korrigieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sortierung wurde erfolgreich korrigiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(3);']");
    $this->select("//select[@name='action_type']", "label=Leeren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Foren wurden erfolgreich geleert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(3);']");
    $this->select("//select[@name='action_type']", "label=Löschen");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Foren wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>