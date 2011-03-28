<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class LockTopicTest extends BaseTest
{
  function testLock()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=forums");
    $this->waitForPageToLoad("30000");
    $this->ensureUsertest();
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=Neues Thema");
    $this->waitForPageToLoad("30000");
    $this->type("topic_name", "noch eins von mir");
    $this->type("bbcode_area1", "abc");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zum erstellten Thema");
    $this->waitForPageToLoad("30000");
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("link=Board-Index");
    $this->waitForPageToLoad("30000");
    $this->type("user_login", "admin");
    $this->type("pw_login", "admin");
    $this->click("//input[@value=' Login ']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("link=noch eins von mir");
    $this->waitForPageToLoad("30000");
    $this->select("topic_action", "label=Thema sperren");
    $this->waitForPageToLoad("30000");
    $this->click("edit_topic_1");
    $this->click("openclose_topic_1");
    $this->click("posts_topic_1");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("link=Board-Index");
    $this->waitForPageToLoad("30000");
    $this->type("user_login", "test");
    $this->type("pw_login", "test");
    $this->click("//input[@value=' Login ']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("id_0");
    $this->select("topic_action", "label=Thema editieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du hast keine Berechtigung dieses Thema zu editieren, da ein Moderator oder Administrator dieses Thema gesperrt hat."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gast Forum");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen schließen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du hast keine Themen ausgewählt, für die die gewählte Aktion erlaubt ist.\nDer Grund könnte sein, dass ein Moderator oder Administrator das Thema gesperrt hat."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gast Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=noch eins von mir");
    $this->waitForPageToLoad("30000");
    $this->click("link=Editieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Fehler: Du hast keine Berechtigung diesen Beitrag zu editieren, da ein Moderator oder Administrator ihn gesperrt hat."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=noch eins von mir");
    $this->waitForPageToLoad("30000");
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("link=Board-Index");
    $this->waitForPageToLoad("30000");
    $this->type("user_login", "admin");
    $this->type("pw_login", "admin");
    $this->click("//input[@value=' Login ']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen sperren");
    $this->waitForPageToLoad("30000");
    $this->click("edit_topic_0");
    $this->click("openclose_topic_0");
    $this->click("posts_topic_0");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Zurück");
    $this->waitForPageToLoad("30000");
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("link=Board-Index");
    $this->waitForPageToLoad("30000");
    $this->type("user_login", "test");
    $this->type("pw_login", "test");
    $this->click("//input[@value=' Login ']");
    $this->waitForPageToLoad("30000");
    $this->click("//table[4]/tbody/tr/td[2]/a");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Thema editieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Forum:\n    Gast Forum"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gast Forum");
    $this->waitForPageToLoad("30000");
    $this->click("//td[@onclick=\"var cb = document.getElementById('id_0'); cb.checked = cb.checked ? false : true;\"]");
    $this->select("topic_action", "label=Themen schließen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Ausgewählte Themen:\n      	Gast Forum » noch eins von mir"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Gast Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=noch eins von mir");
    $this->waitForPageToLoad("30000");
    $this->click("link=Editieren");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("User:\n    test"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Logout");
    $this->waitForPageToLoad("30000");
    $this->click("link=Board-Index");
    $this->waitForPageToLoad("30000");
    $this->type("user_login", "admin");
    $this->type("pw_login", "admin");
    $this->click("//input[@value=' Login ']");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gast Forum");
    $this->waitForPageToLoad("30000");
    $this->click("link=noch eins von mir");
    $this->waitForPageToLoad("30000");
    $this->select("topic_action", "label=Thema löschen");
    $this->waitForPageToLoad("30000");
    $this->click("submit");
    $this->waitForPageToLoad("30000");
    $this->click("link=Gehe zurück zum Forum");
    $this->waitForPageToLoad("30000");
  }
}
?>