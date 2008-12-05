<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class MoveTopicTest extends BaseTest
{
  function testMoveTopic()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "move test");
    $this->type("bbcode_area1", "aaa");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    $this->select("topic_action", "label=Thema verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("leave_link_0");
    $this->click("post_reason_0");
    $this->select("target_forum", "label=Gast Forum");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zum verschobenen Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Neuling mit 7 Punkte, 3 Beiträge"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag move test"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("3 	3"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen verschieben");
    $this->waitForPageToLoad("30000");
    $this->select("target_forum", "label=-- Forum ohne Erfahrung");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du musst etwas im Textfeld eingeben!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "mein");
    $this->type("bbcode_area1", "mein grund");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Themen \"move test\" wurden erfolgreich verschoben."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Zum verschobenen Thema");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Keine neuen Beiträge, nicht wichtig Keine neuen Beiträge, kein 'heißes Thema'\nKeine neuen Beiträge, offen Keine neuen Beiträge, verschoben 	  	move test"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("3 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: test\nZum Thema: Zum letzten Beitrag Noch ein Term ..."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("1 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: admin\nZum Thema: Zum letzten Beitrag move test"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//table[3]/tbody/tr/td[3]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Home");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("0 	0 	-"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("2 	2"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Von: test\nZum Thema: Zum letzten Beitrag Noch ein Term ..."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=admin");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Beiträge: 	2\nPunkte: 	4"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>