<?php
/**
 * Contains the regenerate-action-class
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
 * The regenerate-action for the db-cache module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_dbcache_regenerate extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$input = FWS_Props::get()->input();
		$cache = FWS_Props::get()->cache();
		$locale = FWS_Props::get()->locale();

		$names = $input->get_var('delete','post');
		if(count($names) == 0)
			return '';
		
		$found = array();
		foreach($names as $name)
		{
			if($cache->get_cache($name) !== null)
			{
				$cache->refresh($name);
				$found[] = $name;
			}
		}
		
		$this->set_action_performed(true);
		$this->set_success_msg(
			sprintf($locale->lang('regenerate_cache_success'),'"'.implode('", "',$found).'"')
		);
		
		return '';
	}
}
?>