<?php
/**
 * Contains the edit-submodule for forums
 * 
 * @version			$Id$
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
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_ACP_Page $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$url = FWS_Props::get()->url();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACP_ACTION_ADD_FORUM,array('edit','add'));
		$renderer->add_action(BS_ACP_ACTION_EDIT_FORUM,array('edit','edit'));

		$id = $input->get_var('id','get',FWS_Input::ID);
		if($id != null)
		{
			$renderer->add_breadcrumb(
				$locale->lang('edit_forum'),
				$url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id)
			);
		}
		else
		{
			$renderer->add_breadcrumb(
				$locale->lang('create_new_forum'),
				$url->get_acpmod_url(0,'&amp;action=edit')
			);
		}
		
		$this->_helper = BS_ACP_Module_Forums_Helper::get_instance();
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$url = FWS_Props::get()->url();
		$locale = FWS_Props::get()->locale();
		$auth = FWS_Props::get()->auth();
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();
		$forums = FWS_Props::get()->forums();

		$id = $input->get_var('id','get',FWS_Input::ID);
		$type = $id != null ? 'edit' : 'add';
		
		$this->request_formular();
		
		// retrieve default data
		if($type == 'add')
		{
			$data = new BS_Forums_NodeData(
				array('id' => 0,'forum_name' => '','parent_id' => 0,'sortierung' => 1)
			);
			$forum = $data->get_attributes();
			
			$target_url = $url->get_acpmod_url(0,'&amp;action=edit');
			$form_title = $locale->lang('add');
		}
		else
		{
			$forum = $forums->get_forum_data($id);
			
			$target_url = $url->get_acpmod_url(0,'&amp;action=edit&amp;id='.$id);
			$form_title = $locale->lang('edit');
		}
		
		// build some combos
		$nodes = $forums->get_all_nodes();
		$parent_combo = $this->_get_parent_combobox($nodes,$forum['id'],$forum['parent_id'],'parent');
		
		$this->_add_access_combo(
			'permission_thread',$auth->get_permissions_in_forum(BS_MODE_START_TOPIC,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_poll',$auth->get_permissions_in_forum(BS_MODE_START_POLL,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_event',$auth->get_permissions_in_forum(BS_MODE_START_EVENT,$forum['id'])
		);
		$this->_add_access_combo(
			'permission_post',$auth->get_permissions_in_forum(BS_MODE_REPLY,$forum['id'])
		);

		$forum_type_options = array(
			'contains_cats' => $locale->lang('contains_cats'),
			'contains_threads' => $locale->lang('contains_threads')
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
		
		$usercb = new FWS_HTML_ComboBox('user_intern','user_intern',null,array(),5,true);
		$usercb->set_options($options);
		$usercb->set_css_attribute('width','100%');
		
		// usergroups
		$usergroups = array();
		foreach($cache->get_cache('user_groups') as $gdata)
		{
			if($gdata['id'] == BS_STATUS_ADMIN || $gdata['id'] == BS_STATUS_GUEST)
				continue;

			$usergroups[] = array(
				'id' => $gdata['id'],
				'title' => $gdata['group_title'],
				'value' => isset($groups[$gdata['id']])
			);
		}
		
		$forumtype = new FWS_HTML_ComboBox('forum_type','forum_type',$forum['forum_type']);
		$forumtype->set_options($forum_type_options);
		$forumtype->set_custom_attribute('onchange','toggleForumType()');
		
		// populate template
		$tpl->add_array('default',$forum);
		$tpl->add_array('usergroups',$usergroups);
		$tpl->add_variables(array(
			'action_type' => $type == 'edit' ? BS_ACP_ACTION_EDIT_FORUM : BS_ACP_ACTION_ADD_FORUM,
			'target_url' => $target_url,
			'form_title' => $form_title,
			'parent_combo' => $parent_combo,
			'search_url' => $url->get_acpmod_url('usersearch','&amp;comboid=user_intern'),
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
		$locale = FWS_Props::get()->locale();

		$return = '<select name="'.$name.'">'."\n";
		$sel = ($parent_id == 0) ? ' selected="selected"' : '';
		$return .= '<option value="0"'.$sel.'>- '.$locale->lang('main_forum').' -</option>'."\n";
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
		$cache = FWS_Props::get()->cache();
		$tpl = FWS_Props::get()->tpl();

		$radios = array();
		foreach($cache->get_cache('user_groups') as $data)
		{
			if($data['id'] == BS_STATUS_ADMIN)
				continue;

			$radios[] = array(
				'id' => $data['id'],
				'title' => $data['group_title'],
				'value' => in_array($data['id'],$permissions)
			);
		}
		$tpl->add_array($type,$radios);
	}
}
?>