<?php
/**
 * Contains the edit-smiley-action
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		// nothing to do?
		if(!$input->isset_var('submit','post'))
			return '';
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			return 'Invalid id "'.$id.'"';

		if(BS_DAO::get_smileys()->get_by_id($id) === false)
			return 'No smiley found with id "'.$id.'"';
		
		$smiley_path = $input->get_var('smiley_path','post',FWS_Input::STRING);
		$primary_code = $input->get_var('primary_code','post',FWS_Input::STRING);
		$secondary_code = $input->get_var('secondary_code','post',FWS_Input::STRING);
		$is_base = $input->get_var('is_base','post',FWS_Input::INT_BOOL);

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