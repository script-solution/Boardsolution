<?php
class ACPIPLogTest extends BaseTest
{
  function testIPLog()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_27");
    $this->waitForPageToLoad("30000");
  }
}
?>