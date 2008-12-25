<?php
class ACPSmileysTest extends BaseTest
{
  function testSmileys()
  {
  	$this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_2");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("//img[@alt='Editieren']");
    $this->waitForPageToLoad("30000");
    $this->type("secondary_code", ":)");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Der Smiley-Code \":)\" existiert bereits."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("secondary_code", "(-8");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der Smiley wurde erfolgreich editiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("secondary_code", "");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertEquals("", $this->getValue("secondary_code"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Smileys");
    $this->waitForPageToLoad("30000");
    $this->click("//img[@alt='Dieses Forum um eine Stelle nach oben verschieben']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent(":-) 	:-) 	:) 	Ja 	( 1 ) n/a Dieses Forum um eine Stelle nach unten verschieben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Dieses Forum um eine Stelle nach unten verschieben']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("=) 	=) 		Ja 	( 1 ) n/a Dieses Forum um eine Stelle nach unten verschieben"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("search", "roll");
    $this->click("//input[@value='Suchen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("( Anzeige: 1 - 1 , Gesamt: 1 )"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Smileys");
    $this->waitForPageToLoad("30000");
    $this->click("link=Sortierung korrigieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Sortierung wurde erfolgreich korrigiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>