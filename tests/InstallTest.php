<?php
/**
 * Unittest
 * 
 * @package			test
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