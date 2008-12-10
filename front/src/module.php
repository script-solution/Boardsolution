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
abstract class BS_Front_Module extends FWS_Module
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
	 * @see FWS_Module::request_formular()
	 *
	 * @param boolean $check_attachments sets wether the attachment-form-vars should be checked
	 * @param boolean $check_preview sets wether the preview-var should be checked
	 * @return BS_HTML_Formular
	 */
	protected final function request_formular($check_attachments = null,$check_preview = null)
	{
		$tpl = FWS_Props::get()->tpl();

		if($check_attachments !== null && $check_preview !== null)
			$form = new BS_HTML_Formular($check_attachments,$check_preview);
		else if($check_attachments !== null)
			$form = new BS_HTML_Formular($check_attachments);
		else
			$form = new BS_HTML_Formular();
		
		$tpl->add_variables(array('form' => $form));
		$tpl->add_allowed_method('form','*');
		return $form;
	}
	
	/**
	 * Adds the forum-location-path to the breadcrumbs. Can be used in modules which belong
	 * to a forum or topic.
	 * 
	 * @param int $id the forum-id
	 */
	protected final function add_loc_forum_path($id)
	{
		$forums = FWS_Props::get()->forums();
		$doc = FWS_Props::get()->doc();
		$renderer = $doc->get_renderer();
		
		if(!($renderer instanceof BS_Front_Renderer_HTML))
			FWS_Helper::def_error('instance','render','BS_Front_Renderer_HTML',$renderer);

		if(FWS_Helper::is_integer($id) && $id > 0)
		{
			$path = $forums->get_path($id);
			for($i = count($path) - 1;$i >= 0;$i--)
				$renderer->add_breadcrumb($path[$i][0],BS_URL::build_topics_url($path[$i][1],1));
		}
	}
	
	/**
	 * Adds the topic-location to the breadcrumbs. Can be used in modules which belong
	 * to a topic.
	 */
	protected final function add_loc_topic()
	{
		$doc = FWS_Props::get()->doc();
		$renderer = $doc->get_renderer();
		
		if(!($renderer instanceof BS_Front_Renderer_HTML))
			FWS_Helper::def_error('instance','render','BS_Front_Renderer_HTML',$renderer);
		
		$tdata = BS_Front_TopicFactory::get_current_topic();
		if($tdata !== null)
		{
			$murl = BS_URL::build_posts_url($tdata['rubrikid'],$tdata['id'],1);
			$renderer->add_breadcrumb($tdata['name'],$murl);
		}
	}
}
?>