<?php
class ACPSubscriptionsTest extends BaseTest
{
  function testSubscriptions()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_6");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("search", "noch ein");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Abonnements");
    $this->waitForPageToLoad("30000");
    $this->click("link=Username");
    $this->waitForPageToLoad("30000");
    $this->click("link=Letzter Login");
    $this->waitForPageToLoad("30000");
    $this->click("link=Letzter Beitrag");
    $this->waitForPageToLoad("30000");
    $this->selectFrame("relative=up");
    $this->selectFrame("navigation");
    $this->click("item_32");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Forum abonnieren");
    $this->waitForPageToLoad("30000");
    $this->click("link=Adminbereich");
    $this->waitForPageToLoad("30000");
    $this->selectFrame("navigation");
    $this->click("item_6");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 2 , Gesamt: 2 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>