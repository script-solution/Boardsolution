<?php
/**
 * Contains the stats-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
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
 * The stats-module
 * 
 * @package			Boardsolution
 * @subpackage	front.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_stats extends BS_Front_SubModuleContainer
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('stats',array('default','timeline'));
	}
	
	/**
	 * @see BS_Front_SubModuleContainer::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$cfg = FWS_Props::get()->cfg();
		$auth = FWS_Props::get()->auth();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_breadcrumb($locale->lang('statistics'),'');
		$renderer->set_has_access($cfg['enable_stats'] == 1 && $auth->has_global_permission('view_stats'));
		
		// init submodule
		$this->_sub->init($doc);
		
		$renderer->set_template($this->_sub->get_template());
	}
}
?>