<?php
class ACPAttachmentsTest extends BaseTest
{
  function testAttachments()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_5");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("search", "bla");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Es wurden keine Anhänge gefunden."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>