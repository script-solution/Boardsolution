<?php
/**
 * Contains the admin-faq module for the ACP
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
 * The admin-faq-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_faq extends BS_ACP_Module
{
	/**
	 * The next id
	 *
	 * @var integer
	 */
	private $_id = 0;

	/**
	 * The entries
	 *
	 * @var array
	 */
	private $_entries = array();

	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Document_Content $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_breadcrumb($locale->lang('acpmod_adminfaq'));
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$locale = FWS_Props::get()->locale();
		$tpl = FWS_Props::get()->tpl();

		$http = new FWS_HTTP('www.boardsolution.de');
		$xml = $http->get('/lang-de/bs-informationen/faq?format=raw');
		$doc = new SimpleXMLElement($xml);
		
		$id = 1;
		$entries = array();
		foreach($doc->question as $question)
		{
			$entries[] = array(
				'id' => (string)$question->name,
				'question' => (string)$question->title,
				'answer' => (string)$question->answer
			);
		}
		
		$tpl->add_variable_ref('questions',$entries);
	}
}
?>