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
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = PLIB_Props::get()->input();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_USER_EDIT,'edit');
		
		$id = $input->get_var('id','get',PLIB_Input::ID);
		$renderer->add_breadcrumb(
			$locale->lang('edit_user'),
			$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
		);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$cache = PLIB_Props::get()->cache();
		$tpl = PLIB_Props::get()->tpl();
		$user = PLIB_Props::get()->user();
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();

		$id = $input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
		{
			$this->report_error();
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
			$this->report_error();
			return;
		}

		$order_vals = array('user','reg','group','experience');
		$order = $input->correct_var('order','get',PLIB_Input::STRING,$order_vals,'experience');
		$ad = $input->correct_var('ad','get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		$site = $input->get_var('site','get',PLIB_Input::INTEGER);

		$base_url = $url->get_acpmod_url(0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site='.$site);
		
		$murl = $base_url.'&amp;action=edit&amp;id='.$id;
		$back_url = $base_url;

		$form = $this->request_formular(false,false);

		// group combos
		$groups = array();
		$maingroups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
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
		$tpl->add_variables(array(
			'action_type' => BS_ACP_ACTION_USER_EDIT,
			'groups' => $groups,
			'maingroups' => $maingroups,
			'is_own_user' => $id == $user->get_user_id(),
			'main_group' => (int)$data['user_group'],
			'other_groups' => $ogroupdb
		));

		// avatar
		$avatar = '';
		$rowspan = BS_ENABLE_EXPORT ? (count($fields) + 1) : 5;
		$avatar = '<td width="35%" align="center" class="a_main" rowspan="'.$rowspan.'">';
		$av = BS_UserUtils::get_instance()->get_profile_avatar($data['avatar'],$data['id']);
		$avatar .= $av;
		if($av != $locale->lang('nopictureavailable'))
		{
			$avatar .= '<br />'.$locale->lang('delete').': ';
			$avatar .= $form->get_radio_yesno('remove_avatar',0);
		}
		$avatar .= '</td>'."\n";

		$tpl->add_variables(array(
			'avatar' => $avatar,
			'avatar_export' => BS_ENABLE_EXPORT ? $avatar : '',
			'target_url' => $murl,
			'bbcode_mode' => $data['bbcode_mode'],
			'not_export' => !BS_ENABLE_EXPORT,
			'user_name' => $data['user_name'],
			'user_email' => $data['user_email'],
			'show_avatar' => BS_ENABLE_EXPORT,
			'av_rowspan' => count($fields) + 1,
		));

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
		$pform = new BS_PostingForm($locale->lang('signature'),$data['signature_posted'],'sig');
		$pform->set_textarea_height('100px');
		$pform->add_form();
		
		// set colspan for the post-form-template
		$tpl->set_template('inc_post_form.htm');
		$tpl->add_variables(array(
			'colspan_main' => 2
		));
		$tpl->restore_template();

		// some other stuff
		$tpl->add_variables(array(
			'addfields' => $tplfields,
			'signature' => $form->get_input_value('text',$data['signature_posted']),
			'base_url' => $back_url
		));
	}
}
?>