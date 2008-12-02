<?php
/**
 * The base-class for all tests
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	main
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

class BaseTest extends PHPUnit_Extensions_SeleniumTestCase
{
	protected $verificationErrors = array();
	
  protected function setUp()
  {
    $this->setBrowser('*opera /usr/bin/opera');
    $this->setBrowserUrl("http://localhost/");
  }
  
  protected function ensureAdmin()
  {
  	if(!$this->isTextPresent("Willkommen, admin!"))
    {
	    $this->type("user_login", "admin");
	    $this->type("pw_login", "admin");
	    $this->click("//input[@value=' Login ']");
	    $this->waitForPageToLoad("30000");
    }
  }
}
?>