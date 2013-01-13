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
class ACPAccessTest extends BaseTest
{
  function testACPAccess()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_19");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("user_name_input", "zweitadmin");
    $this->click("//input[@value='Zugriffsrechte einstellen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Dieser User existiert nicht, ist nicht freigeschaltet / gesperrt oder Administrator."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Adminbereich Zugriff");
    $this->waitForPageToLoad("30000");
    $this->type("user_name_input", "meinuser");
    $this->click("//input[@value='Zugriffsrechte einstellen']");
    $this->waitForPageToLoad("30000");
    $this->click("permission_config__1");
    $this->click("permission_bbcode__1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich Zugriff");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Einstellungen 	\nUser: 	meinuser\nGruppen: 	MeineGruppe\n	Editieren\nBBCode-Tags 	\nUser: 	meinuser\nGruppen: 	-"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("user_group", "label=MeineGruppe");
    $this->click("document.forms[1].elements[1]");
    $this->waitForPageToLoad("30000");
    $this->click("permission_bbcode__1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich Zugriff");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Einstellungen 	\nUser: 	meinuser\nGruppen: 	MeineGruppe\n	Editieren\nBBCode-Tags 	\nUser: 	meinuser\nGruppen: 	MeineGruppe"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->addSelection("user_intern", "label=meinuser");
    $this->click("//input[@value='Markierte entfernen']");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich Zugriff");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[3]/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Markierte entfernen']");
    $this->removeSelection("groups", "label=MeineGruppe");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich Zugriff");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Einstellungen 	\nUser: 	-\nGruppen: 	MeineGruppe\n	Editieren\nBBCode-Tags 	\nUser: 	-\nGruppen: 	-"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>