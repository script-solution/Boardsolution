<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class OpenCloseTopicTest extends BaseTest
{
  function testOpenClose()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "open / close");
    $this->type("bbcode_area1", "aaa");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    $this->select("topic_action", "label=Thema schließen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("post_reason_0");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen \"open / close\" wurden erfolgreich geschlossen."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Keine neuen Beiträge, nicht wichtig Keine neuen Beiträge, kein 'heißes Thema'\nKeine neuen Beiträge, geschlossen Keine neuen Beiträge, nicht verschoben 	  	open / close"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=open / close");
    $this->waitForPageToLoad("30000");
    $this->select("topic_action", "label=Thema öffnen");
    $this->waitForPageToLoad("30000");
    $this->type("bbcode_area1", "mit grund");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen \"open / close\" wurden erfolgreich geöffnet."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Keine neuen Beiträge, nicht wichtig Keine neuen Beiträge, kein 'heißes Thema'\nKeine neuen Beiträge, offen Keine neuen Beiträge, nicht verschoben 	  	open / close"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=open / close");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("mit grund"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->select("topic_action", "label=Thema löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
  }
}
?>