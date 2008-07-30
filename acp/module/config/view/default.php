<?php
/**
 * Contains the default-config-view-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The view for the settings
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Module_Config_View_Default extends FWS_Object implements FWS_Config_View
{
	/**
	 * The items for the template
	 *
	 * @var array
	 */
	private $_items = array();
	
	/**
	 * The formular
	 *
	 * @var BS_HTML_Formular
	 */
	private $_form;
	
	/**
	 * The mode
	 * 
	 * @var string
	 */
	private $_mode;
	
	/**
	 * The number of affects-messages-settings
	 *
	 * @var int
	 */
	private $_affects_msgs_settings = 0;
	
	/**
	 * Constructor
	 * 
	 * @param string $mode the mode: default or search
	 */
	public function __construct($mode = 'default')
	{
		parent::__construct();
		
		if(!in_array($mode,array('default','search')))
			FWS_Helper::def_error('inarray','mode',array('default','search'),$mode);
		
		$this->_mode = $mode;
		$this->_form = new BS_HTML_Formular();
	}
	
	/**
	 * @return boolean wether any setting has been displayed that affects the messages
	 */
	public function has_affects_msgs_settings()
	{
		return $this->_affects_msgs_settings > 0;
	}
	
	/**
	 * @return array all items for the template
	 */
	public function get_items()
	{
		return $this->_items;
	}
	
	/**
	 * @see FWS_Config_View::begin_group()
	 *
	 * @param int $id
	 */
	public function begin_group($item,$group)
	{
		$locale = FWS_Props::get()->locale();

		// ignore the group for the first entry
		if($this->_mode != 'search' && count($this->_items) <= 1)
			return;
		
		$data = $item->get_data();
		if(!isset($this->_items[$data->get_id()]))
			$this->_items[$data->get_id()] = array();
		
		$a = &$this->_items[$data->get_id()];
		$a['show_sep'] = true;
		if($this->_mode == 'search')
		{
			$a['separator'] = '';
			$manager = BS_ACP_Module_Config_Helper::get_instance()->get_manager();
			if(($pid = $group->get_parent_id()) > 0)
			{
				$pgroup = $manager->get_group($pid);
				$a['separator'] .= $locale->lang($pgroup->get_title(),false);
			}
			if($pid > 0 && $group->get_title())
				$a['separator'] .= ' &raquo; ';
			$a['separator'] .= $locale->lang($group->get_title(),false);
		}
		else
			$a['separator'] = $locale->lang($group->get_title(),false);
	}

	public function end_group($item,$group)
	{
		// ignore
	}

	/**
	 * @see FWS_Config_View::show_item()
	 *
	 * @param FWS_Config_Item $item
	 */
	public function show_item($item)
	{
		$locale = FWS_Props::get()->locale();

		$data = $item->get_data();
		if(!isset($this->_items[$data->get_id()]))
			$this->_items[$data->get_id()] = array();
		
		$a = &$this->_items[$data->get_id()];
		$a['id'] = $data->get_id();
		$a['affects_messages'] = $data->get_affects_msgs();
		if($data->get_affects_msgs() > 0)
			$this->_affects_msgs_settings++;
		$a['is_default'] = $data->get_value() == $data->get_default();
		$a['content'] = $item->get_control($this->_form);
		$name = $data->get_title_name();
		$a['title'] = $locale->lang($name);
		if($locale->contains_lang($name.'_desc'))
			$a['description'] = $locale->lang($name.'_desc');
	}
	
	protected function get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>