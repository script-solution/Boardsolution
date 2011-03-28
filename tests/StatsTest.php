<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class StatsTest extends BaseTest
{
  function testMyTestCase()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=stats");
    $this->click("link=Statistik");
    $this->waitForPageToLoad("30000");
    $this->click("link=Verlaufsstatistik");
    $this->waitForPageToLoad("30000");
  }
}
?>