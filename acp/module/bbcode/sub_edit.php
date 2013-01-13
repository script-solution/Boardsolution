<?php
/**
 * Contains the submodule for editing and add bbcodes
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
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ADD_BBCODE,array('edit','add'));
		$renderer->add_action(BS_ACP_ACTION_EDIT_BBCODE,array('edit','edit'));

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
			$renderer->add_breadcrumb($locale->lang('add_tag'),BS_URL::build_acpsub_url());
		else
		{
			$url = BS_URL::get_acpsub_url();
			$url->set('id',$id);
			$renderer->add_breadcrumb($locale->lang('edit_tag'),$url->to_url());
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
		$url = BS_URL::get_acpsub_url();
		$url->set('id',$id);
		$url->set('site',$site);
		
		$tpl->add_variables(array(
			'default' => $data,
			'site' => $site,
			'types' => $types,
			'contents' => $contents,
			'param_combo' => $param_combo->to_html(),
			'param_types' => $param_types,
			'action_type' => $action_type,
			'title' => $title,
			'form_target' => $url->to_url()
		));
	}
}
?>