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

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class CalendarTest extends BaseTest
{
  function testCalendar()
  {
  	$eventCount = BS_DAO::get_events()->get_count();
    $this->open("/scriptsolution/Boardsolution/index.php?action=calendar");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Kalender");
    $this->waitForPageToLoad("30000");
    $this->select("month", "label=Januar");
    $this->select("year", "label=2009");
    $this->click("//input[@value='Go!']");
    $this->waitForPageToLoad("30000");
    $this->click("link=«");
    $this->waitForPageToLoad("30000");
    $this->click("link=«");
    $this->waitForPageToLoad("30000");
    $this->select("year", "label=2008");
    $this->select("month", "label=Oktober");
    $this->click("//input[@value='Go!']");
    $this->waitForPageToLoad("30000");
    $this->click("//div[2]/div/div/table/tbody/tr[3]/td[1]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='+']");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst dem Termin einen Namen geben und den Ort angeben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("topic_name", "mein name");
    $this->type("location", "mein ort");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der Start des Termins sollte vor dem Ende sein ;)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("e_day", "label=07");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "test");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Termin wurde erfolgreich hinzugefügt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->select("year", "label=2008");
    $this->select("month", "label=Oktober");
    $this->click("//input[@value='Go!']");
    $this->waitForPageToLoad("30000");
    $this->click("//td[3]/table/tbody/tr[2]/td/div/div/a");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ort:\n    mein ort\n\nBeginn:\n    06.10.2008, 00:00\n\nEnde:\n    07.10.2008, 00:00 \n\nBeschreibung:\n    test"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Termin editieren");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "mein name2");
    $this->type("location", "mein ort2");
    $this->select("b_day", "label=07");
    $this->select("e_day", "label=08");
    $this->select("b_hour", "label=01");
    $this->select("e_hour", "label=02");
    $this->type("bbcode_area1", "test2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Termin wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ort:\n    mein ort2\n\nBeginn:\n    07.10.2008, 01:00\n\nEnde:\n    08.10.2008, 02:00 \n\nBeschreibung:\n    test2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Termin löschen");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Termin wurde erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    
    $this->assertEquals($eventCount,BS_DAO::get_events()->get_count());
  }
}
?>