<?php
/**
 * Contains the (de)activate-links-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The (de)activate-links-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_linklist_activate extends BS_ACP_Action_Base
{
	public function perform_action($active = 0)
	{
		$input = PLIB_Props::get()->input();

		$ids = $input->get_var('delete','post');
		if(!PLIB_Array_Utils::is_integer($ids) || count($ids) == 0)
			return 'Got an invalid id-string via GET';
		
		BS_DAO::get_links()->set_active($ids,$active == 0 ? 0 : 1);
		
		$this->set_show_status_page(false);
		$this->set_action_performed(true);

		return '';
	}
}
?>