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

class UnreadTest extends BaseTest
{
  function testUnread()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->_test();
    $this->ensureAdmin();
    $this->_test();
  }
  
  private function _test()
  {
  	$this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("id_0");
    $this->select("topic_action", "label=Themen ungelesen markieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zum ersten ungelesenen Beitrag Mein Thema"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("id_0");
    $this->select("topic_action", "label=Themen ungelesen markieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zum ersten ungelesenen Beitrag Noch ein Termin in nem Forum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Ungelesene Themen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 2 , Gesamt: 2 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id_0");
    $this->click("//input[@value='Als gelesen markieren']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Dieses Forum als gelesen markieren']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Thema 	Mein Thema"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_1'); cb.checked = cb.checked ? false : true;\"]");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen ungelesen markieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zum ersten ungelesenen Beitrag Noch ein Termin in nem Forum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Zum ersten ungelesenen Beitrag Dürfen Gäste hier Umfragen starten?"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Forum gelesen markieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Umfrage 	Dürfen Gäste hier Umfragen starten?"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Termin 	Noch ein Termin in nem Forum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("id_0");
    $this->select("topic_action", "label=Themen ungelesen markieren");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("link=Alle Themen als gelesen markieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ungelesene Themen (0)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>