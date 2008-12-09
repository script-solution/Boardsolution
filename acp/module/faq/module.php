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

		$locale->add_language_file('lang_admin_faq.php');

		$this->_add_entry(
			$locale->lang('faq_q_board_logo'),$locale->lang('faq_a_board_logo')
		);
		$this->_add_entry(
			$locale->lang('faq_q_moderators'),$locale->lang('faq_a_moderators')
		);
		$this->_add_entry(
			$locale->lang('faq_q_gzip'),$locale->lang('faq_a_gzip')
		);
		$this->_add_entry(
			$locale->lang('faq_q_link_to_hp'),$locale->lang('faq_a_link_to_hp')
		);
		$this->_add_entry(
			$locale->lang('faq_q_status_messages'),$locale->lang('faq_a_status_messages')
		);
		$this->_add_entry(
			$locale->lang('faq_q_add_bbcode'),$locale->lang('faq_a_add_bbcode')
		);
		$this->_add_entry(
			$locale->lang('faq_q_subforums'),$locale->lang('faq_a_subforums')
		);
		$this->_add_entry(
			$locale->lang('faq_q_reduce_userdata'),$locale->lang('faq_a_reduce_userdata')
		);
		$this->_add_entry(
			$locale->lang('faq_q_templates'),$locale->lang('faq_a_templates')
		);
		$this->_add_entry(
			$locale->lang('faq_q_logout'),$locale->lang('faq_a_logout')
		);
		$this->_add_entry(
			$locale->lang('faq_q_emails_spam'),$locale->lang('faq_a_emails_spam')
		);
		$this->_add_entry(
			$locale->lang('faq_q_bs_api'),$locale->lang('faq_a_bs_api')
		);
		$this->_add_entry(
			$locale->lang('faq_q_bbceditor_extra_tags'),$locale->lang('faq_a_bbceditor_extra_tags')
		);
		
		$tpl->add_variable_ref('questions',$this->_entries);
	}

	/**
	 * Adds an entry to the list
	 *
	 * @param string $question he question
	 * @param string $answer the answer
	 */
	private function _add_entry($question,$answer)
	{
		$this->_entries[] = array(
			'id' => $this->_id++,
			'question' => $question,
			'answer' => $answer
		);
	}
}
?>