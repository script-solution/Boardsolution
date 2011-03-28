<?php
class ACPLanguagesTest extends BaseTest
{
  function testLanguages()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_3");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Neue Sprache hinzufügen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Es wurde erfolgreich eine Sprache hinzugefügt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("names[5]", "test");
    $this->type("folders[5]", "test");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sprachen wurden erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("test", $this->getValue("names[5]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//td[@onclick='rowHL.toggleRowSelected(4);']");
    $this->click("//input[@value='Speichern / Löschen']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sprachen wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "deu");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("Deutsch (Sie-Version)", $this->getValue("names[1]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertEquals("Deutsch (Du-Version)", $this->getValue("names[3]"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Sprachen");
    $this->waitForPageToLoad("30000");
  }
}
?>