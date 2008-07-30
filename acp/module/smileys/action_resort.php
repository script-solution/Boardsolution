<?php
/**
 * Contains the resort-smileys-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The resort-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_resort extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$locale = FWS_Props::get()->locale();

		$i = 1;
		foreach(BS_DAO::get_smileys()->get_list() as $smiley)
		{
			$fields = array(
				'sort_key' => $i++
			);
			BS_DAO::get_smileys()->update_by_id($smiley['id'],$fields);
		}
		
		$this->set_success_msg($locale->lang('sort_successfully_corrected'));
		$this->set_action_performed(true);

		return '';
	}
}
?>