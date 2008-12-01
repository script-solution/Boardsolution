<?php
/**
 * Contains the helper-class for the usergroups
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The helper for the usergroups-module
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_UserGroups_Helper extends FWS_Singleton
{
	/**
	 * @return BS_ACP_Module_UserGroups_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * All predefined user-groups
	 * 
	 * @var array
	 */
	private $_predef_groups = array(
		BS_STATUS_ADMIN,BS_STATUS_USER,BS_STATUS_GUEST
	);
	
	/**
	 * All permissions of the groups
	 *
	 * @var array
	 */
	private $_permissions = array(
		'is_super_mod','enter_board','view_memberlist','view_linklist',
		'view_stats','view_calendar','view_search','view_userdetails','view_useronline_list',
		'view_online_locations','view_user_online_detail','edit_own_posts',
		'delete_own_posts','edit_own_threads','delete_own_threads','openclose_own_threads',
		'send_mails','always_edit_poll_options','add_new_link','attachments_add',
		'attachments_download','add_cal_event','edit_cal_event','delete_cal_event',
		'subscribe_forums','disable_ip_blocks','view_user_ip',
	);

	/**
	 * An array with the settings which are disallowed (not possible) for guests
	 *
	 * @var array
	 */
	private $_guest_disallowed = array(
		'is_super_mod','edit_own_posts','delete_own_posts','edit_own_threads',
		 'delete_own_threads','openclose_own_threads','add_new_link','attachments_add',
		 'add_cal_event','edit_cal_event','delete_cal_event','subscribe_forums',
		 'view_user_ip','always_edit_poll_options'
	);
	
	/**
	 * @return array all predefined user-groups
	 */
	public function get_predef_groups()
	{
		return $this->_predef_groups;
	}
	
	/**
	 * @return array all permissions
	 */
	public function get_permissions()
	{
		return $this->_permissions;
	}
	
	/**
	 * @return array an array of all permissions that are not allowed for guests
	 */
	public function get_guest_disallowed()
	{
		return $this->_guest_disallowed;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>