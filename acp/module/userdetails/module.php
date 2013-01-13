<?php
/**
 * Contains the user-details-module
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
 * Displays the user-details-page for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_userdetails extends BS_ACP_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->set_template('popup_userdetails.htm');
		$renderer->set_show_headline(false);
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$auth = FWS_Props::get()->auth();
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		
		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id == null)
		{
			$this->report_error();
			return;
		}
		
		$data = BS_DAO::get_profile()->get_user_by_id($id,-1,-1);
		if($data === false)
		{
			$this->report_error();
			return;
		}
		
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(BS_UF_LOC_USER_DETAILS);
		$sub = 0;
		foreach($fields as $field)
		{
			$fdata = $field->get_data();
			$val = $data['add_'.$fdata->get_name()];
			if($field->is_empty($val) && !$fdata->display_empty())
				$sub++;
		}
		
		$avatar = BS_UserUtils::get_profile_avatar($data['avatar'],$data['id']);
		
		$tpl->add_variable_ref('data',$data);
		$tpl->add_variables(array(
			'user_groups' => $auth->get_usergroup_list($data['user_group'],false,true,true),
			'avatar_rowspan' => count($fields) + 3 - $sub,
			'avatar' => $avatar
		));
		
		// display additional fields
		$addfields = array();
		foreach($fields as $field)
		{
			$fdata = $field->get_data();
			$val = $data['add_'.$fdata->get_name()];
			if($field->is_empty($val))
			{
				if(!$fdata->display_empty())
					continue;
				
				$field_value = $locale->lang('notavailable');
			}
			else
				$field_value = $field->get_display($val,'a_main','a_main');
		
			$addfields[] = array(
				'name' => $field->get_title(),
				'value' => $field_value
			);
		}
		
		$tpl->add_variable_ref('addfields',$addfields);
		
		// generate signature
		if($data['signatur'] != '')
		{
			$enable_bbcode = BS_PostingUtils::get_message_option('enable_bbcode','sig');
			$enable_smileys = BS_PostingUtils::get_message_option('enable_smileys','sig');
			$bbcode = new BS_BBCode_Parser($data['signatur'],'sig',$enable_bbcode,$enable_smileys);
			$signature = $bbcode->get_message_for_output();
		}
		else
			$signature = $locale->lang('notavailable');
		
		$rank_data = $functions->get_rank_data($data['exppoints']);
		
		$tpl->add_variable_ref('data',$data);
		$tpl->add_variables(array(
			'signature' => $signature,
			'rank' => $rank_data['rank']
		));
	}
}
?>