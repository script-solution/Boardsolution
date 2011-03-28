<?php
/**
 * Contains the attachment-task
 * 
 * @version			$Id$
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 * @copyright		2003-2008 Nils Asmussen
 * @link				http://www.script-solution.de
 */
 
/**
 * The task which deletes "dead" attachments
 * 
 * @package			Boardsolution
 * @subpackage	src.tasks
 * @author			Nils Asmussen <nils@script-solution.de>
 */
final class BS_Tasks_attachments extends FWS_Tasks_Base
{
	public function run()
	{
		if($handle = @opendir(FWS_Path::server_app().'uploads'))
		{
			// grab all attachments from the database
			$attachments = array();
			foreach(BS_DAO::get_attachments()->get_all() as $data)
				$attachments[$data['attachment_path']] = true;

			$now = time();
			$files = array();
			$all = array();
			while($file = readdir($handle))
			{
				if($file == '.' || $file == '..' || $file == '.htaccess')
					continue;

				// check if the attachments exists in the database
				if(!isset($attachments['uploads/'.$file]))
				{
					// don't delete attachment which have just been uploaded
					$last_mod = @filemtime('uploads/'.$file);
					if($last_mod !== false && ($now - $last_mod) > BS_ATTACHMENT_TIMEOUT)
						$files[] = $file;
				}

				$all[] = $file;
			}
			closedir($handle);

			// delete the dead files
			foreach($files as $file)
			{
				// check if it may be a thumbnail
				$matches = array();
				preg_match('/(.+)_thumb\.(jpeg|jpg|png)$/i',$file,$matches);

				// if the original-file should be deleted, too, we don't want to keep the thumbnail
				if(count($matches) && !in_array($matches[1].'.'.$matches[2],$files))
				{
					$ext = FWS_FileUtils::get_extension($file);
					$pos = FWS_String::strpos($file,'_thumb');
					$start = FWS_String::substr($file,0,$pos);
					// does the original picture exist?
					if(in_array($start.'.'.$ext,$all))
						continue;
				}

				@unlink(FWS_Path::server_app().'uploads/'.$file);
			}
		}
	}
}
?>