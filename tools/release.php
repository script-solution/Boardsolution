<?php
function getUsage()
{
	return 'Usage: php release.php <targetFolder> [--no-stats]'."\n";
}

define('BS_PATH','../');
define('FWS_PATH',BS_PATH.'fws/');
include(FWS_PATH.'init.php');

$in = FWS_Input::get_instance();
$argc = $in->get_var('argc','server',FWS_Input::INTEGER);
$argv = $in->get_var('argv','server');
if($argc < 2)
	die("Missing parameter\n".getUsage());

$folder = null;
$stats = true;
for($i = 1; $i < $argc; $i++)
{
	if($argv[$i] == '--no-stats')
		$stats = false;
	else
		$folder = FWS_FileUtils::ensure_trailing_slash($argv[$i]);
}

if($folder === null || !is_dir($folder) || !FWS_FileUtils::is_writable($folder))
	die('Folder "'.$folder.'" does not exist, is no folder or is not writable!'."\n");

// copy
echo 'Copying files and folders...'."\n";
$items = array(
	'acp','bbceditor','config','dba','extern','front','images','install','language','fws',
	'src','tools','themes','uploads','.htaccess','admin.php','index.php','_install.php','standalone.php',
	'LICENSE','README.md'
);
foreach($items as $item)
{
	if(is_dir(BS_PATH.$item))
		FWS_FileUtils::copy_folder(BS_PATH.$item, $folder.'/'.$item);
	else
		FWS_FileUtils::copy(BS_PATH.$item, $folder.'/'.$item);
}

// rename install.php and remove unnecessary stuff
rename($folder.'_install.php', $folder.'install.php');
FWS_FileUtils::delete_folder($folder.'themes/joomla');
FWS_FileUtils::delete_folder($folder.'fws/tests');
if(is_dir($folder.'fws/.git'))
	FWS_FileUtils::delete_folder($folder.'fws/.git');
else
	unlink($folder.'fws/.git');
@unlink($folder.'fws/.project');
@unlink($folder.'fws/.buildpath');
unlink($folder.'fws/.gitignore');
unlink($folder.'tools/release.php');

// create missing dirs
echo 'Creating missing stuff...'."\n";
@mkdir($folder.'cache');
@chmod($folder.'cache',0777);
@mkdir($folder.'uploads');
@chmod($folder.'uploads',0777);
@chmod($folder.'images/smileys',0777);
@chmod($folder.'images/avatars',0777);
@chmod($folder.'config',0777);
@chmod($folder.'dba',0777);

// create some missing files
$content = "<html>
<body>
</body>
</html>";
foreach(FWS_FileUtils::get_list($folder,true,true) as $item)
{
	if(is_dir($item) && $item != $folder.'uploads')
	{
		if(!in_array('index.htm',FWS_FileUtils::get_list($item,false,false)))
		{
			echo "Creating ".$item.'/index.htm...'."\n";
			FWS_FileUtils::write($item.'/index.htm',$content);
		}
	}
}

FWS_FileUtils::write($folder.'uploads/.htaccess', "deny from all\n");

// delete files that will be generated during installation
unlink($folder.'config/mysql.php');
unlink($folder.'dba/access.php');

// empty backups-folder except 2 files
FWS_FileUtils::delete_folder($folder.'dba/backups');
mkdir($folder.'dba/backups');
@chmod($folder.'dba/backups',0777);
FWS_FileUtils::write($folder.'dba/backups/backups.txt', '');
FWS_FileUtils::write($folder.'dba/backups/.htaccess', "deny from all\n");

// change path to fws
echo 'Changing stuff in userdef.php...'."\n";
$userdef = FWS_FileUtils::read($folder.'config/userdef.php');
$userdef = preg_replace(
	'/define\(\'BS_FWS_PATH\',\'.*?\'\);/','define(\'BS_FWS_PATH\',\'fws/\');',$userdef
);
$userdef = preg_replace(
	'/define\(\'BS_USE_TRANSACTIONS\',.*?\);/','define(\'BS_USE_TRANSACTIONS\',false);',$userdef
);
$userdef = preg_replace(
	'/define\(\'BS_DEBUG\',.*?\);/','define(\'BS_DEBUG\',1);',$userdef
);
FWS_FileUtils::write($folder.'config/userdef.php',$userdef);

// change .htaccess
$htaccess = FWS_FileUtils::read($folder.'.htaccess');
$htaccess = preg_replace('/RewriteBase\s+[^\s]+/','RewriteBase /',$htaccess);
FWS_FileUtils::write($folder.'.htaccess.txt',$htaccess);
unlink($folder.'.htaccess');

@include_once(BS_PATH.'config/general.php');

// generate zip-file
echo 'Generating zip-file...'."\n";
FWS_FileUtils::zip_folder($folder, $folder.'/boardsolution_'.BS_VERSION_ID.'.zip');

// generate version-xml
echo 'Generating version-info-file...'."\n";
$res = BS_VERSION."\n";
$res .= date('Y-m-d')."\n";
$res .= 'release'."\n";

$folders = array();
$files = array();

$foldernts = $folder;
FWS_FileUtils::ensure_no_trailing_slash($foldernts);
$paths = FWS_FileUtils::get_list($folder,true,true);
foreach($paths as $item)
{
	if(is_file($item) && !FWS_String::ends_with($item,'boardsolution_'.BS_VERSION_ID.'.zip'))
	{
		$hash = md5_file($item);
		$itemfolder = preg_replace('/^'.preg_quote($foldernts,'/').'\/?(.*)/','\\1',dirname($item));
		if($itemfolder == '')
			$itemfolder = '.';
		if(!isset($folders[$itemfolder]))
			$folders[$itemfolder] = count($folders) + 1;
		$pre = '$'.$folders[$itemfolder];
		$files[$pre.'/'.basename($item)] = $hash;
	}
}

foreach($folders as $name => $id)
	$res .= '$'.$id.'='.$name."\n";
foreach($files as $name => $hash)
	$res .= $hash.' '.$name."\n";

$target = $folder.'v'.BS_VERSION_ID.'.txt';
FWS_FileUtils::write($target,$res);

if($stats)
{
	// calculate line-number-statistics
	echo 'Creating statistics...'."\n";
	exec(
		'cloc --quiet '.$folder.' > '.$folder.'stats.txt'
	);
}
?>