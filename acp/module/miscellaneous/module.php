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
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('miscellaneous',array('default','operation'),'default');
	}

	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();

		$renderer->add_breadcrumb($locale->lang('acpmod_miscellaneous'),BS_URL::get_acpmod_url());
		
		// init submodule
		$this->_sub->init($doc);
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