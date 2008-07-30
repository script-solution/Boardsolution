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
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_DELETE_SUBSCRIPTIONS,'delete');
		$renderer->add_breadcrumb($locale->lang('acpmod_subscriptions'),$url->get_acpmod_url());
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$functions = PLIB_Props::get()->functions();
		$locale = PLIB_Props::get()->locale();
		$tpl = PLIB_Props::get()->tpl();
		$url = PLIB_Props::get()->url();

		$end = 15;
		$search = $input->get_var('search','get',PLIB_Input::STRING);
		if($search != '')
			$num = BS_DAO::get_subscr()->get_count_by_keyword($search);
		else
			$num = BS_DAO::get_subscr()->get_count();
		$pagination = new BS_ACP_Pagination($end,$num);
		$site = $pagination->get_page();
		
		$order_vals = array('username','date','lastlogin','lastpost');
		$order = $input->correct_var('order','get',PLIB_Input::STRING,$order_vals,'date');
		$ad = $input->correct_var('ad','get',PLIB_Input::STRING,array('ASC','DESC'),'DESC');

		// display delete-message?
		$delete = $input->get_var('delete','post');
		if($delete != null && PLIB_Array_Utils::is_integer($delete))
		{
			$ids = PLIB_Array_Utils::advanced_implode(',',$delete);
			$def_params = '&amp;site='.$site.'&amp;order='.$order.'&amp;ad='.$ad;
			$yes_url = $url->get_acpmod_url(
				0,'&amp;at='.BS_ACP_ACTION_DELETE_SUBSCRIPTIONS.'&amp;ids='.$ids.$def_params
			);
			$no_url = $url->get_acpmod_url(0,$def_params);
			$functions->add_delete_message(
				$locale->lang('delete_subscriptions_question'),$yes_url,$no_url,''
			);
		}
	
		$base_url = $url->get_acpmod_url(0,'&amp;search='.$search.'&amp;site='.$site.'&amp;');
		$tpl->add_variables(array(
			'target_url' => $base_url.'order='.$order.'&amp;ad='.$ad,
			'username_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('username'),'username','ASC',$order,$base_url
			),
			'date_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('date'),'date','DESC',$order,$base_url
			),
			'lastlogin_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('lastlogin'),'lastlogin','DESC',$order,$base_url
			),
			'lastpost_col' => BS_ACP_Utils::get_instance()->get_order_column(
				$locale->lang('lastpost'),'lastpost','DESC',$order,$base_url
			)
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
				$furl = $url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=topics&amp;'.BS_URL_FID.'='.$data['forum_id']
				);
				$info = BS_TopicUtils::get_instance()->get_displayed_name($data['forum_name'],22);
				$name = '[<b>F</b>] <a target="_blank" href="'.$furl.'"';
				$name .= ' title="'.$info['complete'].'">'.$info['displayed'].'</a>';
				if($data['flastpost_time'] == 0)
					$lastpost = $locale->lang('notavailable');
				else
					$lastpost = PLIB_Date::get_date($data['flastpost_time']);
			}
			else
			{
				$furl = $url->get_frontend_url(
					'&amp;'.BS_URL_ACTION.'=redirect&amp;'.BS_URL_LOC.'=show_topic&amp;'.BS_URL_TID.'='.$data['topic_id']
				);
				$info = BS_TopicUtils::get_instance()->get_displayed_name($data['name'],22);
				$name = '[<b>T</b>] <a target="_blank" href="'.$furl.'"';
				$name .= ' title="'.$info['complete'].'">'.$info['displayed'].'</a>';
				if($data['lastpost_time'] == 0)
					$lastpost = $locale->lang('notavailable');
				else
					$lastpost = PLIB_Date::get_date($data['lastpost_time']);
			}
			
			$subscriptions[] = array(
				'id' => $data['id'],
				'name' => $name,
				'username' => BS_ACP_Utils::get_instance()->get_userlink($data['user_id'],$data['user_name']),
				'subscription_date' => PLIB_Date::get_date($data['sub_date']),
				'lastlogin' => PLIB_Date::get_date($data['lastlogin']),
				'lastpost' => $lastpost
			);
		}

		$tpl->add_array('subscriptions',$subscriptions);
		
		$hidden = $input->get_vars_from_method('get');
		unset($hidden['site']);
		unset($hidden['search']);
		unset($hidden['at']);
		$tpl->add_variables(array(
			'search_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden' => $hidden,
			'search_val' => $search
		));

		$murl = $url->get_acpmod_url(
			0,'&amp;search='.$search.'&amp;order='.$order.'&amp;ad='.$ad.'&amp;site={d}'
		);
		$functions->add_pagination($pagination,$murl);
	}
}
?>