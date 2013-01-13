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
class ACPRanksTest extends BaseTest
{
  function testRanks()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_18");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Neue Gruppe hinzufügen']");
    $this->waitForPageToLoad("30000");
    $this->type("post_to[8]", "1500");
    $this->type("rank_name[8]", "test");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("1500", $this->getValue("post_to[8]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("test", $this->getValue("rank_name[8]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(8);']");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("3000", $this->getValue("post_to[6]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>