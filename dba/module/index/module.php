<?php
/**
 * Contains the show-tables-module
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The module to show the DB-tables
 * 
 * @package			Boardsolution
 * @subpackage	dba.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_DBA_Module_index extends BS_DBA_Module
{
	/**
	 * @see FWS_Module::init($doc)
	 *
	 * @param BS_DBA_Document $doc
	 */
	public function init($doc)
	{
		parent::init($doc);
		
		$renderer = $doc->use_default_renderer();
		$renderer->add_action(BS_DBA_ACTION_DELETE_TABLES,'delete');
		$renderer->add_action(BS_DBA_ACTION_OPTIMIZE_TABLES,'optimize');
	}
	
	/**
	 * @see FWS_Module::run()
	 */
	public function run()
	{
		$input = FWS_Props::get()->input();
		$locale = FWS_Props::get()->locale();
		$functions = FWS_Props::get()->functions();
		$db = FWS_Props::get()->db();
		$tpl = FWS_Props::get()->tpl();
		$user = FWS_Props::get()->user();
		
		$user->delete_session_data('BS_restore');
		$user->delete_session_data('BS_backup');
		
		$mode = $input->get_var('mode','get',FWS_Input::STRING);
		
		// show delete message?
		if($mode == 'qdelete')
		{
			$stables = $input->get_var('tables','get',FWS_Input::STRING);
			$tables = FWS_Array_Utils::advanced_explode(';',$stables);
			if(count($tables) > 0)
			{
				$message = sprintf(
					$locale->lang('delete_tables_question'),'"'.implode('", "',$tables).'"'
				);
				$yes_url = BS_DBA_URL::build_url(
					0,'&amp;aid='.BS_DBA_ACTION_DELETE_TABLES.'&amp;tables='.implode(';',$tables)
				);
				$no_url = BS_DBA_URL::build_url(0);
				
				$functions->add_delete_message($message,$yes_url,$no_url,'');
			}
		}
		
		// retrieve tables in the database
		$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
		$errmsg = '';
		try
		{
			$rows = $db->get_rows('SHOW TABLE STATUS FROM `'.$selected_db.'`');
		}
		catch(FWS_DB_Exception_QueryFailed $ex)
		{
			$errmsg = $ex->get_mysql_error();
			$rows = array();
		}
		
		$tpl->add_variables(array(
			'error_msg' => $errmsg
		));
		
		$total_overhead = 0;
		$total_size = 0;
		$total_rows = 0;
		$tables = array();
		if($rows !== false)
		{
			foreach($rows as $data)
			{
				$overhead = 0;
				$size = $data['Data_length'] + $data['Index_length'];
				if($data['Engine'] != 'InnoDB')
				{
					$size += $data['Data_free'];
					$overhead = $data['Data_free'];
				}
				$total_size += $size;
				$total_rows += $data['Rows'];
				$total_overhead += $overhead;
				
				$create_date = BS_DBA_Utils::get_instance()->mysql_date_to_time($data['Create_time']);
				
				if($create_date >= 0)
					$create_date = FWS_Date::get_date($create_date);
				else
					$create_date = $locale->lang('notavailable');
				
				$tables[] = array(
					'name' => $data['Name'],
					'size' => number_format($size,0,',','.').' Bytes',
					'entries' => $data['Rows'] ? $data['Rows'] : '0',
					'date_created' => $create_date,
					'overhead' => number_format($overhead,0,',','.'). ' Bytes'
				);
			}
		}
		
		$tpl->add_variable_ref('tables',$tables);
		$this->request_formular();
		
		$actions = array(
			'' => $locale->lang('please_choose'),
			'backup' => $locale->lang('backup'),
			'optimize' => $locale->lang('optimize'),
			'delete' => $locale->lang('delete')
		);
		$action_combo = new FWS_HTML_ComboBox('action','action');
		$action_combo->set_options($actions);
		$action_combo->set_custom_attribute('onchange','performAction(this);');
		
		$tpl->add_variables(array(
			'size' => FWS_StringHelper::get_formated_data_size($total_size,BS_DBA_LANGUAGE),
			'overhead' => FWS_StringHelper::get_formated_data_size($total_overhead,BS_DBA_LANGUAGE),
			'entries' => number_format($total_rows,0,',','.'),
			'action_combo' => $action_combo->to_html(),
			'optimize_url' => BS_DBA_URL::build_url(0,'&at='.BS_DBA_ACTION_OPTIMIZE_TABLES.'&tables=%s','&')
		));
	}
}
?>