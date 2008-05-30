<?php
/**
 * Contains the standalone-class for user-details
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * Displays the user-details-page for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.standalone
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Standalone_user_details extends BS_Standalone
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// we have to change some things for the ACP
		$this->locale->add_language_file('admin');		
		$this->tpl->set_path(PLIB_Path::inner().'acp/templates/');
	}
	
	public function run()
	{
		if(!$this->auth->has_acp_access())
			return;
		
		$this->tpl->set_template('inc_header.htm');
		$this->tpl->add_variables(array(
			'charset' => 'charset='.BS_HTML_CHARSET,
			'cookie_path' => $this->cfg['cookie_path'],
			'cookie_domain' => $this->cfg['cookie_domain']
		));
		$this->tpl->restore_template();
		
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id == null)
			return;
		
		$this->tpl->set_template('popup_userdetails.htm');
		
		$data = BS_DAO::get_profile()->get_user_by_id($id,-1,-1);
		if($data === false)
			return;
		
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
		
		$this->tpl->add_array('data',$data,false);
		$this->tpl->add_variables(array(
			'user_groups' => $this->auth->get_usergroup_list($data['user_group'],false,true,true),
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
				
				$field_value = $this->locale->lang('notavailable');
			}
			else
				$field_value = $field->get_display($val,'a_main','a_main');
		
			$addfields[] = array(
				'name' => $field->get_title(),
				'value' => $field_value
			);
		}
		
		$this->tpl->add_array('addfields',$addfields);
		
		// generate signature
		if($data['signatur'] != '')
		{
			$enable_bbcode = BS_PostingUtils::get_instance()->get_message_option('enable_bbcode','sig');
			$enable_smileys = BS_PostingUtils::get_instance()->get_message_option('enable_smileys','sig');
			$bbcode = new BS_BBCode_Parser($data['signatur'],'sig',$enable_bbcode,$enable_smileys);
			$signature = $bbcode->get_message_for_output();
		}
		else
			$signature = $this->locale->lang('notavailable');
		
		$rank_data = $this->functions->get_rank_data($data['exppoints']);
		
		$this->tpl->add_array('data',$data,false);
		$this->tpl->add_variables(array(
			'signature' => $signature,
			'rank' => $rank_data['rank']
		));
		
		$this->tpl->set_template('inc_footer.htm');
		$this->tpl->add_variables(array(
			'render_time' => $this->doc->get_script_time(),
			'db_queries' => $this->db->get_performed_query_num(),
			'queries' => PLIB_PrintUtils::to_string($this->db->get_performed_queries())
		));
		$this->tpl->restore_template();
		
		echo $this->tpl->parse_template();
	}
}
?>