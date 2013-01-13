<?php
/**
 * Contains the config-data class
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
 * The config-data for boardsolution
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Data extends FWS_Config_Data
{
	/**
	 * Indicates wether the setting affects the messages:
	 * <pre>
	 * 	0 = no
	 * 	1 = a little bit
	 * 	2 = heavy
	 * </pre>
	 *
	 * @var int
	 */
	private $_affects_msgs;
	
	/**
	 * Constructor
	 *
	 * @param array $data the config-data from the database
	 */
	public function __construct($data)
	{
		parent::__construct(
			$data['id'],$data['name'],$data['custom_title'],$data['group_id'],$data['sort'],
			$data['type'],$data['properties'],$data['suffix'],$data['value'],$data['default']
		);
		
		$this->_affects_msgs = $data['affects_msgs'];
	}
	
	/**
	 * @return int wether the setting affects the messages:
	 * 	<pre>
	 * 		0 = no
	 * 		1 = a little bit
	 * 		2 = heavy
	 * 	</pre>
	 */
	public function get_affects_msgs()
	{
		return $this->_affects_msgs;
	}
}
?>