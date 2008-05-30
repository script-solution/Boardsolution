<?php
/**
 * Contains the miscellaneous module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The miscellaneous-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_miscellaneous extends BS_ACP_SubModuleContainer
{
	/**
	 * The different tasks the user can execute
	 *
	 * @var array
	 */
	private static $_tasks = array(
		'forums' => 'refresh_forum_attributes',
		'topics' => 'refresh_topic_attributes',
		'messages' => 'refresh_messages',
		'userexp' => 'refresh_user_posts'
	);
	
	public function __construct()
	{
		parent::__construct('miscellaneous',array('default','operation'),'default');
	}

	public function get_location()
	{
		$loc = array(
			$this->locale->lang('acpmod_miscellaneous') => $this->url->get_acpmod_url()
		);
		return array_merge($loc,$this->_sub->get_location());
	}
	
	/**
	 * @return array an associative array with all tasks that may be executed:
	 * 	<code>array(<name> => <lang_name>,...)</code>
	 */
	public static function get_tasks()
	{
		return self::$_tasks;
	}
}
?>