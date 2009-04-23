<?php
	echo ini_get("memory_limit");
	ini_set("memory_limit", -1  );
	echo ini_get("memory_limit");
	$mem_trace=0;
	//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
	require("ndsinc.php");
	$target_path = "/opt/uploads/";
	$realname = $_FILES['uploadedfile']['name'];
	$upfile=$_FILES['uploadedfile']['tmp_name'];

	//exec("/usr/bin/cp $upfile '/opt/$realname'");

        $db = new PDO('mysql:host=ndsdb;dbname=nds', 'nds');
	//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 


	exec("/usr/bin/rm -rf $target_path");
	exec("/usr/bin/mkdir -p $target_path");


	// Determine the rom number from the filename
        if (($pos = preg_match("/^([0-9]{4})/", $realname, $matches)))
        {
                $romid=$matches[0];
                $loc = strpos($realname, $romid);
                $name=ltrim(substr($realname, $loc+4), " -");
        }

        $loc = strpos($name, '.7z');
        $name=rtrim(substr($name, 0, $loc));

	say("\n\n");
	say("Romid: $romid\n");
	say("Name: $name\n");

	//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
        $query = "select 1 ex from adv where romid = :romid";
        $sth = $db->prepare($query);
        $sth->execute(array(':romid' => $romid));
        $res = $sth->fetchAll();
        @$row = $res[0];
        $exists = $row['ex'];
	//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
	if ($exists == "1")
	{
		$cmd="/usr/local/bin/7z e -o$target_path $upfile ";
		say("Running: $cmd\n");
		system($cmd, $ret);
		//system($cmd, $ret);
		//var_dump($res);
		var_dump($ret);

//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 

		$dir=$target_path;
		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					    say("filename: $file : filetype: " . filetype($dir . $file) . "\n");

					if ($file == "$romid - Cover.png")
					{
						say("Found cover\n");
						$res = insert_blob($dir . $file, "cover", $romid);
						var_dump($db->errorInfo());
//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
					}

					if ($file == "$romid - Icon.png")
					{
						say("Found Icon\n");
						$res = insert_blob($dir . $file, "icon", $romid);
						var_dump($db->errorInfo());
//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
					}

					if ($file == "$romid - InGame.png")
					{
						say("Found InGame\n");
						$res = insert_blob($dir . $file, "ingame", $romid);
						var_dump($db->errorInfo());
//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
					}

					if (substr($file, strlen($file)-3) == "nds")
					{
						say("Found ROM\n");
						$res = insert_blob($dir . $file, "rom", $romid);
						var_dump($db->errorInfo());
//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
					}

					if (substr($file, strlen($file)-3) == "nfo")
					{
						say("Found NFO\n");
						$res = insert_blob($dir . $file, "info", $romid);
						var_dump($db->errorInfo());
//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 
					}


				}
				closedir($dh);
			}
		}
	}
	else
		say("ROM not found in ADV table... skipping\n");

	//say("At point " . $mem_trace++ . ": Memory footprint: " . memory_get_usage()/1024 . "\n"); 


	flush();
	ob_flush();
	//@exec("kill ".getmypid());

function fetchname($pattern, $zipfile)
{
	say("Running: /usr/local/bin/7z l -slt $zipfile $pattern | grep Path");
	exec("/usr/local/bin/7z l -slt $zipfile \"$pattern\" | grep Path", $res, $ret);
	if ($ret == 0)
		$retval = substr($res[0], 7);

	return($retval);
}


function say($str)
{
	echo $str;
	@ob_flush();
	flush();
}

?>
