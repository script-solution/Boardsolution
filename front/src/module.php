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
	 * Creates the formular, adds it to the template and allows all methods of it
	 * to be called.
	 *
	 * @return BS_HTML_Formular the created formular
	 */
	protected final function _request_formular()
	{
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
		
		$this->tpl->add_variables(array('form' => $form));
		$this->tpl->add_allowed_method('form','*');
		return $form;
	}

	/**
	 * returns if this module is only for guests
	 * 
	 * @return boolean true if it is only for guests
	 */
	public function is_guest_only()
	{
		return false;
	}
	
	/**
	 * Should return the value for the meta-tag "robots".
	 * That means if you for example return "noindex" the meta-tag would look like:
	 * <code>
	 * 	<meta name="robots" content="noindex" />
	 * </code>
	 * By default the value is "noindex,nofollow".
	 * 
	 * @return string the value for the meta-tag "follow"
	 */
	public function get_robots_value()
	{
		return "noindex,nofollow";
	}
	
	protected final function _report_error($type = PLIB_Messages::MSG_TYPE_ERROR,$message = '')
	{
		// if a no-access-message has been added we want to show the login form
		if($message == '' && $type == PLIB_Messages::MSG_TYPE_NO_ACCESS)
			$this->functions->show_login_form();
		else
			parent::_report_error($type,$message);
	}
	
	/**
	 * adds the forum-location-path for given id to the given array
	 * can be used in modules which belong to a forum or topic
	 * 
	 * @param array $result the path-array to which the forum-path should be added
	 * @param int $id the forum-id
	 */
	protected final function _add_loc_forum_path(&$result,$id)
	{
		if(PLIB_Helper::is_integer($id) && $id > 0)
		{
			$path = $this->forums->get_path($id);
			for($i = count($path) - 1;$i >= 0;$i--)
				$result[$path[$i][0]] = $this->url->get_topics_url($path[$i][1],'&amp;',1);
		}
	}
	
	/**
	 * adds the topic-location to the given array
	 * can be used in modules which belong to a topic
	 * 
	 * @param array $result the path-array to which the topic should be added
	 */
	protected final function _add_loc_topic(&$result)
	{
		$topic = $this->cache->get_cache('topic');
		if($topic !== null)
		{
			$tdata = $topic->current();
			$url = $this->url->get_posts_url($tdata['rubrikid'],$tdata['id'],'&amp;',1);
			$result[$tdata['name']] = $url;
		}
	}
}
?>