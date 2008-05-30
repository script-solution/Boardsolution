<?php
/**
 * Contains the edit-submodule for tpleditor
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The edit sub-module for the tpleditor-module
 * 
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_SubModule_tpleditor_edit extends BS_ACP_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACP_ACTION_EDIT_TPL => 'edit'
		);
	}
	
	public function run()
	{
		$file = $this->input->get_var('file','get',PLIB_Input::STRING);
		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$def_path = $helper->get_path_in_default();
		
		if(is_file($path.'/'.$file))
			$real_file = $path.'/'.$file;
		else
			$real_file = $def_path.'/'.$file;

		if(!is_file($real_file))
		{
			$this->_report_error();
			return;
		}
		
		$target_url = $this->url->get_acpmod_url(
			0,'&amp;action=edit&amp;path='.$path.'&amp;file='.$file.'&amp;at='.BS_ACP_ACTION_EDIT_TPL
		);
		
		$file_content = file_get_contents($real_file);
		$file_content = htmlspecialchars($file_content);
		$file_content = str_replace('&#123;','{',$file_content);
		$file_content = str_replace('&#125;','}',$file_content);
		
		$cpath = '';
		$path_links = '';
		foreach(PLIB_Array_Utils::advanced_explode('/',$path) as $part)
		{
			$cpath .= $part.'/';
			$url = $this->url->get_acpmod_url('tpleditor','&amp;path='.$cpath);
			$path_links .= '<a href="'.$url.'">'.$part.'</a>/';
		}
		
		$this->tpl->set_template('tpleditor_formular.htm');
		$this->tpl->add_variables(array(
			'target_url' => $target_url,
			'image' => BS_ACP_Utils::get_instance()->get_file_image($real_file),
			'filename' => $path_links.$file,
			'filesize' => number_format(filesize($real_file),0,',','.'),
			'last_modification' => PLIB_Date::get_date(filemtime($real_file)),
			'file_content' => $file_content,
			'back_url' => $this->url->get_acpmod_url(0,'&amp;action=view&amp;path='.$path),
			'back_button' => true
		));
		$this->tpl->restore_template();
	}
	
	public function get_location()
	{
		$helper = BS_ACP_Module_TplEditor_Helper::get_instance();
		$path = $helper->get_path();
		$file = $this->input->get_var('file','get',PLIB_Input::STRING);
		return array(
			$this->locale->lang('edit') => $this->url->get_acpmod_url(
				0,'&amp;action=edit&amp;path='.$path.'&amp;file='.$file
			)
		);
	}
}
?>