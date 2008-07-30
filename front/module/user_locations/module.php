<?php
/**
 * Contains the user-locations-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The user-locations-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_user_locations extends BS_Front_Module
{
	/**
	 * The order-value
	 *
	 * @var string
	 */
	private $_order = 'date';
	
	/**
	 * The ASC-/DESC-value
	 *
	 * @var string
	 */
	private $_ad = 'DESC';
	
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->set_has_access($auth->has_global_permission('view_online_locations'));

		$renderer->add_breadcrumb($locale->lang('user_locations'),$url->get_url('user_locations'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$auth = FWS_Props::get()->auth();
		$sessions = FWS_Props::get()->sessions();
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();
		$functions = FWS_Props::get()->functions();
		$user = FWS_Props::get()->user();
		$cfg = FWS_Props::get()->cfg();
		$url = FWS_Props::get()->url();

		$order_vals = array('username','location','date','ip','useragent');
		$this->_order = $input->correct_var(
			BS_URL_ORDER,'get',FWS_Input::STRING,$order_vals,'date'
		);
		$this->_ad = $input->correct_var(
			BS_URL_AD,'get',FWS_Input::STRING,array('ASC','DESC'),'DESC'
		);
		$loc = $input->get_var(BS_URL_LOC,'get',FWS_Input::STRING);
		
		$baseurl = $url->get_url(0,'&amp;'.BS_URL_LOC.'='.$loc.'&amp;');
		
		$view_details = $auth->has_global_permission('view_user_online_detail');
		
		$locations = $sessions->get_user_at_location('all',-1,$loc != 'view_duplicates');
		usort($locations,array($this,'_location_sort_callback'));
		
		$url_params = '&amp;'.BS_URL_ORDER.'='.$this->_order.'&amp;'.BS_URL_AD.'='.$this->_ad;
		if($loc == 'view_duplicates')
		{
			$toggle_duplicate_title = $locale->lang('hide_duplicates');
			$toggle_duplicate_url = $url->get_url(0,$url_params);
		}
		else
		{
			$duplicate = $sessions->get_online_count() - count($locations);
			$toggle_duplicate_title = $locale->lang('view_duplicates').' ('.$duplicate.')';
			$toggle_duplicate_url = $url->get_url(0,$url_params.'&amp;'.BS_URL_LOC.'=view_duplicates');
		}
		
		$tpl->add_variables(array(
			'view_details' => $view_details,
			'toggle_duplicate_title' => $toggle_duplicate_title,
			'toggle_duplicate_url' => $toggle_duplicate_url,
			'colspan' => $view_details ? 5 : 3,
			'u_width' => $view_details ? 13 : 25,
			'l_width' => $view_details ? 35 : 50,
			'd_width' => $view_details ? 15 : 25,
			'col_username' => $functions->get_order_column(
				$locale->lang('username'),'username','ASC',$this->_order,$baseurl
			),
			'col_location' => $functions->get_order_column(
				$locale->lang('location'),'location','ASC',$this->_order,$baseurl
			),
			'col_date' => $functions->get_order_column(
				$locale->lang('date'),'date','DESC',$this->_order,$baseurl
			),
			'col_ip' => $functions->get_order_column(
				$locale->lang('user_ip'),'ip','ASC',$this->_order,$baseurl
			),
			'col_user_agent' => $functions->get_order_column(
				$locale->lang('user_agent'),'useragent','ASC',$this->_order,$baseurl
			)
		));
		
		$num = count($locations);
		$limit = 20;
		$pagination = new BS_Pagination($limit,$num);
		
		$user_list = array();
		$end = min($num,$pagination->get_start() + $limit);
		for($i = $pagination->get_start();$i < $end;$i++)
		{
			$data = $locations[$i];
			$duplicates = 0;
			
			if($user->is_admin() || $data['ghost_mode'] == 0 || $cfg['allow_ghost_mode'] == 0)
			{
				$loc = new BS_Location($data['location']);
				$location = $loc->decode();
				if($data['bot_name'] != '')
				{
					$user_name = $data['bot_name'];
					if($loc != 'view_duplicates' && $data['duplicates'] > 0)
						$duplicates = $data['duplicates'] + 1;
				}
				else if($data['user_id'] == 0)
					$user_name = $locale->lang('guest');
				else
				{
					$user_name = BS_UserUtils::get_instance()->get_link(
						$data['user_id'],$data['user_name'],$data['user_group'],true
					);
					if($loc != 'view_duplicates' && $data['duplicates'] > 0)
						$duplicates = $data['duplicates'] + 1;
				}
			}
			else
			{
				$user_name = '<i>'.$locale->lang('hidden_user').'</i>';
				$location = '<i>'.$locale->lang('notavailable').'</i>';
			}
			
			$user_agent = '';
			if($view_details)
			{
				$a_user_agent = FWS_StringHelper::get_limited_string($data['user_agent'],35);
				if($a_user_agent['complete'] != '')
				{
					$user_agent = '<span title="'.$a_user_agent['complete'].'">';
					$user_agent .= $a_user_agent['displayed'].'</span>';
				}
				else
					$user_agent = $a_user_agent['displayed'];
			}
			
			$user_list[] = array(
				'is_hidden' => !$user->is_admin() && $data['ghost_mode'] == 1 &&
					$cfg['allow_ghost_mode'] == 1,
				'user_name' => $user_name,
				'duplicates' => $duplicates,
				'location' => $location,
				'user_ip' => $data['user_ip'],
				'user_agent' => $user_agent,
				'date' => FWS_Date::get_date($data['date'])
			);
		}
		
		$tpl->add_array('user_list',$user_list);
		
		// display page-split
		$purl = $url->get_url(0,$url_params.'&amp;'.BS_URL_LOC.'='.$loc.'&amp;'.BS_URL_SITE.'={d}');
		$functions->add_pagination($pagination,$purl);
	}
	
	/**
	 * the usort-compare-function for the locations
	 * 
	 * @param array $a the first element
	 * @param array $b the second element
	 * @return int 0 = equal, -1 = the first is smaller, 1 the second is smaller.
	 * 	If the direction is descending it is the other way around
	 */
	private function _location_sort_callback($a,$b)
	{
		switch($this->_order)
		{
			case 'username':
				$field = 'user_name';
				break;
			case 'date':
				$field = 'date';
				break;
			case 'location':
				$field = 'location';
				break;
			case 'ip':
				$field = 'user_ip';
				break;
			case 'useragent':
				$field = 'user_agent';
				break;
		}
		
		if($a[$field] > $b[$field])
			return $this->_ad == 'ASC' ? 1 : -1;
		
		if($a[$field] < $b[$field])
			return $this->_ad == 'ASC' ? -1 : 1;
		
		return 0;
	}
}
?>