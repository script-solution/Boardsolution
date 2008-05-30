<?php
/**
 * Contains the standalone-class for the activation
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * This class activates a user
 * 
 * @package			Boardsolution
 * @subpackage	front.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Standalone_activate extends BS_Standalone
{
	public function get_template()
	{
		return 'extern_conf.htm';
	}
	
	public function run()
	{
		// check parametes
		$id = $this->input->get_var('user_id','get',PLIB_Input::ID);
		$key = $this->input->get_var('user_key','get',PLIB_Input::STRING);
		
		if($id == null || $key == null)
			die($this->locale->lang('invalid_page'));
		
		$message = $this->locale->lang('activate_failed');
		$success = false;
		$url = $this->url->get_frontend_url();
		if(BS_DAO::get_activation()->exists($id,$key))
		{
			$this->db->start_transaction();
			
			BS_DAO::get_profile()->update_user_by_id(array('active' => 1),$id);
			BS_DAO::get_activation()->delete($id,$key);
			
			$this->db->commit_transaction();
			
			$message = sprintf(
				$this->locale->lang('activate_success'),
				'<a href="'.$url.'">'.$this->locale->lang('here').'</a>'
			);
			$success = true;
		}
		
		$this->tpl->add_variables(array(
			'message' => $message,
			'success' => $success,
			'redirect' => $url,
			'charset' => 'charset='.BS_HTML_CHARSET
		));
	}
	
	public function require_board_access()
	{
		return false;
	}
}
?>