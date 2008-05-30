<?php
/**
 * Contains the default-submodule for errorlog
 * 
 * @version			$Id: sub_default.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The default sub-module for the errorlog-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_errorlog_default extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_ERRORLOGS => 'delete',
			BS_ACP_ACTION_DELETE_ALL_ERRORLOGS => 'deleteall'
		);
	}
	
	public function run()
	{
		$order = $this->input->correct_var(BS_URL_ORDER,'get',PLIB_Input::STRING,
			array('error','date','user'),'date');
		$ad = $this->input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		$search = $this->input->get_var('search','get',PLIB_Input::STRING);
		$site = $this->input->get_var('site','get',PLIB_Input::INTEGER);
		
		$params = '&amp;order='.$order.'&amp;ad='.$ad.'&amp;search='.$search.'&amp;site='.$site;
		
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$names = array();
			foreach(BS_DAO::get_logerrors()->get_by_ids($ids) as $data)
				$names[] = $data['message'];
			$namelist = PLIB_StringHelper::get_enum($names,$this->locale->lang('and'));
			
			$this->functions->add_delete_message(
				sprintf($this->locale->lang('delete_message'),$namelist),
				$this->url->get_acpmod_url(
					0,'&amp;at='.BS_ACP_ACTION_DELETE_ERRORLOGS.$params.'&amp;ids='.implode(',',$ids)
				),
				$this->url->get_acpmod_url(0,$params)
			);
		}
		else if($this->input->get_var('ask','get',PLIB_Input::STRING) == 'deleteall')
		{
			$this->functions->add_delete_message(
				$this->locale->lang('delete_all_question'),
				$this->url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_DELETE_ALL_ERRORLOGS),
				$this->url->get_acpmod_url(0,$params)
			);
		}
		
		$hidden_fields = '';
		foreach($this->input->get_vars_from_method('get') as $key => $value)
			$hidden_fields .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
		
		if($search != '')
			$num = BS_DAO::get_logerrors()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_logerrors()->get_count();
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$url = $this->url->get_acpmod_url(0,'&amp;search='.$search.'&amp;');
		$this->tpl->add_variables(array(
			'form_url' => $this->url->get_acpmod_url(0,$params),
			'col_error' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('error_msg'),'error','ASC',$order,$url
			),
			'col_date' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('date'),'date','DESC',$order,$url
			),
			'col_user' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('username'),'user','ASC',$order,$url
			),
			'search_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'search_val' => $search,
			'delete_all_url' => $this->url->get_acpmod_url(0,$params.'&amp;ask=deleteall')
		));
		
		switch($order)
		{
			case 'error':
				$sql_order = 'l.message';
				break;
			case 'date';
				$sql_order = 'l.id';
				break;
			case 'user':
				$sql_order = 'u.`'.BS_EXPORT_USER_NAME.'`';
				break;
		}
		
		$logs = array();
		if($search != '')
		{
			$loglist = BS_DAO::get_logerrors()->get_all_by_keyword(
				$search,$sql_order,$ad,$pagination->get_start(),$end
			);
		}
		else
			$loglist = BS_DAO::get_logerrors()->get_all($sql_order,$ad,$pagination->get_start(),$end);
		
		foreach($loglist as $i => $data)
		{
			if($data['user_id'] > 0)
				$user = BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']);
			else
				$user = '<i>'.$this->locale->lang('guest').'</i>';
			
			$backtrace = '<ul>';
			foreach(explode("\n",$data['backtrace']) as $call)
			{
				if(trim($call) != '')
					$backtrace .= '	<li>'.$call.'</li>'."\n";
			}
			$backtrace .= '</ul>';
			
			$logs[] = array(
				'user' => $user,
				'id' => $data['id'],
				'date' => PLIB_Date::get_date($data['date']),
				'query' => htmlspecialchars($data['query'],ENT_QUOTES),
				'error_msg' => $data['message'],
				'backtrace' => $backtrace
			);
		}
		
		$this->tpl->add_array('logs',$logs);
		$this->tpl->add_variables(array(
			'count' => $i
		));
		
		$url = $this->url->get_acpmod_url(
			0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;search='.$search.'&amp;site={d}'
		);
		$this->functions->add_pagination($pagination,$url);
	}
	
	public function get_location()
	{
		return array();
	}
}
?>