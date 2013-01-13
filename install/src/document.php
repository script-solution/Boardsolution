<?php
/**
 * Contains install-document
 * 
 * @package			Boardsolution
 * @subpackage	install.src
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