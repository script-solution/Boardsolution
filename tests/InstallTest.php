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
    $this->open("/scriptsolution/Boardsolution/quickinstall.php?drop=1");
    try {
        $this->assertTrue($this->isTextPresent("Please call this script to generate the settings."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=this script");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("The settings have been (re-)generated successfully!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->open("/scriptsolution/Boardsolution/myisam2inno.php");
    $this->waitForPageToLoad("30000");
    $this->open("/scriptsolution/Boardsolution/inserttestdata.php");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Test-Data inserted!"));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>