<?php
/**
 * Contains the import-avatars-action
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
 * The import-avatars-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_avatars_import extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$locale = FWS_Props::get()->locale();

		$avatars = array();
		foreach(BS_DAO::get_avatars()->get_all() as $data)
			$avatars[$data['av_pfad']] = 1;
		
		$count = 0;
		$dir = opendir(FWS_Path::server_app().'images/avatars');
		while($file = readdir($dir))
		{
			if($file != '..' && $file != '.' && $file != 'index.htm' && $file != '_blank.jpg')
			{
				if(!isset($avatars[$file]) && preg_match('/\.(gif|jpeg|jpg|png)$/i',$file))
				{
					BS_DAO::get_avatars()->create($file);
					$count++;
				}
			}
		}
		
		$this->set_success_msg(sprintf($locale->lang('avatars_inserted_successfully'),$count));
		$this->set_action_performed(true);

		return '';
	}
}
?>