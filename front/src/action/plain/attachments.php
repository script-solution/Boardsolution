<?php
/**
 * Contains the plain-attachments-action-class
 *
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */

/**
 * The plain-action to add already uploaded (!) attachments
 *
 * @package			Boardsolution
 * @subpackage	front.src.action
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Front_Action_Plain_Attachments extends BS_Front_Action_Plain
{
	/**
	 * Returns the default instance (variables read from POST and the current user) of this
	 * class. Assumes that you can set_target()!
	 *
	 * @param int $post_id The id of the post (for attachments in posts)
	 * @param int $topic_id The id of the topic (for attachments in posts)
	 * @param int $pm_id The id of the PM (for attachments in PMs)
	 * @return BS_Front_Action_Plain_Attachments the attachments-object
	 */
	public static function get_default($post_id = 0,$topic_id = 0,$pm_id = 0)
	{
		$input = FWS_Props::get()->input();
		$user = FWS_Props::get()->user();
		
		$file_paths = $input->get_var('attached_file_paths','post');
		return new BS_Front_Action_Plain_Attachments(
			$file_paths,$user->get_user_id(),$post_id,$topic_id,$pm_id
		);
	}
	
	/**
	 * All file-paths to upload
	 *
	 * @var array
	 */
	private $_file_paths;
	
	/**
	 * The id of the user
	 *
	 * @var int
	 */
	private $_user_id;
	
	/**
	 * The id of the post (for attachments in posts)
	 *
	 * @var int
	 */
	private $_post_id;
	
	/**
	 * The id of the PM (for attachments in PMs)
	 *
	 * @var int
	 */
	private $_pm_id;
	
	/**
	 * The id of the topic (for attachments in posts)
	 *
	 * @var int
	 */
	private $_topic_id;
	
	/**
	 * The number of attachments we really want to insert
	 *
	 * @var int
	 */
	private $_limit = null;
	
	/**
	 * The number of inserted attachments
	 *
	 * @var int
	 */
	private $_count = 0;
	
	/**
	 * Constructor. Note that  the post-, topic- and pm-id will not be checked!
	 * 
	 * @param array $file_paths an array with all paths to upload
	 * @param int $user_id The id of the user
	 * @param int $post_id The id of the post (for attachments in posts)
	 * @param int $topic_id The id of the topic (for attachments in posts)
	 * @param int $pm_id The id of the PM (for attachments in PMs)
	 */
	public function __construct($file_paths,$user_id,$post_id = 0,$topic_id = 0,$pm_id = 0)
	{
		parent::__construct();
		
		$this->_file_paths = $file_paths;
		$this->_user_id = (int)$user_id;
		$this->_post_id = (int)$post_id;
		$this->_topic_id = (int)$topic_id;
		$this->_pm_id = (int)$pm_id;
	}
	
	/**
	 * @return boolean wether attachments are available
	 */
	public function attachments_set()
	{
		return $this->_file_paths !== null;
	}
	
	/**
	 * @return int the number of inserted attachments (will be available after perform_action())
	 */
	public function get_count()
	{
		return $this->_count;
	}
	
	/**
	 * Sets the target of the attachment. That means for which you want to store it (post or PM).
	 * Note that this will NOT be checked!
	 *
	 * @param int $post_id the post-id
	 * @param int $topic_id the topic-id
	 * @param int $pm_id the pm-id
	 * @param int $user_id the user-id
	 */
	public function set_target($post_id,$topic_id,$pm_id = -1,$user_id = -1)
	{
		$this->_post_id = $post_id;
		$this->_topic_id = $topic_id;
		if($pm_id != -1)
			$this->_pm_id = $pm_id;
		if($user_id != -1)
			$this->_user_id = $user_id;
	}
	
	public function check_data()
	{
		$cfg = FWS_Props::get()->cfg();
		$user = FWS_Props::get()->user();

		// attachments allowed?
		if($cfg['attachments_enable'] == 0)
			return 'Attachments disabled';
		
		// check the user-id if it is not the current one
		if($user->get_user_id() != $this->_user_id)
		{
			$data = BS_DAO::get_user()->get_user_by_id($this->_user_id);
			if($data === false)
				return 'A user with id "'.$this->_user_id.'" does not exist';
		}
	
		// input valid?
		$attach_num = 0;
		if($this->_file_paths == null || ($attach_num = count($this->_file_paths)) == 0)
			return 'No attachments specified';
	
		// have we already reached the maximum?
		if($cfg['attachments_max_number'] > 0)
		{
			$global_num = BS_DAO::get_attachments()->get_attachment_count();
			if($global_num + $attach_num > $cfg['attachments_max_number'])
				return 'Global max. attachments reached';
		}
	
		// check the number of attachments of the user
		$user_attach_num = BS_DAO::get_attachments()->get_attachment_count_of_user($this->_user_id);
		$user_limit = $cfg['attachments_per_user'];
		if($user_limit > 0 && $user_attach_num + $attach_num >= $user_limit)
			return 'Max. user-attachments reached';
	
		// ensure that we insert not more than the maximum attachments per post
		if($cfg['attachments_max_per_post'] > 0)
			$this->_limit = min($attach_num,$cfg['attachments_max_per_post']);
		else
			$this->_limit = $attach_num;
		
		return parent::check_data();
	}
	
	public function perform_action()
	{
		$db = FWS_Props::get()->db();
		$cfg = FWS_Props::get()->cfg();

		parent::perform_action();
		
		$db->start_transaction();
	
		$this->_count = 0;
		for($i = 0;$i < $this->_limit;$i++)
		{
			// is the path-beginning valid?
			FWS_FileUtils::clean_path($this->_file_paths[$i]);
			if(FWS_String::substr($this->_file_paths[$i],0,8) != 'uploads/')
				continue;
	
			// the file has to exist
			if(!is_file(FWS_Path::server_app().$this->_file_paths[$i]))
				continue;
	
			// does db-entry exist? (do not check for PMs since there are 2 for one attachment)
			if($this->_pm_id == 0)
			{
				$existing = BS_DAO::get_attachments()->get_attachment_count_of_path($this->_file_paths[$i]);
				if($existing > 0)
					continue;
			}
	
			// is the size valid?
			$filesize = @filesize(FWS_Path::server_app().$this->_file_paths[$i]);
			if($cfg['attachments_max_filesize'] > 0 &&
				($filesize / 1024) > $cfg['attachments_max_filesize'])
				continue;
	
			// insert the attachment
			BS_DAO::get_attachments()->create(
				$this->_post_id,$this->_topic_id,$this->_pm_id,$this->_user_id,$filesize,$this->_file_paths[$i]
			);
			$this->_count++;
		}
		
		$db->commit_transaction();
	}
	
	protected function get_dump_vars()
	{
		return get_object_vars($this);
	}
}
?>