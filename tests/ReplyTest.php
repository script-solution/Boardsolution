<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class ReplyTest extends BaseTest
{
  function testReply()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Noch ein Termin in nem Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "erster neuer beitrag");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("erster neuer beitrag"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("quote_link_5");
    $this->click("//div[6]/div[2]/a[1]");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("[QUOTE=test]hahahaha[/QUOTE]", $this->getValue("bbcode_area1"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("bbcode_area1", "[QUOTE=test]hahahaha[/QUOTE]\nund ich geb auch noch meinen senf dazu");
    $this->click("preview");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Vorschau\ntest hat folgendes geschrieben:\nhahahaha\n\nund ich geb auch noch meinen senf dazu"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("test hat folgendes geschrieben:\nhahahaha\n\nund ich geb auch noch meinen senf dazu"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//a[contains(@href, '/scriptsolution/Boardsolution/index.php?action=edit_post&fid=2&tid=3&site=1&id=7')]");
    $this->waitForPageToLoad("30000");
    $this->type("bbcode_area1", "[QUOTE=test]hahahaha[/QUOTE]\nund ich geb auch noch meinen senf dazu\nwarum das denn?");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Beitrag wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zum Beitrag");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("test hat folgendes geschrieben:\nhahahaha\n\nund ich geb auch noch meinen senf dazu\nwarum das denn?"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    try {
        $this->assertTrue($this->isTextPresent("Dieser Beitrag wurde insgesamt 1 mal editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//a[contains(@href, '/scriptsolution/Boardsolution/index.php?action=delete_post&fid=2&tid=3&id=7')]");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
  }
}
?>