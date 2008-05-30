<?php
/**
 * Contains the delete-smileys-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		foreach(BS_DAO::get_smileys()->get_all() as $data)
		{
			if(in_array($data['id'],$ids))
				@unlink('images/smileys/'.$data['smiley_path']);
		}

		BS_DAO::get_smileys()->delete_by_ids($ids);
		
		$this->set_success_msg($this->locale->lang('delete_smileys_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>