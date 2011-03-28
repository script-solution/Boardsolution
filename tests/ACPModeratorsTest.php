<?php
class ACPModeratorsTest extends BaseTest
{
  function testModerators()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_13");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->type("user_0", "admin");
    $this->type("user_1", "zweitadmin");
    $this->click("//input[@value='Hinzufügen']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("estforum\nadmin Entfernen\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen , Zweitadmin Entfernen"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//img[@alt='Entfernen']");
    $this->waitForPageToLoad("30000");
    $this->click("//a[2]/img");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Testforum\n-\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen\nUsername(n)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->type("user_name_input", "zweitadmin");
    $this->click("//input[@value='Los!']");
    $this->waitForPageToLoad("30000");
    $this->addSelection("forums[4][]", "label=Gast Forum");
    $this->addSelection("forums[4][]", "label=-- Forum ohne Erfahrung");
    $this->click("//input[@value='Speichern']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die moderierten Foren der ausgewählten User wurden gespeichert"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("//input[@value='Zurück']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[3]/tbody/tr/td/div/table/tbody/tr[2]/td/a/img");
    $this->waitForPageToLoad("30000");
    $this->click("//a[2]/img");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("stforum\n-\nUsername(n):\n\n	\nForum ohne Erfahrung\ntest Entfernen\nUsername(n):\nGast Forum\n-\nUser"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>