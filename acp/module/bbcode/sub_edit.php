<?php
/**
 * Contains the submodule for editing and add bbcodes
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ADD_BBCODE,array('edit','add'));
		$renderer->add_action(BS_ACP_ACTION_EDIT_BBCODE,array('edit','edit'));

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			$renderer->add_breadcrumb($locale->lang('add_tag'),$url->get_acpmod_url(0,'&amp;action=edit'));
		else
		{
			$renderer->add_breadcrumb(
				$locale->lang('edit_tag'),
				$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
			);
		}
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$url = FWS_Props::get()->url();

		$id = $input->get_var('id','get',FWS_Input::ID);
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
			$title = $locale->lang('add_tag');
			$action_type = BS_ACP_ACTION_ADD_BBCODE;
		}
		else
		{
			$data = BS_DAO::get_bbcodes()->get_by_id($id);
			$title = $locale->lang('edit_tag');
			$action_type = BS_ACP_ACTION_EDIT_BBCODE;
		}
		
		$form = $this->request_formular();
		
		$types = array();
		$alltypes = array_merge(array('inline','block','link'),BS_DAO::get_bbcodes()->get_types());
		foreach($alltypes as $type)
		{
			if($locale->contains_lang('tag_type_'.$type))
				$types[$type] = $locale->lang('tag_type_'.$type);
			else
				$types[$type] = $type;
		}
		
		$contents = array();
		foreach(BS_DAO::get_bbcodes()->get_contents() as $con)
		{
			if($locale->contains_lang('tag_content_'.$con))
				$contents[$con] = $locale->lang('tag_content_'.$con);
			else
				$contents[$con] = $con;
		}
		
		$param_types = array(
			'text' => $locale->lang('tag_param_type_text'),
			'identifier' => $locale->lang('tag_param_type_identifier'),
			'integer' => $locale->lang('tag_param_type_integer'),
			'color' => $locale->lang('tag_param_type_color'),
			'url' => $locale->lang('tag_param_type_url'),
			'mail' => $locale->lang('tag_param_type_mail'),
		);
		
		$params = array(
			'no' => $locale->lang('tag_param_no'),
			'optional' => $locale->lang('tag_param_optional'),
			'required' => $locale->lang('tag_param_required')
		);
		$param_combo = new FWS_HTML_ComboBox('param','param',null,$data['param']);
		$param_combo->set_options($params);
		if($form->get_condition())
			$param_combo->set_value($form->get_input_value('param'));
		$param_combo->set_custom_attribute('onchange','toggleParameter();');
		
		$site = $input->get_var('site','get',FWS_Input::INTEGER);
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'types' => $types,
			'contents' => $contents,
			'param_combo' => $param_combo->to_html(),
			'param_types' => $param_types,
			'action_type' => $action_type,
			'title' => $title,
			'form_target' => $url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id.'&amp;site='.$site)
		));
	}
}
?>