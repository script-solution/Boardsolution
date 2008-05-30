<?php
/**
 * Contains the pmsearch-userprofile-submodule
 * 
 * @version			$Id: sub_pmsearch.php 765 2008-05-24 21:14:51Z nasmussen $
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The pmsearch submodule for module userprofile
 * 
 * @package			Boardsolution
 * @subpackage	front.modules
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_SubModule_userprofile_pmsearch extends BS_Front_SubModule
{
	public function get_actions()
	{
		return array(
			BS_ACTION_DELETE_PMS => 'deletepms'
		);
	}
	
	public function run()
	{
		$id = $this->input->get_var(BS_URL_ID,'get',PLIB_Input::STRING);
		$modes = array('pms','history');
		$mode = $this->input->correct_var(BS_URL_MODE,'get',PLIB_Input::STRING,$modes,'pms');
		
		$submitted = $this->input->isset_var('submit','post');
		if($mode != 'pms' || $submitted || $id != null)
		{
			switch($mode)
			{
				case 'history':
					$request = new BS_Front_Search_Request_PMHistory();
					break;
				default:
					$request = new BS_Front_Search_Request_PMSearch();
					break;
			}
			
			$manager = new BS_Front_Search_Manager($id,$request);
			$result_num = count($manager->get_result_ids());
			$result = $request->get_result();
		
			$this->tpl->add_variables(array(
				'result_tpl' => $result !== null ? $result->get_template() : '',
				'result_num' => $result_num
			));
		}
		else
			$result_num = 0;
		
		// display results
		if($result_num > 0)
		{
			if($mode == 'pms')
			{
				$delete = $this->input->get_var('delete','post');
				$operation = $this->input->get_var('operation','post',PLIB_Input::STRING);
				if($operation == 'delete' && $delete != null && PLIB_Array_Utils::is_integer($delete))
				{
					$site = $this->input->get_var(BS_URL_SITE,'get',PLIB_Input::INTEGER);
					if(!$site)
						$site = 1;
					$site_param = '&amp;'.BS_URL_SITE.'='.$site;
					$back_url = $this->url->get_url(
						0,'&amp;'.BS_URL_LOC.'=pmsearch&amp;'.BS_URL_ID.'='.$id.$site_param
					);
				
					BS_Front_Module_UserProfile_Helper::get_instance()->add_pm_delete_message($back_url);
				}
			}
			
			$manager->add_result();
		}
		// display the search-form
		else
		{
			$order = $this->input->correct_var(
				'order','post',PLIB_Input::STRING,array('date','subject','folder'),'date'
			);
			$ad = $this->input->correct_var(
				'ad','post',PLIB_Input::STRING,array('ASC','DESC'),'DESC'
			);
			
			$form = new BS_HTML_Formular();
			$keyword_mode_options = array(
				'and' => $this->locale->lang('keyword_mode_and'),
				'or' => $this->locale->lang('keyword_mode_or')
			);

			$order_options = array(
				'date' => $this->locale->lang('date'),
				'subject' => $this->locale->lang('subject'),
				'folder' => $this->locale->lang('folder')
			);

			$ad_options = array(
				'DESC' => $this->locale->lang('descending'),
				'ASC' => $this->locale->lang('ascending')
			);

			$keyword_mode_value = $form->get_input_value('keyword_mode','and');
			$keyword = stripslashes($this->input->get_var('keyword','post',PLIB_Input::STRING));
			$username = stripslashes($this->input->get_var('un','post',PLIB_Input::STRING));

			$this->tpl->add_variables(array(
				'action_param' => BS_URL_ACTION,
				'search_explain_keyword' => sprintf(
					$this->locale->lang('search_explain_keyword'),
					$this->url->get_url('faq').'#f_9',
					BS_SEARCH_MIN_KEYWORD_LEN
				),
				'search_explain_user' => sprintf($this->locale->lang('search_explain_user'),$this->url->get_url('faq').'#f_9'),
				'target_url' => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pmsearch'),
				'keyword_mode_combo' => $form->get_radio_boxes(
					'keyword_mode',$keyword_mode_options,$keyword_mode_value
				),
				'order_combo' => $form->get_combobox('order',$order_options,$order),
				'ad_combo' => $form->get_combobox('ad',$ad_options,$ad),
				'keyword_value' => $keyword,
				'user_name_value' => $username
			));
		}
	}
	
	public function get_location()
	{
		return array(
			$this->locale->lang('pm_search') => $this->url->get_url(0,'&amp;'.BS_URL_LOC.'=pmsearch')
		);
	}
}
?>