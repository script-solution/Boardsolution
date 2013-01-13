<?php
/**
 * The base-class for all tests
 * 
 * @package			Boardsolution
 * @subpackage	main
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class BaseTest extends PHPUnit_Extensions_SeleniumTestCase
{
	protected $verificationErrors = array();
	
  protected function setUp()
  {
    $this->setBrowser('*opera /usr/bin/opera');
    $this->setBrowserUrl("http://localhost/");
  }
  
  protected function loginToACP()
  {
  	$this->open("/scriptsolution/Boardsolution/admin.php");
    $this->type("user_login", "admin");
    $this->type("pw_login", "admin");
    $this->click("//input[@value='Login']");
    $this->waitForPageToLoad("30000");
  }
  
  protected function ensureUsertest()
  {
  	if(!$this->isTextPresent("Willkommen, test!"))
  	{
  		$this->type("user_login", "test");
	    $this->type("pw_login", "test");
	    $this->click("//input[@value=' Login ']");
	    $this->waitForPageToLoad("30000");
  	}
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