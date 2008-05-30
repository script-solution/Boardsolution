<?php
/**
 * Contains the standalone-class for the user-search in the frontend
 * 
 * @version			$Id: user_search.php 713 2008-05-20 21:59:54Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the popup with the front-end-user-search
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_user_search extends BS_Standalone
{
	public function get_template()
	{
		return 'popup_user_search.htm';
	}
	
	public function run()
	{
		// has the user the permission to view the user-search?
		if($this->cfg['enable_memberlist'] == 0 || !$this->auth->has_global_permission('view_memberlist'))
		{
			$this->_report_error();
			return;
		}
		
		$hidden_fields = array();
		if(($sid = $this->url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$this->url->get_extern_vars());
		
		$name = $this->input->get_var(BS_URL_MS_NAME,'get',PLIB_Input::STRING);
		$email = $this->input->get_var(BS_URL_MS_EMAIL,'get',PLIB_Input::STRING);
		
		$limit = $this->cfg['members_per_page'];
		$num = BS_DAO::get_user()->get_search_user_count($name,$email);
		$pagination = new BS_Pagination($limit,$num);
		
		$this->tpl->add_variables(array(
			'num' => $num,
			'charset' => BS_HTML_CHARSET,
			'result_title' => sprintf($this->locale->lang('user_search_result'),$num),
			'search_target' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'action_param' => BS_URL_ACTION,
			'action_value' => $this->input->get_var(BS_URL_ACTION,'get',PLIB_Input::STRING),
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
				'user_group' => $this->auth->get_usergroup_list($data['user_group'],false,false),
			);
		}
		
		$this->tpl->add_array('user_list',$user_list);
		
		$url = $this->url->get_url(0,'&amp;'.BS_URL_MS_NAME.'='.$name.'&amp;'
			.BS_URL_MS_EMAIL.'='.$email.'&amp;'.BS_URL_SITE.'={d}');
		$this->functions->add_pagination($pagination,$url);
	}
}
?>