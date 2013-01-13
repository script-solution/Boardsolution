<?php
/**
 * Contains the front-sub-module-container-class
 * 
 * @package			Boardsolution
 * @subpackage	front.src
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
 * The module-base class for all front-sub-module-containers. That means a module
 * that consists of sub-modules.
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_SubModuleContainer extends BS_Front_Module
{
	/**
	 * The sub-module
	 *
	 * @var BS_Front_SubModule
	 */
	protected $_sub;
	
	/**
	 * The init
	 * 
	 * @param string $module your module-name
	 * @param array $submodules the sub-module names that are possible
	 * @param string $default the default sub-module
	 */
	public function __construct($module,$submodules = array(),$default = 'default')
	{
		$input = FWS_Props::get()->input();

		if(count($submodules) == 0)
			FWS_Helper::error('Please provide the possible submodules of this module!');
		
		$sub = $input->correct_var(BS_URL_SUB,'get',FWS_Input::STRING,$submodules,$default);
		
		// include the sub-module and create it
		include_once(FWS_Path::server_app().'front/module/'.$module.'/sub_'.$sub.'.php');
		$classname = 'BS_Front_SubModule_'.$module.'_'.$sub;
		$this->_sub = new $classname();
	}

	/**
	 * @see FWS_Module::error_occurred()
	 *
	 * @return boolean
	 */
	public function error_occurred()
	{
		return parent::error_occurred() || $this->_sub->error_occurred();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$tpl->set_template($this->_sub->get_template());
		$this->_sub->run();
		$tpl->restore_template();
	}
}
?>