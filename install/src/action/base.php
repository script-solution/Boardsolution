<?php
/**
 * Contains the base-class for all install-actions
 * 
 * @package			Boardsolution
 * @subpackage	install.src.action
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
 * The base-class for all install-actions
 *
 * @package			Boardsolution
 * @subpackage	install.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Install_Action_Base extends FWS_Action_Base
{
	/**
	 * Constructor
	 * 
	 * @param mixed $id the action-id
	 */
	public function __construct($id)
	{
		parent::__construct($id);
		
		$this->set_redirect(false);
		$this->set_show_status_page(false);
	}
	
	/**
	 * Generates an URL for the next step
	 *
	 * @return FWS_URL the url
	 */
	protected function get_step_url()
	{
		$input = FWS_Props::get()->input();
		$action = $input->get_var('action','get',FWS_Input::STRING);
		$dir = $input->get_var('dir','get',FWS_Input::STRING);
		$phpself = $input->get_var('PHP_SELF','server',FWS_Input::STRING);
		$url = new FWS_URL();
		$url->set_file(basename($phpself));
		if($dir == 'back')
			$url->set('action',$action - 1);
		else if($dir == 'forward')
			$url->set('action',$action + 1);
		else
			$url->set('action',$action);
		return $url;
	}
}
?>