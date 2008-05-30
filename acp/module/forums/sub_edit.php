<?php
/**
 * Contains the edit-submodule for forums
 * 
 * @version			$Id: sub_edit.php 742 2008-05-24 12:18:23Z nasmussen $
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit sub-module for the forums-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_forums_edit extends BS_ACP_SubModule
{
	/**
	 * The forums-helper
	 *
	 * @var BS_ACP_Module_Forums_Helper
	 */
	private $_helper;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_helper = BS_ACP_Module_Forums_Helper::get_instance();
	}
	
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_ADD_FORUM => array('edit','add'),
			BS_ACP_ACTION_EDIT_FORUM => array('edit','edit')
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		$type = $id != null ? 'edit' : 'add';
		
		$this->_request_formular();
		
		// retrieve default data
		if($type == 'add')
		{
			$data = new BS_Forums_NodeData(
				array('id' => 0,'forum_name' => '','parent_id' => 0,'sortierung' => 1)
			);
			$forum = $data->get_attributes();
			
			$target_url = $this->url->get_acpmod_url(0,'&amp;action=edit');
			$form_title = $this->locale->lang('add');
		}
		else
		{
			$forum = $this->forums->get_forum_data($id);
			
			$target_url = $this->url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id);
			$form_title = $this->locale->lang('edit');
		}
		
		// build some combos
		$forums = $this->forums->get_all_nodes();
		$parent_combo = $this->_get_parent_combobox($forums,$forum['id'],$forum['parent_id'],'parent');
		
		$this->_add_access_combo(
			'permission_thread',$this->auth->get_permissions_in_forum(BS_MODE_START_TOPIC,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_poll',$this->auth->get_permissions_in_forum(BS_MODE_START_POLL,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_event',$this->auth->get_permissions_in_forum(BS_MODE_START_EVENT,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_post',$this->auth->get_permissions_in_forum(BS_MODE_REPLY,$forum['id'])
		);

		$forum_type_options = array(
			'contains_cats' => $this->locale->lang('contains_cats'),
			'contains_threads' => $this->locale->lang('contains_threads')
		);

		// user
		$groups = array();
		$options = array();
		
		if($type == 'edit')
		{
			foreach(BS_DAO::get_intern()->get_by_forum($id) as $udata)
			{
				if($udata['access_type'] == 'group')
					$groups[$udata['access_value']] = true;
				else
					$options[$udata['access_value']] = $udata['user_name'];
			}
		}
		
		$usercb = new PLIB_HTML_ComboBox('user_intern','user_intern',null,array(),5,true);
		$usercb->set_options($options);
		$usercb->set_css_attribute('width','100%');
		
		// usergroups
		$usergroups = array();
		foreach($this->cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] == BS_STATUS_ADMIN || $gdata['id'] == BS_STATUS_GUEST)
				continue;

			$usergroups[] = array(
				'id' => $gdata['id'],
				'title' => $gdata['group_title'],
				'value' => isset($groups[$gdata['id']])
			);
		}
		
		$forumtype = new PLIB_HTML_ComboBox('forum_type','forum_type',$forum['forum_type']);
		$forumtype->set_options($forum_type_options);
		$forumtype->set_custom_attribute('onchange','toggleForumType()');
		
		// populate template
		$this->tpl->add_array('default',$forum);
		$this->tpl->add_array('usergroups',$usergroups);
		$this->tpl->add_variables(array(
			'action_type' => $type == 'edit' ? BS_ACP_ACTION_EDIT_FORUM : BS_ACP_ACTION_ADD_FORUM,
			'target_url' => $target_url,
			'form_title' => $form_title,
			'parent_combo' => $parent_combo,
			'search_url' => $this->url->get_standalone_url('acp','user_search','&amp;comboid=user_intern'),
			'user_combo' => $usercb->to_html(),
			'forum_type_combo' => $forumtype->to_html()
		));
	}

	/**
	 * generates a combobox for the parent-forum-selection
	 *
	 * @param array $forums an numeric array with the forums
	 * @param int $fid the id of the forum
	 * @param int $parent_id the id of the parent-forum
	 * @param string $name the name of the combobox
	 * @return string the combobox
	 */
	private function _get_parent_combobox($forums,$fid,$parent_id,$name)
	{
		$return = '<select name="'.$name.'">'."\n";
		$sel = ($parent_id == 0) ? ' selected="selected"' : '';
		$return .= '<option value="0"'.$sel.'>- '.$this->locale->lang('main_forum').' -</option>'."\n";
		$num = count($forums);
		for($i = 0;$i < $num;$i++)
		{
			$node = $forums[$i];
			$data = $node->get_data();
			$id = $data->get_id();
			$space = str_repeat('---',$node->get_layer());

			if($id != $fid)
			{
				if($fid > 0 && !$this->_helper->is_no_sub_category($fid,$id))
					$disabled = ' disabled="disabled" style="color: #AAAAAA;"';
				else
					$disabled = '';
				$sel = ($id == $parent_id) ? ' selected="selected"' : '';
			}
			else
			{
				$disabled = ' disabled="disabled" style="color: #AAAAAA;"';
				$sel = '';
			}

			$return .= '<option value="'.$id.'"'.$sel.$disabled.'>';
			$return .= $space.' '.$data->get_name().'</option>'."\n";
		}
		$return .= '</select>'."\n";

		return $return;
	}

	/**
	 * Adds the combobox for the forum-access setting to the template
	 *
	 * @param string $type the type of permission
	 * @param array $permissions the permissions of the forum
	 */
	private function _add_access_combo($type,$permissions = array())
	{
		$radios = array();
		foreach($this->cache->get_cache('user_groups') as $data)
		{
			if($data['id'] == BS_STATUS_ADMIN)
				continue;

			$radios[] = array(
				'id' => $data['id'],
				'title' => $data['group_title'],
				'value' => in_array($data['id'],$permissions)
			);
		}
		$this->tpl->add_array($type,$radios);
	}
	
	public function get_location()
	{
		$id = $this->input->get_var('id','get',PLIB_Input::ID);
		if($id != null)
		{
			return array(
				$this->locale->lang('edit_forum') => $this->url->get_acpmod_url(
					0,'&amp;action=edit&amp;id='.$id
				)
			);
		}
		
		return array(
			$this->locale->lang('create_new_forum') => $this->url->get_acpmod_url(0,'&amp;action=edit')
		);
	}
}
?>