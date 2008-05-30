<?php
/**
 * Contains the import-avatars-action
 *
 * @version			$Id: action_import.php 717 2008-05-21 14:12:53Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
		$avatars = array();
		foreach(BS_DAO::get_avatars()->get_all() as $data)
			$avatars[$data['av_pfad']] = 1;
		
		$count = 0;
		$dir = opendir(PLIB_Path::inner().'images/avatars');
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
		
		$this->set_success_msg(sprintf($this->locale->lang('avatars_inserted_successfully'),$count));
		$this->set_action_performed(true);

		return '';
	}
}
?>