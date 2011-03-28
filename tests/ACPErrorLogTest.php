<?php
class ACPErrorLogTest extends BaseTest
{
  function testErrorLog()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_28");
    $this->waitForPageToLoad("30000");
  }
}
?>