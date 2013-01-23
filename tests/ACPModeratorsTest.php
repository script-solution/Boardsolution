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

class ACPModeratorsTest extends BaseTest
{
  function testModerators()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_13");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("user_0", "admin");
    $this->type("user_1", "zweitadmin");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("estforum\nadmin Entfernen\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen , Zweitadmin Entfernen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Entfernen']");
    $this->waitForPageToLoad("30000");
    $this->click("//a[2]/img");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Testforum\n-\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen\nUsername(n)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_name_input", "zweitadmin");
    $this->click("//input[@value='Los!']");
    $this->waitForPageToLoad("30000");
    $this->addSelection("forums[4][]", "label=Gast Forum");
    $this->addSelection("forums[4][]", "label=-- Forum ohne Erfahrung");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die moderierten Foren der ausgewählten User wurden gespeichert"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[3]/tbody/tr/td/div/table/tbody/tr[2]/td/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("//a[2]/img");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("stforum\n-\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen\nUsername(n):\nGast Forum\n-\nUser"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>