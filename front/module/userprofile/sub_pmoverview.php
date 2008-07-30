<?php
/**
 * Contains the pmoverview-userprofile-submodule
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmoverview submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmoverview extends BS_Front_SubModule
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_DELETE_PMS,'deletepms');
		$renderer->add_action(BS_ACTION_MARK_PMS_READ,'pmmarkread');
		$renderer->add_action(BS_ACTION_MARK_PMS_UNREAD,'pmmarkunread');

		$renderer->add_breadcrumb($locale->lang('overview'),$url->get_url(0,'&amp;'.BS_URL_LOC.'=pmoverview'));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$helper = BS_Front_Module_UserProfile_Helper::get_instance();
		if($helper->get_pm_permission() < 1)
		{
			$this->report_error(PLIB_Document_Messages::NO_ACCESS);
			return;
		}
		
		$helper->add_pm_delete_message();
		$helper->add_folder('inbox');
		$helper->add_folder('outbox');
	}
}
?>