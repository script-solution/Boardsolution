<?php
/**
 * Contains the acp-menu-class
 * 
 * @version			$Id: menu.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The menu for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Menu extends PLIB_Singleton
{
	/**
	 * @return BS_ACP_Menu the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * All menu-items of the ACP
	 *
	 * @return array
	 */
	public function get_menu_items()
	{
		// All available items. In the following form:
		// array(
		// 	'<catName>' => array(
		// 		'title' => '<catTitle>',
		// 		'modules' => array(
		// 			'<modName>' => array(
		//				'title' => '<modTitle>',
		// 				'access' => '<access>',			// optional
		// 				'url' => '<moduleURL>',			// optional
		// 				'frame' => '<targetFrame>',	// optional
		// 				'target' => '<target>',			// optional
		//			),
		//			...
		//		),
		//	),
		// 	...
		// )
		//
		// Variables:
		// 	<modTitle>, <catTitle>: These are entries in the language-array
		//	<modName>: The name of the module. Has to be the name of the module-folder!
		// 	<access>: The following values are allowed: 'all','no','admin','default'
		//						'all' = everybody has access, who has access to the ACP
		//						'no' = nobody has access (for deactivating modules)
		//						'admin' = just admins have access
		//						'default' = depending on wether the user is admin or has access to the module
		//						Note that just modules with 'default' access (or not specified) can be used
		//						in the ACP-access-module!
		//	<moduleURL>: If specified this URL is used as it is. Otherwise the default URL for modules
		//							 will be used.
		//	<targetFrame>: The target-frame for the link. May be 'document' to load the URL in the
		//								 complete page. By default the content-frame will be used.
		//	<target>: May be '_blank' for example to load the link in a new window
		
		return array(
			'general' => array(
				'title' => 'acpcat_general',
				'modules' => array(
					'config' => array(
						'title' => 'acpmod_config',
					),
					'bbcode' => array(
						'title' => 'acpmod_bbcode',
					),
					'smileys' => array(
						'title' => 'acpmod_smileys',
					),
					'languages' => array(
						'title' => 'acpmod_languages',
					),
					'avatars' => array(
						'title' => 'acpmod_avatars',
					),
					'attachments' => array(
						'title' => 'acpmod_attachments',
					),
					'subscriptions' => array(
						'title' => 'acpmod_subscriptions',
					),
					'banlist' => array(
						'title' => 'acpmod_bans',
					),
					'bots' => array(
						'title' => 'acpmod_bots',
					),
					'linklist' => array(
						'title' => 'acpmod_links',
					),
				),
			),
			
			'styles' => array(
				'title' => 'acpcat_design',
				'modules' => array(
					'tpleditor' => array(
						'title' => 'acpmod_tpleditor',
					),
					'themes' => array(
						'title' => 'acpmod_themes'
					),
				),
			),
	
			'forums' => array(
				'title' => 'acpcat_forums',
				'modules' => array(
					'forums' => array(
						'title' => 'acpmod_forums',
					),
					'moderators' => array(
						'title' => 'acpmod_moderators'
					),
				),
			),
	
			'user' => array(
				'title' => 'user',
				'modules' => array(
					'usergroups' => array(
						'title' => 'acpmod_usergroups',
					),
					'user' => array(
						'title' => 'acpmod_user',
					),
					'useractivation' => array(
						'title' => 'acpmod_user_activation',
						'access' => BS_ENABLE_EXPORT ? 'no' : 'default',
					),
					'acpaccess' => array(
						'title' => 'acpmod_acpaccess',
					),
					'massemail' => array(
						'title' => 'acpmod_massemail',
					),
					'additionalfields' => array(
						'title' => 'acpmod_addfields',
					),
					'userranks' => array(
						'title' => 'acpmod_userranks'
					),
				),
			),
	
			'maintenance' => array(
				'title' => 'acpcat_maintenance',
				'modules' => array(
					'vcompare' => array(
						'title' => 'acpmod_versioncompare'
					),
					'dbcache' => array(
						'title' => 'acpmod_dbcache',
					),
					'miscellaneous' => array(
						'title' => 'acpmod_miscellaneous',
					),
					'correctmsgs' => array(
						'title' => 'acpmod_correctmsgs',
					),
					'tasks' => array(
						'title' => 'acpmod_tasks',
					),
					'phpinfo' => array(
						'title' => 'acpmod_phpinfo',
					),
					'iplog' => array(
						'title' => 'acpmod_iplog',
					),
					'errorlog' => array(
						'title' => 'acpmod_errorlog',
					),
					'dbbackup' => array(
						'title' => 'acpmod_dbbackup',
						'access' => 'admin',
						'url' => 'dba/index.php',
						'frame' => 'document',
						'target' => '_blank'
					),
				),
			),
			
			'other' => array(
				'title' => 'acpcat_other',
				'modules' => array(
					'faq' => array(
						'title' => 'acpmod_adminfaq',
						'access' => 'all'
					),
					'index' => array(
						'title' => 'acpmod_index',
					),
					'back_to_frontend' => array(
						'title' => 'back_to_frontend',
						'url' => $this->url->get_frontend_url(),
						'access' => 'all',
						'frame' => 'document',
					),
					'logout' => array(
						'title' => 'logout',
						'url' => 'admin.php?logout=1&amp;'.BS_URL_SID.'='.$this->user->get_session_id(),
						'access' => 'all',
						'frame' => 'document',
					),
				),
			),
		);
	}
}
?>