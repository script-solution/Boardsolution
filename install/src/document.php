<?php
/**
 * Contains install-document
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The document of the install-script
 *
 * @package			Boardsolution
 * @subpackage	install.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Install_Document extends FWS_Document
{
	/**
	 * Returns the default renderer. If it is already set the instance will be returned. Otherwise
	 * it will be created, set and returned.
	 *
	 * @return BS_Install_Renderer_HTML
	 */
	public function use_default_renderer()
	{
		$renderer = $this->get_renderer();
		if($renderer instanceof BS_Install_Renderer_HTML)
			return $renderer;
		
		$renderer = new BS_Install_Renderer_HTML();
		$this->set_renderer($renderer);
		return $renderer;
	}

	/**
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		parent::prepare_rendering();
		
		$this->set_charset(BS_HTML_CHARSET);
		
		// set default renderer
		if($this->get_renderer() === null)
			$this->use_default_renderer();
	}

	/**
	 * @see FWS_Document::load_module()
	 *
	 * @return BS_Install_Module
	 */
	protected function load_module()
	{
		$this->_module_name = FWS_Document::load_module_def(
			'BS_Install_Module_','action','1','install/module/'
		);
		$class = 'BS_Install_Module_'.$this->_module_name;
		return new $class();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>