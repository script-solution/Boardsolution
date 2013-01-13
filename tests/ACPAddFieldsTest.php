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
class ACPAddFieldsTest extends BaseTest
{
	function testAddFields()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_17");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neues Feld hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("field_name", "mein feld");
    $this->type("display_name", "Mein Feld");
    $this->type("field_length", "50");
    $this->click("loc_2_1");
    $this->click("loc_4_1");
    $this->click("loc_1_1");
    $this->click("loc_8_1");
    $this->type("field_suffix", "hihi");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der Feldname ist ungültig. Bitte benutze nur die Zeichen: a-z A-Z 0-9 _"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("field_name", "meinfeld");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Das Feld wurde erfolgreich erstellt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zusätzliche Profilfelder");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_6']/td[4]/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_5']/td[4]/a[2]/img");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_6']/td[6]/a/img");
    $this->waitForPageToLoad("30000");
    $this->type("display_name", "Mein Feld2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("Mein Feld2", $this->getValue("display_name"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zusätzliche Profilfelder");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(6);']");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    $this->type("search", "wohn");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("wohnort 	Wohnort"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>