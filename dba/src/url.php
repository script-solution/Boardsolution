<?php
/**
 * Contains the URL-class for the dbbackup-script
 * 
 * @package			Boardsolution
 * @subpackage	dba.src
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
 * The URL-class for the dbbackup-script
 *
 * @package			Boardsolution
 * @subpackage	dba.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_URL extends FWS_URL
{
	/**
	 * Builds an URL for the backup-script
	 * 
	 * @param mixed $target the action-value. 0 = current, -1 = none
	 * @param string $additional additional parameters
	 * @param string $separator the separator for the parameters.
	 * @return string the url
	 */
	public static function build_url($target = 0,$additional = '',$separator = '&amp;')
	{
		$url = new BS_DBA_URL();
		$url->set_separator($separator);
		$url->set_sid_policy(self::SID_FORCE);
		
		$input = FWS_Props::get()->input();
		
		// add action
		$action_param = $input->get_var('action','get',FWS_Input::STRING);
		if($target === 0 && $action_param !== null)
			$url->set('action',$action_param);
		else if($target !== -1)
			$url->set('action',$target);
		
		// add additional params
		foreach(FWS_Array_Utils::advanced_explode($separator,$additional) as $param)
		{
			@list($k,$v) = explode('=',$param);
			$url->set($k,$v);
		}
		
		return $url->to_url();
	}
}
?>