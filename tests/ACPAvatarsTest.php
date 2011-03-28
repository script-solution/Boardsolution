<?php
class ACPAvatarsTest extends BaseTest
{
  function testAvatars()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_4");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("search", "test");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Keine Avatare vorhanden"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "1_");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>