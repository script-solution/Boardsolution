<?php
/**
 * Contains the ip-log module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The ip-log-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_iplog extends BS_ACP_Module
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_DELETE_IPLOGS => 'delete',
			BS_ACP_ACTION_DELETE_ALL_IPLOGS => 'deleteall'
		);
	}
	
	public function run()
	{
		$order = $this->input->correct_var(BS_URL_ORDER,'get',PLIB_Input::STRING,
			array('action','date','user','ip','agent'),'date');
		$ad = $this->input->correct_var(BS_URL_AD,'get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');
		
		$keyword = $this->input->get_var('keyword','get',PLIB_Input::STRING);
		$date_from = $this->input->get_var('date_from','get',PLIB_Input::STRING);
		$date_to = $this->input->get_var('date_to','get',PLIB_Input::STRING);
		$action = $this->input->get_var('ipaction','get',PLIB_Input::STRING);
		
		$where = '';
		if($keyword)
		{
			$where .= ' AND (u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$keyword.'%"';
			$where .= ' OR l.user_ip LIKE "%'.$keyword.'%"';
			$where .= ' OR l.user_agent LIKE "%'.$keyword.'%")';
		}
		if($date_from || $date_to)
			$where .= PLIB_StringHelper::build_date_range_sql('l.date',$date_from,$date_to);
		if($action)
		{
			$action_qry = '';
			switch($action)
			{
				case 'linkre':
				case 'adl':
					$action_qry = ' LIKE "'.$action.'%"';
					break;
				
				default:
					$action_qry = ' = "'.$action.'"';
					break;
			}
			$where .= ' AND l.action'.$action_qry;
		}
		if($where)
			$where = ' WHERE '.PLIB_String::substr($where,4);
		
		$num = BS_DAO::get_logips()->get_count_by_search($where);
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$this->_request_formular();
		
		$splitted = $this->url->get_acpmod_comps();
		$hidden_fields = '';
		foreach($splitted as $key => $val)
			$hidden_fields .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
		$hidden_fields .= '<input type="hidden" name="order" value="'.$order.'" />'."\n";
		$hidden_fields .= '<input type="hidden" name="ad" value="'.$ad.'" />'."\n";
		
		$actions = array(
			0 => '- '.$this->locale->lang('all').' -',
			'login' => $this->locale->lang('action_login'),
			'post' => $this->locale->lang('action_post'),
			'topic' => $this->locale->lang('action_topic'),
			'pm' => $this->locale->lang('action_pm'),
			'reg' => $this->locale->lang('action_reg'),
			'mail' => $this->locale->lang('action_mail'),
			'linkadd' => $this->locale->lang('action_linkadd'),
			'linkre' => $this->locale->lang('action_linkre'),
			'search' => $this->locale->lang('action_search'),
			'adl' => $this->locale->lang('action_adl')
		);
		
		$url = $this->url->get_acpmod_url(
			0,'&amp;keyword='.$keyword.'&amp;ipaction='.$action.'&amp;date_from='
				.$date_from.'&amp;date_to='.$date_to.'&amp;'
		);
		
		$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
		if($this->input->isset_var('delete','post'))
		{
			$ids = $this->input->get_var('delete','post');
			$this->functions->add_delete_message(
				$this->locale->lang('delete_ip_logs'),
				$url.'&amp;at='.BS_ACP_ACTION_DELETE_IPLOGS.'&amp;order='.$order.'&amp;ad='
					.$ad.'&amp;ids='.implode(',',$ids).'&amp;site='.$site,
				$url.'order='.$order.'&amp;ad='.$ad.'&amp;site='.$site
			);
		}
		else if($this->input->get_var('ask','get',PLIB_Input::STRING) == 'deleteall')
		{
			$this->functions->add_delete_message(
				$this->locale->lang('delete_all_question'),
				$this->url->get_acpmod_url(0,'&amp;at='.BS_ACP_ACTION_DELETE_ALL_IPLOGS),
				$url.'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site='.$site
			);
		}
		
		$this->tpl->add_variables(array(
			'search_url' => $this->input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'keyword' => $keyword,
			'date_from' => $date_from,
			'date_to' => $date_to,
			'reset_url' => $this->url->get_acpmod_url(0,'&amp;order='.$order.'&amp;ad='.$ad),
			'action' => $action,
			'actions' => $actions,
			'form_url' => $url.'order='.$order.'&amp;ad='.$ad.'&amp;site='.$site,
			'col_action' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('action'),'action','ASC',$order,$url
			),
			'col_user_name' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('username'),'user','ASC',$order,$url
			),
			'col_user_ip' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('user_ip'),'ip','ASC',$order,$url
			),
			'col_user_agent' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('user_agent'),'agent','ASC',$order,$url
			),
			'col_date' => BS_ACP_Utils::get_instance()->get_order_column(
				$this->locale->lang('date'),'date','DESC',$order,$url
			),
			'num' => $num,
			'delete_all_url' => $url.'&amp;order='.$order.'&amp;ad='.$ad
				.'&amp;site='.$site.'&amp;action=delete_allq'
		));
		
		switch($order)
		{
			case 'user':
				$sql_order = 'user_name';
				break;
			case 'ip':
				$sql_order = 'l.user_ip';
				break;
			case 'agent':
				$sql_order = 'l.user_agent';
				break;
			case 'date':
				$sql_order = 'l.date';
				break;
			case 'action':
				$sql_order = 'l.action';
				break;
		}
		
		$logs = array();
		$loglist = BS_DAO::get_logips()->get_list_by_search(
			$where,$sql_order,$ad,$pagination->get_start(),$end
		);
		foreach($loglist as $data)
		{
			if($data['user_id'] > 0)
				$user = BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']);
			else
				$user = '<i>'.$this->locale->lang('guest').'</i>';
			
			if(PLIB_String::substr($data['action'],0,4) == 'adl_' ||
				PLIB_String::substr($data['action'],0,7) == 'linkre_')
				$data['action'] = strtok($data['action'],'_');
			
			$user_agent = PLIB_StringHelper::get_limited_string($data['user_agent'],25);
			
			$logs[] = array(
				'id' => $data['id'],
				'action' => $this->locale->lang('action_'.$data['action']),
				'user_name' => $user,
				'user_ip' => $data['user_ip'],
				'user_agent' => '<span title="'.$user_agent['complete'].'">'.$user_agent['displayed'].'</span>',
				'date' => PLIB_Date::get_date($data['date'])
			);
		}
		
		$this->tpl->add_variables(array(
			'count' => count($logs)
		));
		$this->tpl->add_array('logs',$logs);
		
		$url = $this->url->get_acpmod_url(
			0,'&amp;order='.$order.'&amp;ad='.$ad.'&amp;keyword='.$keyword.'&amp;ipaction='
				.$action.'&amp;date_from='.$date_from.'&amp;date_to='.$date_to.'&amp;site={d}'
		);
		$this->functions->add_pagination($pagination,$url);
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('acpmod_iplog') => $this->url->get_acpmod_url()
		);
	}
}
?>