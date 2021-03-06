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

class ACPLinksTest extends BaseTest
{
  function testLinks()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_9");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->type("url", "http://www.web2.de");
    $this->type("new_category", "Richtig schlechte Webseiten");
    $this->click("//input[@value='Update']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("http://www.web2.de", $this->getValue("url"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->type("new_category", "Schlechte Webseiten");
    $this->type("url", "http://www.web.de");
    $this->click("//input[@value='Update']");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->selectFrame("relative=up");
    $this->selectFrame("navigation");
    $this->click("item_32");
    $this->waitForPageToLoad("30000");
    $this->click("link=Linkliste");
    $this->waitForPageToLoad("30000");
    $this->click("link=Link hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("link_url", "www.test.de");
    $this->type("bbcode_area1", "Mein Linktest");
    $this->click("//img[@alt=':-O']");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich");
    $this->waitForPageToLoad("30000");
    $this->selectFrame("navigation");
    $this->click("item_9");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("check_0");
    $this->select("action_type", "label=Deaktivieren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->select("action_type", "label=Deaktivieren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("http://www.test.de 	Gute Webseiten 	Heute, admin 	0 	Nein"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->select("action_type", "label=Aktivieren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("http://www.test.de 	Gute Webseiten 	Heute, admin 	0 	Ja"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("check_0");
    $this->select("action_type", "label=Löschen");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 2 , Gesamt: 2 ) 	n/a 	n/a"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>