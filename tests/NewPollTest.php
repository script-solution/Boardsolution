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

class NewPollTest extends BaseTest
{
  function testNewPoll()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neue Umfrage");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitten gib mindestens 2 Möglichkeiten an!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("polloptions", "erste\nzweite");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst dem Thema einen Namen bzw. eine Frage geben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("topic_name", "meine frage");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "so, jetzt aber");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Umfrage wurde erfolgreich gestartet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Umfrage: \"meine frage\" [ Seite 1 ]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("erste 	 \nzweite"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//tr[2]/td/table/tbody/tr/td[1]");
    $this->click("//input[@value='Zu den Ergebnissen']");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[2]/td/table/tbody/tr/td[4]");
    try {
        $this->assertTrue($this->isTextPresent("erste 	\n	0% 	\n	0 % 	0 Stimmen\nzweite 	\n	0% 	\n	0 % 	0 Stimmen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zur Abstimmung']");
    $this->waitForPageToLoad("30000");
    $this->click("vote_1_10");
    $this->click("//input[@name='submit' and @value='Abstimmen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Deine Stimme wurde gezählt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("zweite 	\n	100% 	\n	100 % 	1 Stimmen\nerste 	\n	0% 	\n	0 % 	0 Stimmen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//tr[2]/td/table/tbody/tr/td[3]");
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
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag meine frage"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=meine frage");
    $this->waitForPageToLoad("30000");
    $this->click("link=Thema löschen");
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