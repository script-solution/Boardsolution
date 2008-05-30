<?php
/**
 * Contains the show-tables-module
 * 
 * @version			$Id: module_index.php 676 2008-05-08 09:02:28Z nasmussen $
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
	public function get_actions()
	{
		return array(
			BS_DBA_ACTION_DELETE_TABLES => 'delete',
			BS_DBA_ACTION_OPTIMIZE_TABLES => 'optimize'
		);
	}
	
	public function run()
	{
		unset($_SESSION['BS_restore']);
		unset($_SESSION['BS_backup']);
		
		$mode = $this->input->get_var('mode','get',PLIB_Input::STRING);
		
		// show delete message?
		if($mode == 'qdelete')
		{
			$stables = $this->input->get_var('tables','get',PLIB_Input::STRING);
			$tables = PLIB_Array_Utils::advanced_explode(';',$stables);
			if(count($tables) > 0)
			{
				$message = sprintf(
					$this->locale->lang('delete_tables_question'),'"'.implode('", "',$tables).'"'
				);
				$yes_url = $this->url->get_url(
					0,'&amp;at='.BS_DBA_ACTION_DELETE_TABLES.'&amp;tables='.implode(';',$tables)
				);
				$no_url = $this->url->get_url(0);
				
				$this->functions->add_delete_message($message,$yes_url,$no_url,'');
			}
		}
		else if($mode == 'optimize')
			$this->_optimize_tables();
		
		// retrieve tables in the database
		$selected_db = BS_DBA_Utils::get_instance()->get_selected_database();
		$qry = $this->db->sql_qry('SHOW TABLE STATUS FROM `'.$selected_db.'`',false);
		
		$this->tpl->add_variables(array(
			'error_msg' => $qry ? '' : $this->db->last_error()
		));
		
		$total_overhead = 0;
		$total_size = 0;
		$total_rows = 0;
		$tables = array();
		if($qry)
		{
			while($data = $this->db->sql_fetch_assoc($qry))
			{
				$size = $data['Data_length'] + $data['Index_length'] + $data['Data_free'];
				$total_size += $size;
				$total_rows += $data['Rows'];
				$total_overhead += $data['Data_free'];
				
				$create_date = BS_DBA_Utils::get_instance()->mysql_date_to_time($data['Create_time']);
				
				if($create_date >= 0)
					$create_date = PLIB_Date::get_date($create_date);
				else
					$create_date = $this->locale->lang('notavailable');
				
				$tables[] = array(
					'name' => $data['Name'],
					'size' => number_format($size,0,',','.').' Bytes',
					'entries' => $data['Rows'] ? $data['Rows'] : '0',
					'date_created' => $create_date,
					'overhead' => number_format($data['Data_free'],0,',','.'). ' Bytes'
				);
			}
		}
		
		$this->tpl->add_array('tables',$tables);
		$this->_request_formular();
		
		$actions = array(
			'' => $this->locale->lang('please_choose'),
			'backup' => $this->locale->lang('backup'),
			'optimize' => $this->locale->lang('optimize'),
			'delete' => $this->locale->lang('delete')
		);
		$action_combo = new PLIB_HTML_ComboBox('action','action');
		$action_combo->set_options($actions);
		$action_combo->set_custom_attribute('onchange','performAction(this);');
		
		$this->tpl->add_variables(array(
			'size' => PLIB_StringHelper::get_formated_data_size($total_size,BS_DBA_LANGUAGE),
			'overhead' => PLIB_StringHelper::get_formated_data_size($total_overhead,BS_DBA_LANGUAGE),
			'entries' => number_format($total_rows,0,',','.'),
			'action_combo' => $action_combo->to_html(),
			'optimize_url' => $this->url->get_url(0,'&at='.BS_DBA_ACTION_OPTIMIZE_TABLES.'&tables=','&')
		));
	}
}
?>