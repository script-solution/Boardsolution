<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class InstallTest extends PHPUnit_Extensions_SeleniumTestCase
{
	private $verificationErrors = array();
	
  function setUp()
  {
    $this->setBrowser('*opera /usr/bin/opera');
    $this->setBrowserUrl("http://localhost/");
  }

  function testMyTestCase()
  {
    $this->open("/scriptsolution/Boardsolution/scripts/quickinstall.php?drop=1");
    try {
        $this->assertTrue($this->isTextPresent("Boardsolution has been installed successfully! :-)"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/scriptsolution/Boardsolution/scripts/myisam2inno.php");
    $this->waitForPageToLoad("30000");
    $this->open("/scriptsolution/Boardsolution/scripts/inserttestdata.php");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Test-Data inserted!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>