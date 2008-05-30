<?php
/**
 * Contains the dao-factory
 *
 * @version			$Id: dao.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The factory for all DAO-classes. This allows us for example to support other DBMS in future
 * by exchanging the DAO-classes here.
 * 
 * @package			Boardsolution
 * @subpackage	src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DAO extends PLIB_UtilBase
{
	/**
	 * @return BS_DAO_ACPAccess the DAO for the acp-access-table
	 */
	public static function get_acpaccess()
	{
		return BS_DAO_ACPAccess::get_instance();
	}
	
	/**
	 * @return BS_DAO_Activation the DAO for the activation-table
	 */
	public static function get_activation()
	{
		return BS_DAO_Activation::get_instance();
	}
	
	/**
	 * @return BS_DAO_AddFields the DAO for the add-fields-table
	 */
	public static function get_addfields()
	{
		return BS_DAO_AddFields::get_instance();
	}
	
	/**
	 * @return BS_DAO_Attachments the DAO for the attachments-table
	 */
	public static function get_attachments()
	{
		return BS_DAO_Attachments::get_instance();
	}
	
	/**
	 * @return BS_DAO_Avatars the DAO for the avatars-table
	 */
	public static function get_avatars()
	{
		return BS_DAO_Avatars::get_instance();
	}
	
	/**
	 * @return BS_DAO_Bans the DAO for the bans-table
	 */
	public static function get_bans()
	{
		return BS_DAO_Bans::get_instance();
	}
	
	/**
	 * @return BS_DAO_BBCodes the DAO for the bbcodes-table
	 */
	public static function get_bbcodes()
	{
		return BS_DAO_BBCodes::get_instance();
	}
	
	/**
	 * @return BS_DAO_Bots the DAO for the bots-table
	 */
	public static function get_bots()
	{
		return BS_DAO_Bots::get_instance();
	}
	
	/**
	 * @return BS_DAO_CFGGroups the DAO for the cfg-groups-table
	 */
	public static function get_cfggroups()
	{
		return BS_DAO_CFGGroups::get_instance();
	}
	
	/**
	 * @return BS_DAO_ChangeEmail the DAO for the change-email-table
	 */
	public static function get_changeemail()
	{
		return BS_DAO_ChangeEmail::get_instance();
	}
	
	/**
	 * @return BS_DAO_ChangePW the DAO for the change-pw-table
	 */
	public static function get_changepw()
	{
		return BS_DAO_ChangePW::get_instance();
	}
	
	/**
	 * @return BS_DAO_Config the DAO for the config-table
	 */
	public static function get_config()
	{
		return BS_DAO_Config::get_instance();
	}
	
	/**
	 * @return BS_DAO_Events the DAO for the events-table
	 */
	public static function get_events()
	{
		return BS_DAO_Events::get_instance();
	}
	
	/**
	 * @return BS_DAO_EventAnn the DAO for the event-announcements-table
	 */
	public static function get_eventann()
	{
		return BS_DAO_EventAnn::get_instance();
	}
	
	/**
	 * @return BS_DAO_Forums the DAO for the forums-table
	 */
	public static function get_forums()
	{
		return BS_DAO_Forums::get_instance();
	}
	
	/**
	 * @return BS_DAO_ForumsPerm the DAO for the forums-permission-table
	 */
	public static function get_forums_perm()
	{
		return BS_DAO_ForumsPerm::get_instance();
	}
	
	/**
	 * @return BS_DAO_Intern the DAO for the intern-table
	 */
	public static function get_intern()
	{
		return BS_DAO_Intern::get_instance();
	}
	
	/**
	 * @return BS_DAO_Langs the DAO for the langs-table
	 */
	public static function get_langs()
	{
		return BS_DAO_Langs::get_instance();
	}
	
	/**
	 * @return BS_DAO_Links the DAO for the links-table
	 */
	public static function get_links()
	{
		return BS_DAO_Links::get_instance();
	}
	
	/**
	 * @return BS_DAO_LinkVotes the DAO for the link-votes-table
	 */
	public static function get_linkvotes()
	{
		return BS_DAO_LinkVotes::get_instance();
	}
	
	/**
	 * @return BS_DAO_LogErrors the DAO for the log-errors-table
	 */
	public static function get_logerrors()
	{
		return BS_DAO_LogErrors::get_instance();
	}
	
	/**
	 * @return BS_DAO_LogIPs the DAO for the log-ip-table
	 */
	public static function get_logips()
	{
		return BS_DAO_LogIPs::get_instance();
	}
	
	/**
	 * @return BS_DAO_Mods the DAO for the mods-table
	 */
	public static function get_mods()
	{
		return BS_DAO_Mods::get_instance();
	}
	
	/**
	 * @return BS_DAO_PMs the DAO for the pms-table
	 */
	public static function get_pms()
	{
		return BS_DAO_PMs::get_instance();
	}
	
	/**
	 * @return BS_DAO_Polls the DAO for the polls-table
	 */
	public static function get_polls()
	{
		return BS_DAO_Polls::get_instance();
	}
	
	/**
	 * @return BS_DAO_PollVotes the DAO for the poll-votes-table
	 */
	public static function get_pollvotes()
	{
		return BS_DAO_PollVotes::get_instance();
	}
	
	/**
	 * @return BS_DAO_Posts the DAO for the posts-table
	 */
	public static function get_posts()
	{
		return BS_DAO_Posts::get_instance();
	}
	
	/**
	 * @return BS_DAO_Profile the DAO for the profile-table
	 */
	public static function get_profile()
	{
		return BS_DAO_Profile::get_instance();
	}
	
	/**
	 * @return BS_DAO_Ranks the DAO for the ranks-table
	 */
	public static function get_ranks()
	{
		return BS_DAO_Ranks::get_instance();
	}
	
	/**
	 * @return BS_DAO_Search the DAO for the search-table
	 */
	public static function get_search()
	{
		return BS_DAO_Search::get_instance();
	}
	
	/**
	 * @return BS_DAO_Sessions the DAO for the sessions-table
	 */
	public static function get_sessions()
	{
		return BS_DAO_Sessions::get_instance();
	}
	
	/**
	 * @return BS_DAO_Smileys the DAO for the smileys-table
	 */
	public static function get_smileys()
	{
		return BS_DAO_Smileys::get_instance();
	}
	
	/**
	 * @return BS_DAO_Subscr the DAO for the subscriptions-table
	 */
	public static function get_subscr()
	{
		return BS_DAO_Subscr::get_instance();
	}
	
	/**
	 * @return BS_DAO_Tasks the DAO for the tasks-table
	 */
	public static function get_tasks()
	{
		return BS_DAO_Tasks::get_instance();
	}
	
	/**
	 * @return BS_DAO_Themes the DAO for the themes-table
	 */
	public static function get_themes()
	{
		return BS_DAO_Themes::get_instance();
	}
	
	/**
	 * @return BS_DAO_Topics the DAO for the topics-table
	 */
	public static function get_topics()
	{
		return BS_DAO_Topics::get_instance();
	}
	
	/**
	 * @return BS_DAO_Unread the DAO for the unread-table
	 */
	public static function get_unread()
	{
		return BS_DAO_Unread::get_instance();
	}
	
	/**
	 * @return BS_DAO_UnreadHide the DAO for the unread-hide-table
	 */
	public static function get_unreadhide()
	{
		return BS_DAO_UnreadHide::get_instance();
	}
	
	/**
	 * @return BS_DAO_UnsentPosts the DAO for the unsent-posts-table
	 */
	public static function get_unsentposts()
	{
		return BS_DAO_UnsentPosts::get_instance();
	}

	/**
	 * @return BS_DAO_User the DAO for the user-table
	 */
	public static function get_user()
	{
		return BS_DAO_User::get_instance();
	}
	
	/**
	 * @return BS_DAO_UserBans the DAO for the user-bans-table
	 */
	public static function get_userbans()
	{
		return BS_DAO_UserBans::get_instance();
	}
	
	/**
	 * @return BS_DAO_UserGroups the DAO for the user-groups-table
	 */
	public static function get_usergroups()
	{
		return BS_DAO_UserGroups::get_instance();
	}
}
?>