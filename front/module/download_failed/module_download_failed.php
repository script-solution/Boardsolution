<?php
/**
 * Contains the download-failed-module
 * 
 * @version			$Id: module_download_failed.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The download-failed-module. Will be used to display that a download is not allowed (the user
 * will be redirected to this module)
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_download_failed extends BS_Front_Module
{
	public function run()
	{
		$this->functions->show_login_form();
	}
	
	public function get_location()
	{
		return array();
	}
}
?>