<?php
/**
 * Contains the default-submodule for vcompare
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
 * The default sub-module for the vcompare-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_vcompare_default extends BS_ACP_SubModule
{
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$this->request_formular();
		
		$http = new FWS_HTTP(BS_Version::VERSION_HOST);
		$versions = $http->get(BS_Version::VERSION_PATH);
		if($versions === false)
		{
			$this->report_error(
				FWS_Document_Messages::ERROR,$http->get_error_code().': '.$http->get_error_message()
			);
			return;
		}
		
		// build combo-items
		$cbversions = array();
		$current = new BS_Version(BS_VERSION_ID,BS_VERSION);
		$vs = BS_Version::read_versions($versions);
		rsort($vs);
		foreach($vs as $v)
		{
			/* @var $v BS_Version */
			if($v->get_release_number() == $current->get_release_number())
				$cbversions[$v->get_id()] = $v->get_name();
		}
		
		// check for updates
		$res = BS_Version::check_for_update($vs);
		$update_notice = '';
		$instr = array();
		if(is_array($res))
		{
			$update_notice = sprintf(
				$locale->lang('vcompare_update_available'),$res[count($res) - 1]->get_name()
			);
			foreach($res as $v)
			{
				if($v->get_instructions())
					$instr[] = $v->get_instructions();
			}
		}
		else if($res !== null)
			$update_notice = sprintf($locale->lang('vcompare_release_available'),$res->get_name());
		
		$tpl->add_variables(array(
			'current_version' => BS_VERSION,
			'versions' => $cbversions,
			'selected_version' => is_array($res) ? $res[count($res) - 1]->get_id() : 0,
			'update' => $res !== null,
			'update_notice' => $update_notice,
			'update_instr' => $instr
		));
	}
}
?>