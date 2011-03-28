<?php
class ACPFAQTest extends BaseTest
{
  function testFAQ()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_30");
  }
}
?>