<?php
/**
 * Contains the submodule for editing and add bbcodes
 * 
 * @version			$Id: sub_edit.php 796 2008-05-29 18:23:27Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The submodule for editing and add bbcodes
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_bbcode_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_BBCODE => array('edit','add'),
			BS_ACP_ACTION_EDIT_BBCODE => array('edit','edit')
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$data = array(
				'name' => '',
				'type' => 'inline',
				'content' => 'text',
				'replacement' => '',
				'replacement_param' => '',
				'param' => 'no',
				'param_type' => '',
				'allow_nesting' => 0,
				'ignore_whitespace' => 0,
				'ignore_unknown_tags' => 0,
				'allowed_content' => 'inline,link'
			);
			$title = $this->locale->lang('add_tag');
			$action_type = BS_ACP_ACTION_ADD_BBCODE;
		}
		else
		{
			$data = BS_DAO::get_bbcodes()->get_by_id($id);
			$title = $this->locale->lang('edit_tag');
			$action_type = BS_ACP_ACTION_EDIT_BBCODE;
		}
		
		$form = $this->_request_formular();
		
		$types = array();
		$alltypes = array_merge(array('inline','block','link'),BS_DAO::get_bbcodes()->get_types());
		foreach($alltypes as $type)
		{
			if($this->locale->contains_lang('tag_type_'.$type))
				$types[$type] = $this->locale->lang('tag_type_'.$type);
			else
				$types[$type] = $type;
		}
		
		$contents = array();
		foreach(BS_DAO::get_bbcodes()->get_contents() as $con)
		{
			if($this->locale->contains_lang('tag_content_'.$con))
				$contents[$con] = $this->locale->lang('tag_content_'.$con);
			else
				$contents[$con] = $con;
		}
		
		$param_types = array(
			'text' => $this->locale->lang('tag_param_type_text'),
			'identifier' => $this->locale->lang('tag_param_type_identifier'),
			'integer' => $this->locale->lang('tag_param_type_integer'),
			'color' => $this->locale->lang('tag_param_type_color'),
			'url' => $this->locale->lang('tag_param_type_url'),
			'mail' => $this->locale->lang('tag_param_type_mail'),
		);
		
		$params = array(
			'no' => $this->locale->lang('tag_param_no'),
			'optional' => $this->locale->lang('tag_param_optional'),
			'required' => $this->locale->lang('tag_param_required')
		);
		$param_combo = new PLIB_HTML_ComboBox('param','param',null,$data['param']);
		$param_combo->set_options($params);
		if($form->get_condition())
			$param_combo->set_value($form->get_input_value('param'));
		$param_combo->set_custom_attribute('onchange','toggleParameter();');
		
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		$this->tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'types' => $types,
			'contents' => $contents,
			'param_combo' => $param_combo->to_html(),
			'param_types' => $param_types,
			'action_type' => $action_type,
			'title' => $title,
			'form_target' => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id.'&amp;site='.$site)
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			return array(
				$this->locale->lang('add_tag') => $this->url->get_acpmod_url(0,'&amp;action=edit')
			);
		}
		
		return array(
			$this->locale->lang('edit_tag') => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
}
?>