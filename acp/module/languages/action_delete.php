<?php
/**
 * Contains the delete-languages-action
 *
 * @version			$Id: action_delete.php 750 2008-05-24 15:39:08Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The delete-languages-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_languages_delete extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$id_str = $this->input->get_var('ids','get',PLIB_Input::STRING);
		if(!($ids = PLIB_StringHelper::get_ids($id_str)))
			return 'Got an invalid id-string via GET';
		
		$count = BS_DAO::get_langs()->delete_by_ids($ids);

		if($count > 0)
			$this->cache->refresh('languages');

		$this->set_success_msg($this->locale->lang('lang_delete_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>