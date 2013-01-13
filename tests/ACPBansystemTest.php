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
class ACPBansystemTest extends BaseTest
{
	function testMyTestCase()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_7");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neuen Eintrag hinzufügen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Ein neuer Eintrag wurde hinzugefügt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("values_1_", "1.2.3.4");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->select("types_1_", "label=Username");
    $this->type("values_1_", "test");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("test", $this->getValue("values_1_"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "test");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("test", $this->getValue("values_1_"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "test2");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Es sind keine Einträge vorhanden."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Bann System");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Einträge wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>