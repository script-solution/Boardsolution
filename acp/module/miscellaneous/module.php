<?php
/**
 * Contains the miscellaneous module for the ACP
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 *
 * Copyright (C) 2003 - 2012 Nils Asmussen
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();

		$renderer->add_breadcrumb($locale->lang('acpmod_miscellaneous'),BS_URL::build_acpmod_url());
		
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