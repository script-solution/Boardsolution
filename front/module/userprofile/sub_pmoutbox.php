<?php
/**
 * Contains the pmoutbox-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmoutbox submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmoutbox extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		
		$doc->add_action(BS_ACTION_DELETE_PMS,'deletepms');
		$doc->add_action(BS_ACTION_MARK_PMS_READ,'pmmarkread');
		$doc->add_action(BS_ACTION_MARK_PMS_UNREAD,'pmmarkunread');

		$doc->add_breadcrumb($locale->lang('pmoutbox'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=pmoutbox'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(PLIB_Messages::MSG_TYPE_NO_ACCESS);
			return;
		}
		
		$helper->add_pm_delete_message();
		$helper->add_folder('outbox','detail');
	}
}
?>