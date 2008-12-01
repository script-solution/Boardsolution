<?php
/**
 * Contains the pmsearch-userprofile-submodule
 * 
 * @version			$Id$
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
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_Front_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$locale = FWS_Props::get()->locale();
		$renderer = $doc->use_default_renderer();
		
		$renderer->add_action(BS_ACTION_DELETE_PMS,'deletepms');

		$renderer->add_breadcrumb($locale->lang('pm_search'),BS_URL::build_sub_url());
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$tpl = FWS_Props::get()->tpl();
		$locale = FWS_Props::get()->locale();

		$id = $input->get_var(BS_URL_ID,'get',FWS_Input::STRING);
		$modes = array('pms','history');
		$mode = $input->correct_var(BS_URL_MODE,'get',FWS_Input::STRING,$modes,'pms');
		
		$submitted = $input->isset_var('submit','post');
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
		
			$tpl->add_variables(array(
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
				$delete = $input->get_var('delete','post');
				$operation = $input->get_var('operation','post',FWS_Input::STRING);
				if($operation == 'delete' && $delete != null && FWS_Array_Utils::is_integer($delete))
				{
					$site = $input->get_var(BS_URL_SITE,'get',FWS_Input::INTEGER);
					if(!$site)
						$site = 1;
					$url = BS_URL::get_sub_url(0,'pmsearch');
					$url->set(BS_URL_ID,$id);
					$url->set(BS_URL_SITE,$site);
					$back_url = $url->to_url();
					BS_Front_Module_UserProfile_Helper::get_instance()->add_pm_delete_message($back_url);
				}
			}
			
			$manager->add_result();
		}
		// display the search-form
		else
		{
			$order = $input->correct_var(
				'order','post',FWS_Input::STRING,array('date','subject','folder'),'date'
			);
			$ad = $input->correct_var(
				'ad','post',FWS_Input::STRING,array('ASC','DESC'),'DESC'
			);
			
			$form = new BS_HTML_Formular();
			$keyword_mode_options = array(
				'and' => $locale->lang('keyword_mode_and'),
				'or' => $locale->lang('keyword_mode_or')
			);

			$order_options = array(
				'date' => $locale->lang('date'),
				'subject' => $locale->lang('subject'),
				'folder' => $locale->lang('folder')
			);

			$ad_options = array(
				'DESC' => $locale->lang('descending'),
				'ASC' => $locale->lang('ascending')
			);

			$keyword_mode_value = $form->get_input_value('keyword_mode','and');
			$keyword = stripslashes($input->get_var('keyword','post',FWS_Input::STRING));
			$username = stripslashes($input->get_var('un','post',FWS_Input::STRING));

			$faqurl = BS_URL::get_mod_url('faq');
			$faqurl->set_anchor('f_9');
			$tpl->add_variables(array(
				'action_param' => BS_URL_ACTION,
				'search_explain_keyword' => sprintf(
					$locale->lang('search_explain_keyword'),$faqurl->to_url(),BS_SEARCH_MIN_KEYWORD_LEN
				),
				'search_explain_user' => sprintf($locale->lang('search_explain_user'),$faqurl->to_url()),
				'target_url' => BS_URL::build_sub_url(),
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
}
?>