<?php
class ACPDBCacheTest extends BaseTest
{
	function testDBCache()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_22");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Markierung umkehren");
    $this->click("//input[@value='Aktualisieren']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der cache der Tabellen \"config\", \"stats\", \"acp_access\", \"banlist\", \"themes\", \"languages\", \"bots\", \"intern\", \"user_groups\", \"tasks\", \"user_fields\", \"user_ranks\", \"moderators\" wurde erfolgreich neu generiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Anzeigen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Details von \"config\""));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>