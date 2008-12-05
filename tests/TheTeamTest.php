<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class TheTeamTest extends BaseTest
{
  function testMyTestCase()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=team");
    $this->waitForPageToLoad("30000");
  }
}
?>