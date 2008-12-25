<?php
class ACPBotsTest extends BaseTest
{
  function testBots()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_8");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Bot hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->type("bot_name", "mein bot");
    $this->type("bot_match", "blub/");
    $this->type("bot_ip_start", "123.*");
    $this->type("bot_ip_end", "124.*");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Ungültige Start-IP!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bot_ip_start", "123.0.0.0");
    $this->type("bot_ip_end", "123.0.0.1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Bot wurde erfolgreich hinzugefügt"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Bots");
    $this->waitForPageToLoad("30000");
    $this->click("//tr[@id='row_12']/td[5]/a/img");
    $this->waitForPageToLoad("30000");
    $this->type("bot_name", "mein bot2");
    $this->type("bot_match", "blub/2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("mein bot2", $this->getValue("bot_name"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("blub/2", $this->getValue("bot_match"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(12);']");
    $this->click("//input[@value='Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Anzeige: 1 - 12 , Gesamt: 12 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "google");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 2 , Gesamt: 2 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>