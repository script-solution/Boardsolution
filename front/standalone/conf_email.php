<?php
/**
 * Contains the standalone-class for the confirmation of email-address-changes
 * 
 * @version			$Id: conf_email.php 773 2008-05-25 16:13:44Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The page to confirm an email-address-change
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_conf_email extends BS_Standalone
{
	/**
	 * Constructor
	 * 
	 * @param string $path the path
	 */
	public function BS_Front_Standalone_conf_email($path)
	{
		BS_Standalone::BS_Standalone($path,true,array(
			'sql_helper','sess','unread','forums','html'
		));
	}
	
	public function run()
	{
		// check parametes
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::ID);
		$key = $this->input->get_var(BS_URL_PID,'get',PLIB_Input::STRING);
		
		if($id == null || $key == null)
			die($this->locale->lang('invalid_page'));
		
		$message = $this->locale->lang('email_change_failed');
		$success = false;
		$url = $this->url->get_frontend_url();
		
		$data = BS_DAO::get_changeemail()->get_by_user($id,$key);
		if($data !== false)
		{
			$this->db->start_transaction();
			
			BS_DAO::get_user()->update($id,'',$data['email_address']);
			BS_DAO::get_changeemail()->delete_by_user($id);
			
			$this->db->commit_transaction();
		
			$message = sprintf(
				$this->locale->lang('email_change_success'),
				'<a href="'.$url.'">'.$this->locale->lang('here').'</a>'
			);
			$success = true;
		}
		
		$this->tpl->set_template('extern_conf.htm',0);
		$this->tpl->add_variables(array(
			'message' => $message,
			'success' => $success,
			'redirect' => $url,
			'charset' => 'charset='.BS_HTML_CHARSET
		));
		echo $this->tpl->parse_template();
	}
	
	public function require_board_access()
	{
		return false;
	}
}
?>