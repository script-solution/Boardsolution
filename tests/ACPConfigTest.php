<?php
class ACPConfigTest extends BaseTest
{
  function testConfig()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_0");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("forum_title", "Boardsolution2");
    $this->type("error_log_days", "11");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Einstellungen wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("Boardsolution2", $this->getValue("forum_title"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("11", $this->getValue("error_log_days"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("error_log_days", "7");
    $this->type("forum_title", "Boardsolution");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("//td[2]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//td[3]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//td[4]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//td[5]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//td[6]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[1]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[2]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[3]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[4]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[5]/a/b");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[4]/td[6]/a/b");
    $this->waitForPageToLoad("30000");
    $this->type("attachments_max_number", "1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Einstellungen wurden erfolgreich gespeichert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Standard wiederherstellen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("0", $this->getValue("attachments_max_number"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Einstellung wurde erfolgreich zurükgesetzt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("keyword_field", "datei");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Gefundene Einstellungen für \"datei\""));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurücksetzen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Allgemein"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>