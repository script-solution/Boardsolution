<?php
/**
 * Contains the user-details-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$doc->set_template('popup_userdetails.htm');
		$doc->set_show_headline(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$auth = PLIB_Props::get()->auth();
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
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
		
		$avatar = BS_UserUtils::get_instance()->get_profile_avatar($data['avatar'],$data['id']);
		
		$tpl->add_array('data',$data,false);
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
		
		$tpl->add_array('addfields',$addfields);
		
		// generate signature
		if($data['signatur'] != '')
		{
			$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','sig');
			$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','sig');
			$bbcode = new BS_BBCode_Parser($data['signatur'],'sig',$enable_bbcode,$enable_smileys);
			$signature = $bbcode->get_message_for_output();
		}
		else
			$signature = $locale->lang('notavailable');
		
		$rank_data = $functions->get_rank_data($data['exppoints']);
		
		$tpl->add_array('data',$data,false);
		$tpl->add_variables(array(
			'signature' => $signature,
			'rank' => $rank_data['rank']
		));
	}
}
?>