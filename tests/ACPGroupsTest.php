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
class ACPGroupsTest extends BaseTest
{
  function testGroups()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_14");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neue Gruppe hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("group_title", "testgruppe");
    $this->type("group_color", "004400");
    $this->click("view_memberlist_1");
    $this->click("enter_board_1");
    $this->click("view_linklist_1");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Gruppe wurde erfolgreich erstellt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gruppen");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_4']/td[4]/a/img");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("004400", $this->getValue("group_color"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("group_color", "ff4400");
    $this->click("view_user_ip_1");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Gruppe wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gruppen");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(4);']");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Gruppen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "user");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("User 	Nein 	Ja"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>