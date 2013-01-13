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

class NewEventTest extends BaseTest
{
  function testNewEvent()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neuer Termin");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst dem Thema einen Namen bzw. eine Frage geben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("topic_name", "ok, ein name");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "eingabe");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst dem Termin einen Namen geben und den Ort angeben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("location", "der ort");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der Start des Termins sollte vor dem Ende sein ;)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("b_day", "label=08");
    $this->select("b_hour", "label=15");
    $this->select("b_min", "label=00");
    $this->click("openend");
    $this->click("cas");
    $this->select("c_day", "label=06");
    $this->select("c_day", "label=10");
    $this->select("c_day", "label=06");
    $this->select("c_hour", "label=15");
    $this->select("c_min", "label=00");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Termin wurde erfolgreich gestartet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ort:\n    der ort\n\nBeginn:\n    08.12.2008, 15:00\n\nEnde:\n    Open End \n\nAnmeldungen:\n    Anmeldeschluss: 	06.12.2008, 15:00\n    Angemeldet: 	0 / ∞:"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Termin: \"ok, ein name\" [ Seite 1 ]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Neuling mit 7 Punkte, 3 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("2 	3"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag ok, ein name"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("id_0");
    $this->select("topic_action", "label=Themen löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=admin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Beiträge: 	2\nPunkte: 	4"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("1 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag Mein Thema"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>