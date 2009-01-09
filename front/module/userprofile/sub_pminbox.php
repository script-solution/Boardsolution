<?php
/**
 * Contains the pminbox-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pminbox submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pminbox extends BS_Front_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_DELETE_PMS,'deletepms');
		$renderer->add_action(BS_ACTION_MARK_PMS_READ,'pmmarkread');
		$renderer->add_action(BS_ACTION_MARK_PMS_UNREAD,'pmmarkunread');

		$renderer->add_breadcrumb($locale->lang('pminbox'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(FWS_Document_Messages::NO_ACCESS);
			return;
		}
		
		$helper->add_pm_delete_message();
		$helper->add_folder('inbox','detail');
	}
}
?>