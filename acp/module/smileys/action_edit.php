<?php
/**
 * Contains the edit-smiley-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit-smiley-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_edit extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		if(BS_DAO::get_smileys()->get_by_id($id) === false)
			return 'No smiley found with id "'.$id.'"';
		
		$smiley_path = $input->get_var('smiley_path','post',PLIB_Input::STRING);
		$primary_code = $input->get_var('primary_code','post',PLIB_Input::STRING);
		$secondary_code = $input->get_var('secondary_code','post',PLIB_Input::STRING);
		$is_base = $input->get_var('is_base','post',PLIB_Input::INT_BOOL);

		if($smiley_path == '')
			return 'smiley_path_empty';

		$smiley_path = basename($smiley_path);
		if(!preg_match('/\.(jpg|jpeg|gif|png|bmp)$/i',$smiley_path))
			return 'smiley_path_invalid';

		if($primary_code == '')
			return 'smiley_primary_code_empty';

		// check wether the codes exist
		if(BS_DAO::get_smileys()->code_exists($primary_code,$id))
			return sprintf($locale->lang('smiley_code_exists'),$primary_code);
		if($secondary_code != '' && BS_DAO::get_smileys()->code_exists($secondary_code,$id))
			return sprintf($locale->lang('smiley_code_exists'),$secondary_code);
		
		$fields = array(
			'smiley_path' => $smiley_path,
			'primary_code' => $primary_code,
			'secondary_code' => $secondary_code,
			'is_base' => $is_base
		);
		BS_DAO::get_smileys()->update_by_id($id,$fields);
		
		$this->set_success_msg($locale->lang('smiley_edit_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>