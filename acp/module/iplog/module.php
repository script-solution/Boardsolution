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
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_IPLOGS,'delete');
		$renderer->add_action(BS_ACP_ACTION_DELETE_ALL_IPLOGS,'deleteall');
		$renderer->add_breadcrumb($locale->lang('acpmod_iplog'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$tpl = FWS_Props::get()->tpl();
		$order = $input->correct_var(BS_URL_ORDER,'get',FWS_Input::STRING,
			array('action','date','user','ip','agent'),'date');
		$ad = $input->correct_var(BS_URL_AD,'get',FWS_Input::STRING,array('ASC','DESC'),'DESC');
		
		$keyword = $input->get_var('keyword','get',FWS_Input::STRING);
		$date_from = $input->get_var('date_from','get',FWS_Input::STRING);
		$date_to = $input->get_var('date_to','get',FWS_Input::STRING);
		$action = $input->get_var('ipaction','get',FWS_Input::STRING);
		
		$where = '';
		if($keyword)
		{
			$where .= ' AND (u.`'.BS_EXPORT_USER_NAME.'` LIKE "%'.$keyword.'%"';
			$where .= ' OR l.user_ip LIKE "%'.$keyword.'%"';
			$where .= ' OR l.user_agent LIKE "%'.$keyword.'%")';
		}
		if($date_from || $date_to)
			$where .= FWS_StringHelper::build_date_range_sql('l.date',$date_from,$date_to);
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
			$where = ' WHERE '.FWS_String::substr($where,4);
		
		$num = BS_DAO::get_logips()->get_count_by_search($where);
		$end = 15;
		$pagination = new BS_ACP_Pagination($end,$num);
		
		$this->request_formular();
		
		$splitted = BS_URL::get_acpmod_comps();
		$hidden_fields = '';
		foreach($splitted as $key => $val)
			$hidden_fields .= '<input type="hidden" name="'.$key.'" value="'.$val.'" />'."\n";
		$hidden_fields .= '<input type="hidden" name="order" value="'.$order.'" />'."\n";
		$hidden_fields .= '<input type="hidden" name="ad" value="'.$ad.'" />'."\n";
		
		$actions = array(
			0 => '- '.$locale->lang('all').' -',
			'login' => $locale->lang('action_login'),
			'post' => $locale->lang('action_post'),
			'topic' => $locale->lang('action_topic'),
			'pm' => $locale->lang('action_pm'),
			'reg' => $locale->lang('action_reg'),
			'mail' => $locale->lang('action_mail'),
			'linkadd' => $locale->lang('action_linkadd'),
			'linkre' => $locale->lang('action_linkre'),
			'search' => $locale->lang('action_search'),
			'adl' => $locale->lang('action_adl')
		);
		
		$baseurl = BS_URL::get_acpmod_url();
		$baseurl->set('keyword',$keyword);
		$baseurl->set('ipaction',$action);
		$baseurl->set('date_from',$date_from);
		$baseurl->set('date_to',$date_to);
		
		$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
		$durl = clone $baseurl;
		$durl->set('order',$order);
		$durl->set('ad',$ad);
		$durl->set('site',$site);
		
		if($input->isset_var('delete','post'))
		{
			$ids = $input->get_var('delete','post');
			$yurl = clone $durl;
			$yurl->set('ids',implode(',',$ids));
			$yurl->set('at',BS_ACP_ACTION_DELETE_IPLOGS);
			
			$functions->add_delete_message($locale->lang('delete_ip_logs'),$yurl->to_url(),$durl->to_url());
		}
		else if($input->get_var('ask','get',FWS_Input::STRING) == 'deleteall')
		{
			$yurl = BS_URL::get_acpmod_url();
			$yurl->set('at',BS_ACP_ACTION_DELETE_ALL_IPLOGS);
			$functions->add_delete_message(
				$locale->lang('delete_all_question'),$yurl->to_url(),$durl->to_url()
			);
		}
		
		$tpl->add_variables(array(
			'search_url' => $input->get_var('PHP_SELF','server',FWS_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'keyword' => $keyword,
			'date_from' => $date_from,
			'date_to' => $date_to,
			'action' => $action,
			'actions' => $actions,
			'form_url' => $durl->to_url(),
			'col_action' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('action'),'action','ASC',$order,$baseurl
			),
			'col_user_name' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('username'),'user','ASC',$order,$baseurl
			),
			'col_user_ip' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('user_ip'),'ip','ASC',$order,$baseurl
			),
			'col_user_agent' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('user_agent'),'agent','ASC',$order,$baseurl
			),
			'col_date' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('date'),'date','DESC',$order,$baseurl
			),
			'num' => $num,
			'delete_all_url' => $durl->set('ask','delete_allq')->to_url(),
			'reset_url' => $durl->remove('ask')->set('site',1)->to_url(),
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
				$user = '<i>'.$locale->lang('guest').'</i>';
			
			if(FWS_String::substr($data['action'],0,4) == 'adl_' ||
				FWS_String::substr($data['action'],0,7) == 'linkre_')
				$data['action'] = strtok($data['action'],'_');
			
			$user_agent = FWS_StringHelper::get_limited_string($data['user_agent'],25);
			
			$logs[] = array(
				'id' => $data['id'],
				'action' => $locale->lang('action_'.$data['action']),
				'user_name' => $user,
				'user_ip' => $data['user_ip'],
				'user_agent' => '<span title="'.$user_agent['complete'].'">'.$user_agent['displayed'].'</span>',
				'date' => FWS_Date::get_date($data['date'])
			);
		}
		
		$tpl->add_variables(array(
			'count' => count($logs)
		));
		$tpl->add_array('logs',$logs);
		
		$pagination->populate_tpl($durl);
	}
}
?>