<?php
/**
 * Contains the admin-faq module for the ACP
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
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
				'id' => $id++,
				'question' => (string)$question->title,
				'answer' => (string)$question->answer
			);
		}
		
		$tpl->add_variable_ref('questions',$entries);
	}
}
?>