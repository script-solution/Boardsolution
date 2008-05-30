<?php
/**
 * Contains the messages-task for the miscellaneous module
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The task to refresh the texts of messages
 *
 * @package			Boardsolution
 * @subpackage	acp.module
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_ACP_Miscellaneous_Tasks_Messages extends PLIB_FullObject implements PLIB_Progress_Task
{
	/**
	 * The number of posts
	 *
	 * @var int
	 */
	private $_posts;
	
	/**
	 * The number of pms
	 *
	 * @var int
	 */
	private $_pms;
	
	/**
	 * The number of signatures
	 *
	 * @var int
	 */
	private $_sigs;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->_pms = BS_DAO::get_pms()->get_count();
		$this->_posts = BS_DAO::get_posts()->get_count();
		$this->_sigs = BS_DAO::get_user()->get_user_count();
	}
	
	/**
	 * @see PLIB_Progress_Task::get_total_operations()
	 *
	 * @return int
	 */
	public function get_total_operations()
	{
		return max($this->_pms,$this->_posts,$this->_sigs);
	}

	/**
	 * @see PLIB_Progress_Task::run()
	 *
	 * @param int $pos
	 * @param int $ops
	 */
	public function run($pos,$ops)
	{
		// we can refresh the linklist-descriptions in one step
		if($pos == 0)
		{
			foreach(BS_DAO::get_links()->get_all() as $data)
			{
				$text = '';
				BS_PostingUtils::get_instance()->prepare_message_for_db(
					$text,addslashes($data['link_desc_posted']),'lnkdesc'
				);
				
				BS_DAO::get_links()->update_text($data['id'],$text,$data['link_desc_posted']);
			}
		}
		
		// refresh posts
		if($pos < $this->_posts)
		{
			foreach(BS_DAO::get_posts()->get_all('id','ASC',$pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::get_instance()->prepare_message_for_db(
					$text,addslashes($data['text_posted']),'posts',$data['use_smileys'],$data['use_bbcode']
				);
				
				BS_DAO::get_posts()->update($data['id'],array('text' => $text));
			}
		}

		// refresh PMs
		if($pos < $this->_pms)
		{
			foreach(BS_DAO::get_pms()->get_all('id','ASC',$pos,$ops) as $data)
			{
				$text = '';
				BS_PostingUtils::get_instance()->prepare_message_for_db(
					$text,addslashes($data['pm_text_posted']),'posts',1,1
				);
				
				BS_DAO::get_pms()->update_text($data['id'],$text,$data['pm_text_posted']);
			}
		}

		if($pos < $this->_sigs)
		{
			foreach(BS_DAO::get_profile()->get_users('p.id','ASC',$pos,$ops,-1,-1) as $data)
			{
				$text = '';
				BS_PostingUtils::get_instance()->prepare_message_for_db(
					$text,addslashes($data['signature_posted']),'sig'
				);
				
				BS_DAO::get_profile()->update_user_by_id(array('signatur' => $text),$data['id']);
			}
		}
	}
	
	protected function _get_print_vars()
	{
		return get_object_vars($this);
	}
}
?>