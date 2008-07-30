<?php
/**
 * Contains the config-manager for BS
 *
 * @version			$Id$
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
final class BS_ACP_Module_Config_Manager extends FWS_Config_Manager
{
	protected function get_item($data)
	{
		$item = parent::get_item($data);
		if($item !== null)
			return $item;
		
		$type = $data->get_type();
		$file = FWS_Path::server_app().'acp/module/config/item/'.$type.'.php';
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