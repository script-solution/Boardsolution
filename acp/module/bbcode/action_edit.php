<?php
/**
 * Contains the add-/edit-bbcode-action
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
 * The add-/edit-bbcode-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_bbcode_edit extends BS_ACP_Action_Base
{
	public function perform_action($mode = 'edit')
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();

		// check id if we want to edit
		if($mode == 'edit')
		{
			$id = $input->get_var('id','get',FWS_Input::ID);
			if($id == null)
				return 'The parameter id is invalid';
			
			$tag = BS_DAO::get_bbcodes()->get_by_id($id);
			if($tag === false)
				return 'A tag with id "'.$id.'" doesn\'t exist';
		}
		
		// grab parameters from POST
		$name = $input->get_var('name','post',FWS_Input::STRING);
		$type = $input->get_var('type','post',FWS_Input::STRING);
		$type_custom = $input->get_var('type_custom','post',FWS_Input::STRING);
		$content = $input->get_var('content','post',FWS_Input::STRING);
		$content_custom = $input->get_var('content_custom','post',FWS_Input::STRING);
		$param = $input->correct_var(
			'param','post',FWS_Input::STRING,array('no','optional','required'),'no'
		);
		$param_type = $input->correct_var(
			'param_type','post',FWS_Input::STRING,
			array('text','integer','identifier','color','url','mail'),'text'
		);
		$replacement = $input->get_var('replacement','post',FWS_Input::STRING);
		$replacement_param = $input->get_var('replacement_param','post',FWS_Input::STRING);
		$allowed_content = $input->get_var('allowed_content','post',FWS_Input::STRING);
		$allow_nesting = $input->get_var('allow_nesting','post',FWS_Input::INT_BOOL);
		$ignore_whitespace = $input->get_var('ignore_whitespace','post',FWS_Input::INT_BOOL);
		$ignore_unknown_tags = $input->get_var('ignore_unknown_tags','post',FWS_Input::INT_BOOL);
		
		// check parameters
		if(empty($name))
			return 'tag_name_empty';
		
		if(BS_DAO::get_bbcodes()->name_exists($name,$mode == 'edit' ? $id : 0))
			return 'tag_name_exists';
		
		$types = array_merge(array('inline','block','link'),BS_DAO::get_bbcodes()->get_types());
		if(!in_array($type,$types))
			return 'Invalid type "'.$type.'"!';
		
		// use custom?
		if(!empty($type_custom))
			$type = $type_custom;
		
		$contents = BS_DAO::get_bbcodes()->get_contents();
		if(!in_array($content,$contents))
			return 'Invalid content "'.$content.'"!';
		
		// use custom?
		if(!empty($content_custom))
			$content = $content_custom;
		
		if($param == 'required' && empty($replacement_param))
			return 'replacement_param_required';
		if($param != 'required' && empty($replacement))
			return 'replacement_required';
		
		$replacement = FWS_StringHelper::htmlspecialchars_back($replacement);
		$replacement_param = FWS_StringHelper::htmlspecialchars_back($replacement_param);
		
		$allowed_content_types = FWS_Array_Utils::advanced_explode(',',$allowed_content);
		foreach($allowed_content_types as $actype)
		{
			if(!in_array($actype,$types))
				return 'Invalid type "'.$actype.'"!';
		}
		$allowed_content = implode(',',$allowed_content_types);
		
		// build fields for the SQL-statement
		$fields = array(
			'name' => $name,
			'type' => $type,
			'content' => $content,
			'param' => $param,
			'param_type' => $param_type,
			'replacement' => $replacement,
			'replacement_param' => $replacement_param,
			'allowed_content' => $allowed_content,
			'allow_nesting' => $allow_nesting,
			'ignore_whitespace' => $ignore_whitespace,
			'ignore_unknown_tags' => $ignore_unknown_tags
		);
		
		// create or update the tag
		if($mode == 'edit')
			BS_DAO::get_bbcodes()->update_by_id($id,$fields);
		else
			BS_DAO::get_bbcodes()->create($fields);
		
		// we are finished :)
		$this->set_success_msg($locale->lang('tag_'.$mode.'_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>