<?php
/**
 * Contains the helper-class for correctmsgs
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
 * An helper-class for the correctmsgs-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_CorrectMsgs_Helper extends FWS_UtilBase
{
	/**
	 * Determines all incorrect messages
	 *
	 * @return array an array of the form: <code>array(<type>,<id>)</code>
	 */
	public static function get_incorrect_messages()
	{
		$incorrect = array();
		
		// posts
		foreach(BS_DAO::get_posts()->get_invalid_post_ids() as $data)
			$incorrect[] = array('post',$data['id']);
	
		// signatures
		foreach(BS_DAO::get_profile()->get_invalid_signature_ids() as $data)
			$incorrect[] = array('signature',$data['id']);
	
		// pms
		foreach(BS_DAO::get_pms()->get_invalid_pm_ids() as $data)
			$incorrect[] = array('pm',$data['id']);
	
		// links
		foreach(BS_DAO::get_links()->get_invalid_link_ids() as $data)
			$incorrect[] = array('link',$data['id']);
	
		// events
		foreach(BS_DAO::get_events()->get_invalid_event_ids() as $data)
			$incorrect[] = array('event',$data['id']);
		
		return $incorrect;
	}
}
?>