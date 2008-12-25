<?php
class ACPCorrectMsgsTest extends BaseTest
{
  function testCorrectMsgs()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_24");
  }
}
?>