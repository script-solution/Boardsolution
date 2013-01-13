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
class ACPThemesTest extends BaseTest
{
  function testThemes()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_11");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neues Theme hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("theme_name", "test");
    $this->type("theme_folder", "test");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Das Theme wurde erfolgreich erstellt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->type("names_6_", "test2");
    $this->type("folders_6_", "test2");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("test2", $this->getValue("names_6_"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("test2", $this->getValue("folders_6_"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//tr[@id='row_5']/td[3]/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("link=Themes");
    $this->waitForPageToLoad("30000");
    $this->click("check_5");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    $this->type("search", "script");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("Script-solution", $this->getValue("names_1_"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Themes");
    $this->waitForPageToLoad("30000");
  }
 	
  function testSimpleEditor()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_11");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->select("attribute_2_", "label=Schriftgewicht");
    $this->click("add[2]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Schriftgewicht:"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("attrval_2_font-weight", "label=300");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    $this->click("cb_2_font_weight");
    $this->click("delete");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
  }
  
  function testAdvancedEditor()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_11");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Fortgeschrittener Modus");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
  }
}
?>