<?php
/**
 * Contains the subscriptions module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The subscriptions-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_subscriptions extends BS_ACP_Module
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
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_SUBSCRIPTIONS,'delete');
		$renderer->add_breadcrumb($locale->lang('acpmod_subscriptions'),BS_URL::build_acpmod_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$functions = FWS_Props::get()->functions();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$end = 15;
		$search = $input->get_var('search','get',FWS_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_subscr()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_subscr()->get_count();
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();
		
		$order_vals = array('username','date','lastlogin','lastpost');
		$order = $input->correct_var('order','get',FWS_Input::STRING,$order_vals,'date');
		$ad = $input->correct_var('ad','get',FWS_Input::STRING,array('ASC','DESC'),'DESC');

		// display delete-message?
		$delete = $input->get_var('delete','post');
		if($delete != null && FWS_Array_Utils::is_integer($delete))
		{
			$ids = FWS_Array_Utils::advanced_implode(',',$delete);
			
			$url = BS_URL::get_acpmod_url();
			$url->set('site',$site);
			$url->set('order',$order);
			$url->set('ad',$ad);
			
			$yurl = clone $url;
			$yurl->set('at',BS_ACP_ACTION_DELETE_SUBSCRIPTIONS);
			$yurl->set('ids',$ids);
			
			$functions->add_delete_message(
				$locale->lang('delete_subscriptions_question'),$yurl->to_url(),$url->to_url(),''
			);
		}
	
		$baseurl = BS_URL::get_acpmod_url();
		$baseurl->set('search',$search);
		$baseurl->set('site',$site);
		
		$tpl->add_variables(array(
			'target_url' => $baseurl->set('order',$order)->set('ad',$ad)->to_url(),
			'username_col' => BS_ACP_Utils::get_order_column(
				$locale->lang('username'),'username','ASC',$order,$baseurl
			),
			'date_col' => BS_ACP_Utils::get_order_column(
				$locale->lang('date'),'date','DESC',$order,$baseurl
			),
			'lastlogin_col' => BS_ACP_Utils::get_order_column(
				$locale->lang('lastlogin'),'lastlogin','DESC',$order,$baseurl
			),
			'lastpost_col' => BS_ACP_Utils::get_order_column(
				$locale->lang('lastpost'),'lastpost','DESC',$order,$baseurl
			),
		));

		switch($order)
		{
			case 'username':
				$sql_order = 'user_name';
				break;
			case 'date':
				$sql_order = 's.sub_date';
				break;
			case 'lastlogin':
				$sql_order = 'p.lastlogin';
				break;
			default:
				$sql_order = 't.lastpost_time';
				break;
		}
		
		$subscriptions = array();
		$sublist = BS_DAO::get_subscr()->get_list($search,$sql_order,$ad,$pagination->get_start(),$end);
		foreach($sublist as $data)
		{
			if($data['forum_id'] > 0)
			{
				$furl = BS_URL::get_frontend_url('topics');
				$furl->set(BS_URL_FID,$data['forum_id']);
				list($infod,$infoc) = BS_TopicUtils::get_displayed_name($data['forum_name'],22);
				$name = '[<b>F</b>] <a target="_blank" href="'.$furl->to_url().'"';
				$name .= ' title="'.$infoc.'">'.$infod.'</a>';
				if($data['flastpost_time'] == 0)
					$lastpost = $locale->lang('notavailable');
				else
					$lastpost = FWS_Date::get_date($data['flastpost_time']);
			}
			else
			{
				$furl = BS_URL::get_frontend_url('redirect');
				$furl->set(BS_URL_LOC,'show_topic');
				$furl->set(BS_URL_TID,$data['topic_id']);
				list($infod,$infoc) = BS_TopicUtils::get_displayed_name($data['name'],22);
				$name = '[<b>T</b>] <a target="_blank" href="'.$furl->to_url().'"';
				$name .= ' title="'.$infoc.'">'.$infod.'</a>';
				if($data['lastpost_time'] == 0)
					$lastpost = $locale->lang('notavailable');
				else
					$lastpost = FWS_Date::get_date($data['lastpost_time']);
			}
			
			$subscriptions[] = array(
				'id' => $data['id'],
				'name' => $name,
				'username' => BS_ACP_Utils::get_userlink($data['user_id'],$data['user_name']),
				'subscription_date' => FWS_Date::get_date($data['sub_date']),
				'lastlogin' => FWS_Date::get_date($data['lastlogin']),
				'lastpost' => $lastpost
			);
		}

		$tpl->add_variable_ref('subscriptions',$subscriptions);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'search_url' => 'admin.php',
			'hidden' => $hidden,
			'search_val' => $search
		));

		$murl = BS_URL::get_acpmod_url();
		$murl->set('search',$search);
		$murl->set('order',$order);
		$murl->set('ad',$ad);
		$pagination->populate_tpl($murl);
	}
}
?>