<?php
/**
 * Contains the module-class which is the base-class for all modules in Boardsolution
 * (A module is a part of the page which will be displayed between header and footer)
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The class has to be named like the following:
 * <code>BS_Front_Module_<yourFileName></code>
 * and you have to extend the BS_Front_Module-class!
 * 
 * Boardsolution will include this file and call the run()-method
 * therefore all stuff you want to do has to be started in this method
 * 
 * @package			Boardsolution
 * @subpackage	front.src
 * @author			Nils Asmussen <nils@script-solution.de>
 */
abstract class BS_Front_Module extends PLIB_Module
{
	/**
	 * Should return wether this module is viewable by guests only. The default value is "false".
	 * Please overwrite the method if you want to change it.
	 * 
	 * @return boolean true if it is only for guests
	 */
	public function is_guest_only()
	{
		return false;
	}
	
	/**
	 * @see PLIB_Module::request_formular()
	 *
	 * @return BS_HTML_Formular
	 */
	protected final function request_formular()
	{
		$tpl = PLIB_Props::get()->tpl();

		$args = func_get_args();
		switch(count($args))
		{
			case 0:
				$form = new BS_HTML_Formular();
				break;
			case 1:
				$form = new BS_HTML_Formular($args[0]);
				break;
			case 2:
				$form = new BS_HTML_Formular($args[0],$args[1]);
				break;
			default:
				PLIB_Helper::error('Invalid number of arguments ('.count($args).')!');
				break;
		}
		
		$tpl->add_variables(array('form' => $form));
		$tpl->add_allowed_method('form','*');
		return $form;
	}
	
	/**
	 * @see PLIB_Module::report_error()
	 */
	protected final function report_error($type = PLIB_Messages::MSG_TYPE_ERROR,$message = '')
	{
		$functions = PLIB_Props::get()->functions();
		$doc = PLIB_Props::get()->doc();

		// if a no-access-message has been added we want to show the login form
		if($message == '' && $type == PLIB_Messages::MSG_TYPE_NO_ACCESS)
		{
			$functions->show_login_form();
			$doc->set_error();
		}
		else
			$doc->report_error($type,$message);
	}
	
	/**
	 * Adds the forum-location-path to the breadcrumbs. Can be used in modules which belong
	 * to a forum or topic.
	 * 
	 * @param int $id the forum-id
	 */
	protected final function add_loc_forum_path($id)
	{
		$forums = PLIB_Props::get()->forums();
		$url = PLIB_Props::get()->url();
		$doc = PLIB_Props::get()->doc();

		if(PLIB_Helper::is_integer($id) && $id > 0)
		{
			$path = $forums->get_path($id);
			for($i = count($path) - 1;$i >= 0;$i--)
				$doc->add_breadcrumb($path[$i][0],$url->get_topics_url($path[$i][1],'&amp;',1));
		}
	}
	
	/**
	 * Adds the topic-location to the breadcrumbs. Can be used in modules which belong
	 * to a topic.
	 */
	protected final function add_loc_topic()
	{
		$url = PLIB_Props::get()->url();
		$doc = PLIB_Props::get()->doc();
		
		$tdata = BS_Front_TopicFactory::get_instance()->get_current_topic();
		if($tdata !== null)
		{
			$murl = $url->get_posts_url($tdata['rubrikid'],$tdata['id'],'&amp;',1);
			$doc->add_breadcrumb($tdata['name'],$murl);
		}
	}
}
?>