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
class ACPTasksTest extends BaseTest
{
  function testTasks()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_25");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Task hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte geben Sie den Titel des Tasks an!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("task_title", "titel");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte geben Sie den Dateinamen des Tasks an. Die Datei muss existieren!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("task_file", "titel.php");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Bitte geben Sie den Dateinamen des Tasks an. Die Datei muss existieren!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_6']/td[4]/a");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Task wurde erfolgreich ausgeführt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//tr[@id='row_6']/td[5]/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("enabled_0");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("enabled_1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
  }
}
?>