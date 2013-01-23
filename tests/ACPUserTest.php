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

class ACPUserTest extends BaseTest
{
  function testUser()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_15");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neuen User registrieren");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du hast verbotene Zeichen in Deinem Usernamen verwendet oder einen nicht erlaubten Usernamen eingegeben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_name", "bla");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Die Passwörter sind nicht identisch."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_pw", "123");
    $this->type("user_pw_conf", "123");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Diese Email-Adresse ist nicht erlaubt!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_pw", "123");
    $this->type("user_pw_conf", "123");
    $this->type("user_email", "1");
    $this->type("user_email", "1@web.de");
    $this->addSelection("other_groups__", "label=MeineGruppe");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der User wurde erfolgreich registriert!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("bla 	User\nMeineGruppe 	0 Beitr., 0 Pkt. 	Heute 	Nein"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(5);']");
    $this->select("action_type", "label=Sperren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("bla 	User\nMeineGruppe 	0 Beitr., 0 Pkt. 	Heute 	Ja"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("check_5");
    $this->select("action_type", "label=Entsperren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("bla 	User\nMeineGruppe 	0 Beitr., 0 Pkt. 	Heute 	Nein"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(5);']");
    $this->select("action_type", "label=Usergruppe(n) editieren");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->select("main_group_7_", "label=MeineGruppe");
    $this->addSelection("other_groups_7___", "label=User");
    $this->removeSelection("other_groups_7___", "label=MeineGruppe");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("bla\nMeineGruppe, User"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(5);']");
    $this->select("action_type", "label=Löschen");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 5 , Gesamt: 5 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Username");
    $this->waitForPageToLoad("30000");
    $this->click("link=Usergruppe");
    $this->waitForPageToLoad("30000");
    $this->click("link=Erfahrung");
    $this->waitForPageToLoad("30000");
    $this->click("link=Registriert seit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gesperrt");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neue Suche starten");
    $this->waitForPageToLoad("30000");
    $this->type("user_name_input", "zweitadmin");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zweitadmin 	User\nAdministratoren 	0 Beitr., 0 Pkt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Suche verändern");
    $this->waitForPageToLoad("30000");
    $this->removeSelection("user_group__", "label=User");
    $this->removeSelection("user_group__", "label=MeineGruppe");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Zweitadmin 	User\nAdministratoren 	0 Beitr., 0 Pk"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Suche zurücksetzen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 5 , Gesamt: 5 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>