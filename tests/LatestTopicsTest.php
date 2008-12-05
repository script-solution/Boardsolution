<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class LatestTopicsTest extends BaseTest
{
  function testLatestTopics()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=latest_topics");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("link=Aktuelle Themen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 3 , Gesamt: 3 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("fid", "label=Testforum");
    $this->click("//input[@value='Anzeigen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("fid", "label=Gast Forum");
    $this->click("//input[@value='Anzeigen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 2 , Gesamt: 2 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>