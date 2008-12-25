<?php
class ACPRanksTest extends BaseTest
{
  function testRanks()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_18");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Neue Gruppe hinzufügen']");
    $this->waitForPageToLoad("30000");
    $this->type("post_to[8]", "1500");
    $this->type("rank_name[8]", "test");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("1500", $this->getValue("post_to[8]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("test", $this->getValue("rank_name[8]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(8);']");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("3000", $this->getValue("post_to[6]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>