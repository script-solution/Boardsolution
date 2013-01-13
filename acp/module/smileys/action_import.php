<?php
/**
 * Contains the import-smileys-action
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
 * The import-smileys-action
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Action_smileys_import extends BS_ACP_Action_Base
{
	public function perform_action()
	{
		$locale = FWS_Props::get()->locale();

		$i = BS_DAO::get_smileys()->get_next_sort_key();
		
		$count = 0;
		$dir = opendir('images/smileys');
		while($file = readdir($dir))
		{
			$file = basename($file);
			if($file != '.' && $file != '..' && preg_match('/\.(jpg|jpeg|gif|bmp|png)$/i',$file))
			{
				if(!BS_DAO::get_smileys()->path_exists($file))
				{
					BS_DAO::get_smileys()->create(array(
						'smiley_path' => $file,
						'sort_key' => $i
					));
					$count++;
					$i++;
				}
			}
		}
		closedir($dir);

		$this->set_success_msg(sprintf($locale->lang('import_smileys_success'),$count));
		$this->set_action_performed(true);

		return 'import_smileys_failed';
	}
}
?>