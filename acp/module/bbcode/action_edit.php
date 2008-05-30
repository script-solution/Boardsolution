<?php
/**
 * Contains the add-/edit-bbcode-action
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		// check id if we want to edit
		if($mode == 'edit')
		{
			$id = $this->input->get_var('id','get',PLIB_Input::ID);
			if($id == null)
				return 'The parameter id is invalid';
			
			$tag = BS_DAO::get_bbcodes()->get_by_id($id);
			if($tag === false)
				return 'A tag with id "'.$id.'" doesn\'t exist';
		}
		
		// grab parameters from POST
		$name = $this->input->get_var('name','post',PLIB_Input::STRING);
		$type = $this->input->get_var('type','post',PLIB_Input::STRING);
		$type_custom = $this->input->get_var('type_custom','post',PLIB_Input::STRING);
		$content = $this->input->get_var('content','post',PLIB_Input::STRING);
		$content_custom = $this->input->get_var('content_custom','post',PLIB_Input::STRING);
		$param = $this->input->correct_var(
			'param','post',PLIB_Input::STRING,array('no','optional','required'),'no'
		);
		$param_type = $this->input->correct_var(
			'param_type','post',PLIB_Input::STRING,
			array('text','integer','identifier','color','url','mail'),'text'
		);
		$replacement = $this->input->get_var('replacement','post',PLIB_Input::STRING);
		$replacement_param = $this->input->get_var('replacement_param','post',PLIB_Input::STRING);
		$allowed_content = $this->input->get_var('allowed_content','post',PLIB_Input::STRING);
		$allow_nesting = $this->input->get_var('allow_nesting','post',PLIB_Input::INT_BOOL);
		$ignore_whitespace = $this->input->get_var('ignore_whitespace','post',PLIB_Input::INT_BOOL);
		$ignore_unknown_tags = $this->input->get_var('ignore_unknown_tags','post',PLIB_Input::INT_BOOL);
		
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
		
		$replacement = PLIB_StringHelper::htmlspecialchars_back($replacement);
		$replacement_param = PLIB_StringHelper::htmlspecialchars_back($replacement_param);
		
		$allowed_content_types = PLIB_Array_Utils::advanced_explode(',',$allowed_content);
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
		$this->set_success_msg($this->locale->lang('tag_'.$mode.'_success'));
		$this->set_action_performed(true);

		return '';
	}
}
?>