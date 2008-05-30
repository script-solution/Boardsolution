<?php
/**
 * Contains the edit-submodule for user
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_USER_EDIT => 'edit'
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->_report_error();
			return;
		}

		// retrieve additional fields to select
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(
			BS_UF_LOC_POSTS | BS_UF_LOC_REGISTRATION | BS_UF_LOC_USER_DETAILS | BS_UF_LOC_USER_PROFILE
		);
		
		// grab userdata from db
		$data = BS_DAO::get_profile()->get_user_by_id($id,1,-1);
		if($data === false)
		{
			$this->_report_error();
			return;
		}

		$order_vals = array('user','reg','group','experience');
		$order = $this->input->correct_var('order','get',PLIB_Input::STRING,$order_vals,'experience');
		$ad = $this->input->correct_var('ad','get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);

		$base_url = $this->url->get_acpmod_url(0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site='.$site);
		
		$url = $base_url.'&amp;action=edit&amp;id='.$id;
		$back_url = $base_url;

		$form = $this->_request_formular(false,false);

		// group combos
		$groups = array();
		$maingroups = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] != BS_STATUS_GUEST)
			{
				if($gdata['is_visible'] == 1)
					$maingroups[$gdata['id']] = $gdata['group_title'];
				$groups[$gdata['id']] = $gdata['group_title'];
			}
		}
		
		$ogroupdb = PLIB_Array_Utils::advanced_explode(',',$data['user_group']);
		unset($ogroupdb[0]);
		$this->tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_USER_EDIT,
			'groups' => $groups,
			'maingroups' => $maingroups,
			'is_own_user' => $id == $this->user->get_user_id(),
			'main_group' => (int)$data['user_group'],
			'other_groups' => $ogroupdb
		));

		// avatar
		$avatar = '';
		$rowspan = BS_ENABLE_EXPORT ? (count($fields) + 1) : 5;
		$avatar = '<td width="35%" align="center" class="a_main" rowspan="'.$rowspan.'">';
		$av = BS_UserUtils::get_instance()->get_profile_avatar($data['avatar'],$data['id']);
		$avatar .= $av;
		if($av != $this->locale->lang('nopictureavailable'))
		{
			$avatar .= '<br />'.$this->locale->lang('delete').': ';
			$avatar .= $form->get_radio_yesno('remove_avatar',0);
		}
		$avatar .= '</td>'."\n";

		$this->tpl->add_variables(array(
			'avatar' => $avatar,
			'avatar_export' => BS_ENABLE_EXPORT ? $avatar : '',
			'target_url' => $url,
			'bbcode_mode' => $data['bbcode_mode'],
			'not_export' => !BS_ENABLE_EXPORT,
			'user_name' => $data['user_name'],
			'user_email' => $data['user_email'],
			'show_avatar' => BS_ENABLE_EXPORT,
			'av_rowspan' => count($fields) + 1,
		));

		$sig_smileys = '';
		$sig_format = '';

		// add additional fields
		$tplfields = array();
		foreach($fields as $field)
		{
			/* @var $field PLIB_AddField_Field */
			$fdata = $field->get_data();
			$field_name = $fdata->get_name();
			$stored_val = $data['add_'.$field_name];
			$value = $field->get_value_from_formular($stored_val);

			$tplfields[] = array(
				'is_required' => $fdata->is_required() ? ' *' : '',
				'name' => $field->get_title(),
				'value' => $field->get_formular_field($form,$value)
			);
		}
		
		// add signature form
		$pform = new BS_PostingForm($this->locale->lang('signature'),$data['signature_posted'],'sig');
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		// set colspan for the post-form-template
		$this->tpl->set_template('inc_post_form.htm');
		$this->tpl->add_variables(array(
			'colspan_main' => 2
		));
		$this->tpl->restore_template();

		// some other stuff
		$this->tpl->add_variables(array(
			'addfields' => $tplfields,
			'signature' => $form->get_input_value('text',$data['signature_posted']),
			'base_url' => $back_url
		));
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		return array(
			$this->locale->lang('edit_user') => $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
}
?>