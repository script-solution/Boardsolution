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
class ACPDBCacheTest extends BaseTest
{
	function testDBCache()
  {
    $this->loginToACP();
    $this->selectFrame("navigation");
    $this->click("item_22");
    $this->selectFrame("relative=up");
    $this->selectFrame("content");
    $this->click("link=Markierung umkehren");
    $this->click("//input[@value='Aktualisieren']");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Hinweis: Der cache der Tabellen \"config\", \"stats\", \"acp_access\", \"banlist\", \"themes\", \"languages\", \"bots\", \"intern\", \"user_groups\", \"tasks\", \"user_fields\", \"user_ranks\", \"moderators\" wurde erfolgreich neu generiert."));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
    $this->click("link=Anzeigen");
    $this->waitForPageToLoad("30000");
    try {
        $this->assertTrue($this->isTextPresent("Details von \"config\""));
    } catch (PHPUnit_Framework_AssertionFailedError $e) {
        array_push($this->verificationErrors, $e->toString());
    }
  }
}
?>