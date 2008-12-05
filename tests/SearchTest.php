<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class SearchTest extends BaseTest
{
  function testSearch()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("link=Suchen");
    $this->waitForPageToLoad("30000");
    $this->type("keyword", "haha");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ergebnis für die Keyword(s) \"haha\": 1 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//div[2]/a[2]");
    $this->waitForPageToLoad("30000");
    $this->type("keyword", "haha");
    $this->removeSelection("fid[]", "label=- Alle Foren -");
    $this->addSelection("fid[]", "label=-- Forum ohne Erfahrung");
    $this->addSelection("fid[]", "label=Testforum");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Es wurden keine Beiträge gefunden."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("keyword", "anhänge");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ergebnis für die Keyword(s) \"anhänge\": 1 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//div[2]/a[2]");
    $this->waitForPageToLoad("30000");
    $this->type("user_name_input", "admin");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ergebnis für die Keyword(s) \"\" und Username(n) \"admin\": 2 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//div[2]/a[2]");
    $this->waitForPageToLoad("30000");
    $this->type("user_name_input", "admin");
    $this->select("result_type", "label=Themen");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ergebnis für die Keyword(s) \"\" und Username(n) \"admin\": 1 Themen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>