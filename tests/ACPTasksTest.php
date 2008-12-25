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