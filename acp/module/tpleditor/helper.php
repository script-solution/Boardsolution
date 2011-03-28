<?php
/**
 * Contains the helper-class for the template-editor
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * An helper-class for the template-editor-module of the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_TplEditor_Helper extends FWS_Singleton
{
	/**
	 * @return BS_ACP_Module_TplEditor_Helper the instance of this class
	 */
	public static function get_instance()
	{
		return parent::_get_instance(get_class());
	}
	
	/**
	 * The current path
	 *
	 * @var string
	 */
	private $_path;
	
	/**
	 * The current path in the default-theme
	 *
	 * @var string
	 */
	private $_path_in_default;
	
	/**
	 * Prevent instantiation
	 */
	public function __construct()
	{
		$input = FWS_Props::get()->input();

		parent::__construct();
		
		$path = $input->get_var('path','get',FWS_Input::STRING);
		$path = FWS_FileUtils::clean_path($path);
		$path = FWS_FileUtils::ensure_no_trailing_slash($path);
		if($path == null)
		{
			$this->_path = FWS_Path::server_app().'themes';
			$this->_path_in_default = $this->_path;
		}
		else
		{
			$this->_path = FWS_Path::server_app().$path;
			$this->_path_in_default = preg_replace('/themes\/[^\/]+/','themes/default',$this->_path);
		}
	}
	
	/**
	 * @return string the parent path
	 */
	public function get_parent_path()
	{
		$dir = dirname($this->_path);
		return ($dir == '/' || $dir == '\\' || $dir == '.') ? '' : $dir;
	}
	
	/**
	 * @return string the current path
	 */
	public function get_path()
	{
		return $this->_path;
	}
	
	/**
	 * @return string the current path in the default-theme
	 */
	public function get_path_in_default()
	{
		return $this->_path_in_default;
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>