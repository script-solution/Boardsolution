<?php
/**
 * Contains the acp-content-document-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The content-document for the ACP
 *
 * @package			Boardsolution
 * @subpackage	acp.src.document
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Document_Content extends BS_ACP_Document
{
	/**
	 * Returns the default renderer. If it is already set the instance will be returned. Otherwise
	 * it will be created, set and returned.
	 *
	 * @return BS_ACP_Renderer_Content the default renderer
	 */
	public function use_default_renderer()
	{
		$renderer = $this->get_renderer();
		if($renderer instanceof BS_ACP_Renderer_Content)
			return $renderer;
		
		$renderer = new BS_ACP_Renderer_Content();
		$this->set_renderer($renderer);
		return $renderer;
	}
	
	/**
	 * @see FWS_Document::prepare_rendering()
	 */
	protected function prepare_rendering()
	{
		parent::prepare_rendering();
		
		// set default renderer
		if($this->get_renderer() === null)
			$this->use_default_renderer();
	}

	/**
	 * Determines the module to load and returns it
	 *
	 * @return BS_Front_Module the module
	 */
	protected function load_module()
	{
		$this->_module_name = FWS_Document::load_module_def(
			'BS_ACP_Module_','loc','index','acp/module/'
		);
		$class = 'BS_ACP_Module_'.$this->_module_name;
		return new $class();
	}
}
?>