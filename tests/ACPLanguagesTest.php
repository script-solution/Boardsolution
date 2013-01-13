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
class ACPLanguagesTest extends BaseTest
{
  function testLanguages()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_3");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neue Sprache hinzufügen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Es wurde erfolgreich eine Sprache hinzugefügt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("names[5]", "test");
    $this->type("folders[5]", "test");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sprachen wurden erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("test", $this->getValue("names[5]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(4);']");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sprachen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "deu");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("Deutsch (Sie-Version)", $this->getValue("names[1]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("Deutsch (Du-Version)", $this->getValue("names[3]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Sprachen");
    $this->waitForPageToLoad("30000");
  }
}
?>