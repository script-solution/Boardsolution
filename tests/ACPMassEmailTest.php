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

class ACPMassEmailTest extends BaseTest
{
  function testMassEmail()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_20");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte gib den Betreff an!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("subject", "betreff");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte gib einen Text ein!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "text");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte gib mindestens eine Gruppe oder einen User als Empfänger an"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->addSelection("recipient_groups", "label=MeineGruppe");
    $this->click("//input[@value='User hinzufügen']");
    $this->waitForPopUp("UserSuche", "30000");
    $this->selectWindow("name=UserSuche");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(1);']");
    $this->click("//input[@value='Auswählen']");
    $this->selectWindow("name=content");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Ja']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("3 von 3 Emails wurden verschickt"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->addSelection("recipient_groups", "label=MeineGruppe");
    $this->type("subject", "nochmal");
    $this->type("bbcode_area1", "abc");
    $this->click("method_BCC");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
  }
}
?>