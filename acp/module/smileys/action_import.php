<?php
/**
 * Contains the import-smileys-action
 *
 * @version			$Id: action_import.php 795 2008-05-29 18:22:45Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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

		$this->set_success_msg(sprintf($this->locale->lang('import_smileys_success'),$count));
		$this->set_action_performed(true);

		return 'import_smileys_failed';
	}
}
?>