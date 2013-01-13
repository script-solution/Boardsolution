<?php
/**
 * Contains the page-class which is used to display the whole frontend of the board except the
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
 * Represents the frontend of Boardsolution. Includes all necessary files and loads the appropriate
 * module. And it shows header, the module and footer.
 *
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Document extends BS_Document
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		try
		{
			parent::__construct();
			
			$this->_check_addfields();
		}
		catch(FWS_Exception_Critical $e)
		{
			echo $e;
		}
	}
	
	/**
	 * Returns the default renderer. If it is already set the instance will be returned. Otherwise
	 * it will be created, set and returned.
	 *
	 * @return BS_Front_Renderer_HTML
	 */
	public function use_default_renderer()
	{
		$renderer = $this->get_renderer();
		if($renderer instanceof BS_Front_Renderer_HTML)
			return $renderer;
		
		$renderer = new BS_Front_Renderer_HTML();
		$this->set_renderer($renderer);
		return $renderer;
	}
	
	/**
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		parent::prepare_rendering();
		
		// set default renderer
		if($this->get_renderer() === null)
			$this->use_default_renderer();
	}

	/**
	 * Determines the module to load and returns it
	 *
	 * @return BS_Front_Module the module
	 */
	protected function load_module()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();
		
		// determine start-module
		if($cfg['enable_portal'] == 1 &&
			(!$user->is_loggedin() || $user->get_profile_val('startmodule') == 'portal'))
			$default = 'portal';
		else
			$default = 'forums';
		
		$this->_module_name = FWS_Document::load_module_def(
			'BS_Front_Module_',BS_URL_ACTION,$default,'front/module/'
		);
		$class = 'BS_Front_Module_'.$this->_module_name;
		return new $class();
	}
	
	/**
	 * Checks wether any required additional field is empty. If so the user will be redirected
	 * to the profile-info-page (if he/she is not already there).
	 */
	private function _check_addfields()
	{
		$cfg = FWS_Props::get()->cfg();
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		if($cfg['force_fill_of_empty_req_fields'] == 1)
		{
			$action = $input->get_var(BS_URL_ACTION,'get',FWS_Input::STRING);
			$loc = $input->get_var(BS_URL_SUB,'get',FWS_Input::STRING);
			if($user->is_loggedin() && ($action != 'userprofile' || $loc != 'infos'))
			{
				if(BS_AddField_Manager::get_instance()->is_any_required_field_empty())
				{
					$murl = BS_URL::get_sub_url('userprofile','infos');
					$murl->set(BS_URL_MODE,1);
					$this->redirect($murl);
				}
			}
		}
	}
}
?>