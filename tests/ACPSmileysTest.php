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
class ACPSmileysTest extends BaseTest
{
  function testSmileys()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_2");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->type("secondary_code", ":)");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der Smiley-Code \":)\" existiert bereits."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("secondary_code", "(-8");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Smiley wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("secondary_code", "");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("", $this->getValue("secondary_code"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Smileys");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Dieses Forum um eine Stelle nach oben verschieben']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent(":-) 	:-) 	:) 	Ja 	( 1 ) n/a Dieses Forum um eine Stelle nach unten verschieben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Dieses Forum um eine Stelle nach unten verschieben']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("=) 	=) 		Ja 	( 1 ) n/a Dieses Forum um eine Stelle nach unten verschieben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "roll");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Smileys");
    $this->waitForPageToLoad("30000");
    $this->click("link=Sortierung korrigieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sortierung wurde erfolgreich korrigiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>