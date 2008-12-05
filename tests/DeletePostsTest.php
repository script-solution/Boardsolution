<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class DeletePostsTest extends BaseTest
{
  function testMyTestCase()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureAdmin();
    $this->click("//td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "neues thema");
    $this->type("bbcode_area1", "asd");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 1");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 2");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Schnell-Antwort");
    $this->type("text", "beitrag 3");
    $this->click("//input[@name='submit' and @value='Absenden']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Beiträge löschen / verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("td_1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Die Beiträge wurden erfolgreich gelöscht."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Beiträge löschen / verschieben");
    $this->waitForPageToLoad("30000");
    $this->click("td_1");
    $this->click("td_2");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Thema löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
  }
}
?>