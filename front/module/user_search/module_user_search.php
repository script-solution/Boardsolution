<?php
/**
 * Contains the module for the user-search in the frontend
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the popup with the front-end-user-search
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_user_search extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->set_template('popup_user_search.htm');
		$renderer->set_show_headline(false);
		$renderer->set_show_bottom(false);
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$cfg = PLIB_Props::get()->cfg();
		$auth = PLIB_Props::get()->auth();
		$input = PLIB_Props::get()->input();
		$tpl = PLIB_Props::get()->tpl();
		$locale = PLIB_Props::get()->locale();
		$functions = PLIB_Props::get()->functions();
		$url = PLIB_Props::get()->url();

		// has the user the permission to view the user-search?
		if($cfg['enable_memberlist'] == 0 || !$auth->has_global_permission('view_memberlist'))
		{
			$this->report_error();
			return;
		}
		
		$hidden_fields = array();
		if(($sid = $url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$url->get_extern_vars());
		
		$name = $input->get_var(BS_URL_MS_NAME,'get',PLIB_Input::STRING);
		$email = $input->get_var(BS_URL_MS_EMAIL,'get',PLIB_Input::STRING);
		
		$limit = $cfg['members_per_page'];
		$num = BS_DAO::get_user()->get_search_user_count($name,$email);
		$pagination = new BS_Pagination($limit,$num);
		
		$tpl->add_variables(array(
			'num' => $num,
			'charset' => BS_HTML_CHARSET,
			'result_title' => sprintf($locale->lang('user_search_result'),$num),
			'search_target' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'action_param' => BS_URL_ACTION,
			'action_value' => $input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING),
			'name_param' => BS_URL_MS_NAME,
			'name_value' => $name,
			'email_param' => BS_URL_MS_EMAIL,
			'email_value' => $email
		));
		
		$user_list = array();
		$users = BS_DAO::get_profile()->get_users_by_search(
			$name,$email,0,array(),'user_name','ASC',$pagination->get_start(),$limit
		);
		foreach($users as $data)
		{
			$user_list[] = array(
				'user_name' => $data['user_name'],
				'email' => BS_UserUtils::get_instance()->get_displayed_email(
					$data['user_email'],$data['email_display_mode']
				),
				'user_group' => $auth->get_usergroup_list($data['user_group'],false,false),
			);
		}
		
		$tpl->add_array('user_list',$user_list);
		
		$murl = $url->get_url(0,'&amp;'.BS_URL_MS_NAME.'='.$name.'&amp;'
			.BS_URL_MS_EMAIL.'='.$email.'&amp;'.BS_URL_SITE.'={d}');
		$functions->add_pagination($pagination,$murl);
	}
}
?>