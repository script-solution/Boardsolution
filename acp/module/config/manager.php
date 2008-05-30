<?php
/**
 * Contains the config-manager for BS
 *
 * @version			$Id: manager.php 676 2008-05-08 09:02:28Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The item-manager for the ACP of BS which supports additional item-types
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_Manager extends PLIB_Config_Manager
{
	protected function _get_item($data)
	{
		$item = parent::_get_item($data);
		if($item !== null)
			return $item;
		
		$type = $data->get_type();
		$file = PLIB_Path::inner().'acp/module/config/item/'.$type.'.php';
		if(is_file($file))
		{
			include_once($file);
			$class = 'BS_ACP_Config_Item_'.$type;
			if(class_exists($class))
				return new $class($data);
		}
		
		return null;
	}
}
?>