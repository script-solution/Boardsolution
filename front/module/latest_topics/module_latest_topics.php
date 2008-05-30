<?php
/**
 * Contains the latest-topics-module
 * 
 * @version			$Id: module_latest_topics.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The latest-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_latest_topics extends BS_Front_Module
{
	public function run()
	{
		$fid = $this->input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		$forum_combo = BS_ForumUtils::get_instance()->get_recursive_forum_combo(
			BS_URL_FID,$fid,0,false,true
		);
		
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = 'latest_topics';
		if(($sid = $this->url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$this->url->get_extern_vars());
		
		BS_Front_TopicFactory::get_instance()->add_latest_topics_full($fid);
		
		$this->tpl->add_variables(array(
			'target_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'forum_combo' => $forum_combo
		));
	}
	
	public function get_location()
	{
		$result = array($this->locale->lang('current_topics') => $this->url->get_url(0));
		return $result;
	}
	
	public function has_access()
	{
		return true;
	}
	
	public function get_robots_value()
	{
		return "index,follow";
	}
}
?>