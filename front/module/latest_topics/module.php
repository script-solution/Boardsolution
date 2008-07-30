<?php
/**
 * Contains the latest-topics-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The latest-topics-module
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Module_latest_topics extends BS_Front_Module
{
	/**
	 * @see PLIB_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = PLIB_Props::get()->locale();
		$url = PLIB_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$doc->use_default_renderer()->set_robots_value('index,follow');
		
		$renderer->add_breadcrumb($locale->lang('current_topics'),$url->get_url(0));
	}
	
	/**
	 * @see PLIB_Module::run()
	 */
	public function run()
	{
		$input = PLIB_Props::get()->input();
		$url = PLIB_Props::get()->url();
		$tpl = PLIB_Props::get()->tpl();

		$fid = $input->get_var(BS_URL_FID,'get',PLIB_Input::ID);
		
		$forum_combo = BS_ForumUtils::get_instance()->get_recursive_forum_combo(
			BS_URL_FID,$fid,0,false,true
		);
		
		$hidden_fields = array();
		$hidden_fields[BS_URL_ACTION] = 'latest_topics';
		if(($sid = $url->get_splitted_session_id()) != 0)
			$hidden_fields[$sid[0]] = $sid[1];
		$hidden_fields = array_merge($hidden_fields,$url->get_extern_vars());
		
		BS_Front_TopicFactory::get_instance()->add_latest_topics_full($fid);
		
		$tpl->add_variables(array(
			'target_url' => $input->get_var('PHP_SELF','server',PLIB_Input::STRING),
			'hidden_fields' => $hidden_fields,
			'forum_combo' => $forum_combo
		));
	}
}
?>