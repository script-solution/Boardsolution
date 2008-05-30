<?php
/**
 * Contains the pminbox-userprofile-submodule
 * 
 * @version			$Id: sub_pminbox.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pminbox submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pminbox extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_DELETE_PMS => 'deletepms',
			BS_ACTION_MARK_PMS_READ => 'pmmarkread',
			BS_ACTION_MARK_PMS_UNREAD => 'pmmarkunread'
		);
	}
	
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->_report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$helper->add_pm_delete_message();
		$helper->add_folder('inbox','detail');
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('pminbox') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pminbox')
		);
	}
}
?>