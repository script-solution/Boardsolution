<?php
class ACPPHPInfoTest extends BaseTest
{
  function testPHPInfo()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_26");
  }
}
?>