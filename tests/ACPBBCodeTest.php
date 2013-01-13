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
class ACPBBCodeTest extends BaseTest
{
  function testBBCode()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_1");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("search", "list");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=BBCode-Tags");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->select("type", "label=Block");
    $this->select("type", "label=Inline");
    $this->type("replacement", "<b><TEXT></b>\nund noch viel mehr");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("<b><TEXT></b>\nund noch viel mehr", $this->getValue("replacement"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("replacement", "<b><TEXT></b>");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("<b><TEXT></b>", $this->getValue("replacement"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=BBCode-Tags");
    $this->waitForPageToLoad("30000");
    $this->click("link=BBCode-Tag hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("name", "meintag");
    $this->type("replacement", "<pre><TEXT></pre>");
    $this->type("allowed_content", "");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Tag wurde erfolgreich hinzugefügt"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=BBCode-Tags");
    $this->waitForPageToLoad("30000");
    $this->click("link=2");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(7);']");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Tags wurden erfolgreich gelöscht"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 16 - 22 , Gesamt: 22 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>