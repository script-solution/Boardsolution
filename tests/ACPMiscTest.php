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
class ACPMiscTest extends BaseTest
{
  function testMisc()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_23");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("refresh[forums]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Aktion wurde erfolgreich durchgeführt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("refresh[topics]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Aktion wurde erfolgreich durchgeführt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("refresh[messages]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Aktion wurde erfolgreich durchgeführt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("refresh[userexp]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Aktion wurde erfolgreich durchgeführt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
  }
}
?>