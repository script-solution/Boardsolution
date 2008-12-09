<?php
/**
 * Contains the search-submodule for user
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The search sub-module for the user-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_user_search extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$url = BS_URL::get_acpsub_url();
		$url->set('use_sess',1);
		$renderer->add_breadcrumb($locale->lang('search'),$url->to_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$cfg = FWS_Props::get()->cfg();

		// search?
		if($input->isset_var('submit','post'))
			$this->_perform_search();
		
		// init the search
		$use_sess = $input->get_var('use_sess','get',FWS_Input::INT_BOOL);
		if($use_sess)
			$sp = $user->get_session_data('user_search_params');
		else
		{
			// delete potential old session-data
			$user->delete_session_data('user_search_ids');
			$user->delete_session_data('user_search_params');
			
			$sp = $this->_get_search_params();
		}
		
		// collect usergroups
		$groups = $cache->get_cache('user_groups');
		$user_group_options = array();
		$selected_groups = array();
		foreach($groups as $data)
		{
			if($data['id'] != BS_STATUS_GUEST)
			{
				if($sp['group'] == null || in_array($data['id'],$sp['group']))
					$selected_groups[] = $data['id'];

				$user_group_options[$data['id']] = $data['group_title'];
			}
		}
		
		$form = new BS_HTML_Formular(false,false);
		$form->set_condition($input->isset_var('submit','post'));
		
		$tpl->add_variables(array(
			'wait_image' => $user->get_theme_item_path('images/wait.gif'),
			'search_target' => BS_URL::build_acpsub_url(),
			'name_value' => stripslashes($sp['name']),
			'action_param' => BS_URL_ACTION,
			'email_value' => stripslashes($sp['email']),
			'signature_value' => stripslashes($sp['signature']),
			'user_group_combo' => $form->get_combobox(
				'user_group[]',$user_group_options,$selected_groups,true,count($user_group_options)
			),
		));
		
		$tpl->add_variables(array(
			'posts_control' => $this->_get_int_control(
				'from_posts','to_posts',$sp['from_posts'],$sp['to_posts']
			),
			'points_control' => $this->_get_int_control(
				'from_pts','to_pts',$sp['from_points'],$sp['to_points']
			),
			'reg_control' => $this->_get_date_control(
				$form,'from_reg','to_reg',$sp['from_reg'],$sp['to_reg']
			),
			'lastlogin_control' => $this->_get_date_control(
				$form,'from_lastlogin','to_lastlogin',$sp['from_lastlogin'],$sp['to_lastlogin']
			),
			'enable_post_count' => $cfg['enable_post_count'] == 1,
			'reset_url' => BS_URL::build_acpsub_url()
		));
		
		// add additional fields
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(
			BS_UF_LOC_POSTS | BS_UF_LOC_REGISTRATION | BS_UF_LOC_USER_DETAILS | BS_UF_LOC_USER_PROFILE
		);
		$tplfields = array();
		foreach($fields as $field)
		{
			$data = $field->get_data();
			$field_name = $data->get_name();
			
			switch($data->get_type())
			{
				case 'int':
					if(!$use_sess)
					{
						$from = $input->get_var('add_from_'.$field_name,'post',FWS_Input::INTEGER);
						$to = $input->get_var('add_to_'.$field_name,'post',FWS_Input::INTEGER);
					}
					else
					{
						$from = $sp['add_from_'.$field_name];
						$to = $sp['add_to_'.$field_name];
					}
					
					$control = $this->_get_int_control('add_from_'.$field_name,'add_to_'.$field_name,$from,$to);
					break;
				
				case 'text':
				case 'line':
					if(!$use_sess)
						$value = $field->get_value_from_formular();
					else
						$value = $sp['add_'.$field_name];
					
					//$value = $field->get_value_from_formular();
					$control = $this->_get_string_control('add_'.$field_name,$value);
					break;
				
				case 'date':
					if(!$use_sess)
					{
						$from = $input->get_var('add_from_'.$field_name,'post',FWS_Input::INTEGER);
						$to = $input->get_var('add_to_'.$field_name,'post',FWS_Input::INTEGER);
					}
					else
					{
						$from = $sp['add_from_'.$field_name];
						$to = $sp['add_to_'.$field_name];
					}
					
					$control = $this->_get_date_control(
						$form,'add_from_'.$field_name,'add_to_'.$field_name,$from,$to
					);
					break;
				
				case 'enum':
					if(!$use_sess)
						$value = $input->get_var('add_'.$field_name,'post');
					else
						$value = $sp['add_'.$field_name];
					
					$options = $data->get_values();
					$control = $form->get_combobox(
						'add_'.$field_name.'[]',$options,$value,true,count($options)
					);
					break;
			}

			$tplfields[] = array(
				'name' => $field->get_title(),
				'value' => $control
			);
		}
		
		$tpl->add_variable_ref('addfields',$tplfields);
	}
	
	/**
	 * Builds the sql-query and displays the search-result
	 */
	private function _perform_search()
	{
		$cache = FWS_Props::get()->cache();
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		$doc = FWS_Props::get()->doc();
		$msgs = FWS_Props::get()->msgs();
		$locale = FWS_Props::get()->locale();

		$search_params = $this->_get_search_params();
		
		// builds where-clause
		$where = ' WHERE p.active = 1 AND p.banned = 0';
		if($search_params['name'])
		{
			$keyword = str_replace('*','%',$search_params['name']);
			$where .= ' AND u.`'.BS_EXPORT_USER_NAME."` LIKE '%".$keyword."%'";
		}

		if($search_params['email'])
		{
			$keyword = str_replace('*','%',$search_params['email']);
			$where .= ' AND u.`';
			$where .= BS_EXPORT_USER_EMAIL."` LIKE '%".$keyword."%'";
		}
		
		if($search_params['signature'])
		{
			$keyword = str_replace('*','%',$search_params['signature']);
			$where .= ' AND p.signature_posted LIKE "%'.$keyword.'%"';
		}

		if($search_params['group'] != null && FWS_Array_Utils::is_integer($search_params['group']))
		{
			$where .= ' AND (';
			$groups = $cache->get_cache('user_groups');
			foreach($search_params['group'] as $gid)
			{
				// check if the group is allowed
				$gdata = $groups->get_element($gid);
				if($gdata !== null && $gid != BS_STATUS_GUEST)
					$where .= 'FIND_IN_SET('.$gid.',p.user_group) > 0 OR ';
			}
			$where = FWS_String::substr($where,0,FWS_String::strlen($where) - 4);
			$where .= ')';
		}
		// in this case we don't want to find any user
		else if($search_params['group'] != null)
			$where .= ' AND p.user_group = -1';

		$where .= FWS_StringHelper::build_int_range_sql('p.posts',$search_params['from_posts'],
			$search_params['to_posts']);
		$where .= FWS_StringHelper::build_int_range_sql('p.exppoints',$search_params['from_points'],
			$search_params['to_points']);

		$where .= FWS_StringHelper::build_date_range_sql('p.registerdate',$search_params['from_reg'],
			$search_params['to_reg']);
		$where .= FWS_StringHelper::build_date_range_sql('p.lastlogin',$search_params['from_lastlogin'],
			$search_params['to_lastlogin']);
		
		$cfields = BS_AddField_Manager::get_instance();
		$fields = $cfields->get_fields_at(
			BS_UF_LOC_POSTS | BS_UF_LOC_REGISTRATION | BS_UF_LOC_USER_DETAILS | BS_UF_LOC_USER_PROFILE
		);
		foreach($fields as $field)
		{
			$data = $field->get_data();
			$field_name = $data->get_name();
			
			switch($data->get_type())
			{
				case 'int':
					$from = $input->get_var('add_from_'.$field_name,'post',FWS_Input::INTEGER);
					$to = $input->get_var('add_to_'.$field_name,'post',FWS_Input::INTEGER);
					$where .= FWS_StringHelper::build_int_range_sql('p.add_'.$field_name,$from,$to);
					
					$search_params['add_from_'.$field_name] = $from;
					$search_params['add_to_'.$field_name] = $to;
					break;
				
				case 'text':
				case 'line':
					$value = $field->get_value_from_formular();
					$where .= ' AND p.`add_'.$field_name.'` LIKE "%'.str_replace('*','%',$value).'%"';
					
					$search_params['add_'.$field_name] = $value;
					break;
				
				case 'date':
					$from = $input->get_var('add_from_'.$field_name,'post',FWS_Input::STRING);
					$to = $input->get_var('add_to_'.$field_name,'post',FWS_Input::STRING);
					$where .= FWS_StringHelper::build_date_range_sql('p.add_'.$field_name,$from,$to);
					
					$search_params['add_from_'.$field_name] = $from;
					$search_params['add_to_'.$field_name] = $to;
					break;
				
				case 'enum':
					$selected = $input->get_var('add_'.$field_name,'post');
					if(is_array($selected) && FWS_Array_Utils::is_integer($selected) && count($selected) > 0)
						$where .= ' AND p.`add_'.$field_name.'` IN ('.implode(',',$selected).')';
					$search_params['add_'.$field_name] = $selected;
					break;
			}
		}
		
		$user_ids = array();
		foreach(BS_DAO::get_profile()->get_users_by_custom_search($where) as $data)
			$user_ids[] = $data['id'];
		
		// have we found some user?
		if(count($user_ids) > 0)
		{
			// ok, store them to the session and redirect to the results-page
			$user->set_session_data('user_search_params',$search_params);
			$user->set_session_data('user_search_ids',$user_ids);
			$doc->redirect(BS_URL::get_acpsub_url(0,'default'));
		}
		// show the search-form again, if we have found 0 user
		else
			$msgs->add_error($locale->lang('no_user_found'));
	}
	
	/**
	 * Reads the search-params from post and returns them
	 * 
	 * @return array an associative array with all search-params
	 */
	private function _get_search_params()
	{
		$input = FWS_Props::get()->input();

		$from_reg = $input->get_var('from_reg','post',FWS_Input::STRING);
		$to_reg = $input->get_var('to_reg','post',FWS_Input::STRING);
		$from_lastlogin = $input->get_var('from_lastlogin','post',FWS_Input::STRING);
		$to_lastlogin = $input->get_var('to_lastlogin','post',FWS_Input::STRING);
		
		return array(
			'name' => $input->get_var('user_name','post',FWS_Input::STRING),
			'email' => $input->get_var('user_email','post',FWS_Input::STRING),
			'group' => $input->get_var('user_group','post'),
			'from_posts' => $input->get_var('from_posts','post',FWS_Input::INTEGER),
			'to_posts' => $input->get_var('to_posts','post',FWS_Input::INTEGER),
			'from_points' => $input->get_var('from_pts','post',FWS_Input::INTEGER),
			'to_points' => $input->get_var('to_pts','post',FWS_Input::INTEGER),
			'from_reg' => FWS_StringHelper::get_clean_date($from_reg),
			'to_reg' => FWS_StringHelper::get_clean_date($to_reg),
			'from_lastlogin' => FWS_StringHelper::get_clean_date($from_lastlogin),
			'to_lastlogin' => FWS_StringHelper::get_clean_date($to_lastlogin),
			'signature' => $input->get_var('signature','post',FWS_Input::STRING)
		);
	}
	
	/**
	 * Builds a date-control and returns it
	 * 
	 * @param FWS_HTML_Formular $form the html-formular
	 * @param string $from_param the name of the from-input-box
	 * @param string $to_param the name of the to-input-box
	 * @param string $from_val the value of the from-input-box
	 * @param string $to_val the value of the to-input-box
	 * @return string the html-code
	 */
	private function _get_date_control($form,$from_param,$to_param,$from_val,$to_val)
	{
		$locale = FWS_Props::get()->locale();

		$html = $locale->lang('between').' ';
		$html .= $form->get_date_chooser_textbox($from_param,$from_val);
		$html .= ' '.$locale->lang('and').' ';
		$html .= $form->get_date_chooser_textbox($to_param,$to_val);
		return $html;
	}
	
	/**
	 * Builds an integer-control and returns it
	 * 
	 * @param string $from_param the name of the from-input-box
	 * @param string $to_param the name of the to-input-box
	 * @param string $from_val the value of the from-input-box
	 * @param string $to_val the value of the to-input-box
	 * @return string the html-code
	 */
	private function _get_int_control($from_param,$to_param,$from_val,$to_val)
	{
		$locale = FWS_Props::get()->locale();

		$html = $locale->lang('From').' <input type="text" name="'.$from_param.'" size="10"';
		$html .= ' value="'.$from_val.'" />'."\n";
		$html .= $locale->lang('to').' <input type="text" name="'.$to_param.'" size="10"';
		$html .= ' value="'.$to_val.'" />'."\n";
		return $html;
	}
	
	/**
	 * Builds a string-control and returns it
	 * 
	 * @param string $param the name of the input-box
	 * @param string $val the value of the input-box
	 * @return string the html-code
	 */
	private function _get_string_control($param,$val)
	{
		return '<input type="text" name="'.$param.'" size="30" maxlength="30"'
					.' value="'.$val.'" />'."\n";
	}
}
?>