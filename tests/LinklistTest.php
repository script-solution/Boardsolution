<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class LinklistTest extends BaseTest
{
  function testMyTestCase()
  {
  	$linkcount = BS_DAO::get_links()->get_count();
    $this->open("/scriptsolution/Boardsolution/index.php?action=linklist");
    $this->ensureAdmin();
    $this->click("link=Linkliste");
    $this->waitForPageToLoad("30000");
    $this->click("link=Link hinzufügen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->assertEquals("Boardsolution » Linkliste » Link hinzufügen", $this->getTitle());
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst alle Felder ausfüllen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("link_url", "www.test.de");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "mein text");
    $this->click("preview");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Vorschau\nmein text"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Der Link wurde erfolgreich hinzugefügt."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Adminbereich");
    $this->waitForPageToLoad("30000");
    $this->selectFrame("navigation");
    $this->click("item_9");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//td[@onclick='rowHL.toggleRowSelected(0);']");
    $this->select("action_type", "label=Löschen");
    $this->click("//input[@value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("del_yes");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Links wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    
    $this->assertEquals($linkcount,BS_DAO::get_links()->get_count(),"Link count changed");
  }
}
?>