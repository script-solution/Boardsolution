<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class FAQTest extends BaseTest
{
  function testMyTestCase()
  {
    $this->open("/scriptsolution/Boardsolution/index.php?action=faq");
    $this->click("link=FAQ");
    $this->waitForPageToLoad("30000");
  }
}
?>