#!/usr/local/bin/php -q
<?php
	require("ndsinc.php");
	ini_set("memory_limit", -1  );
	$target_path = "/opt/uploads/";

        $db = new PDO('mysql:host=ndsdb;dbname=nds', 'nds');

	$count=0;

	$last_processed=0;
	$last_processed=3000;

	$query="select romid, romcrc from adv where romid >= $last_processed order by romid";
	foreach($db->query($query) as $row)
	{
		$romid = $row['romid'];
		echo "Checking $romid\n";
		$romcrc = ltrim($row['romcrc'], '0');
		$query="select sum from blobdata where id=$romid and type='rom'";
		//$query="select id from blobdata where id=$romid and type='unknown'";
		$res = $db->query($query)->fetch();
                $sum = $res['sum'];

		if ($sum != $romcrc)
		{
			echo "sum: $sum != $romcrc\n";
			locate_update($romid);
			$count++;
		}
	}
function locate_update($romid)
{
	global $target_path;
	global $db;

	exec("/usr/bin/rm -rf $target_path 2> /dev/null");
	exec("/usr/bin/mkdir -p $target_path 2> /dev/null");
	exec("/usr/bin/chmod -R a+rw $target_path 2> /dev/null");

	echo "Need to update: ($romid)\n";
	exec("/usr/bin/ls /nds/*/$romid* 2> /dev/null", $name);
	$file = $name[0];
	if ($file == "")
	{
		echo "Archive not found!\n";
		return;
	}
	//$file = addslashes($file);
	echo "Found: $file\n";
	$cmd="/usr/local/bin/7z e -o$target_path \"$file\" ";
	echo "Unzipping...[$cmd]\n";
	exec($cmd, $result);
	echo "Got ";
	print_r($result);
	exec("/usr/bin/chmod -R a+rw $target_path 2> /dev/null");

	$dir=$target_path;
	$cover = false;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
					if (filetype($dir . $file) == "dir")
						continue;
				    printf("filename: $file : filetype: " . filetype($dir . $file) . "\n");

				if ($file == "$romid - Cover.png")
				{
					$cover=true;
					printf("Found cover\n");
					exec("/usr/bin/rm -rf /tmp/crop-0.png");
					exec("/usr/bin/rm -rf /tmp/crop-1.png");
					echo "Cropping png...\n";
					$fname=$dir . $file;
					$cmd = "convert \"$fname\" -crop 214x192 /tmp/crop.png";
					echo "Spawning: [$cmd]\n";
					exec($cmd);
					echo "Inserting pieces\n";
					insert_blob("/tmp/crop-0.png", "cover", $romid);
					var_dump($db->errorInfo());
					insert_blob("/tmp/crop-1.png", "unknown", $romid);
					var_dump($db->errorInfo());

					//$res = insert_blob($dir . $file, "cover", $romid);
				}

				if ($file == "$romid - Icon.png")
				{
					printf("Found Icon\n");
					$res = insert_blob($dir . $file, "icon", $romid);
					var_dump($db->errorInfo());
				}

				if ($file == "$romid - InGame.png")
				{
					printf("Found InGame\n");
					$res = insert_blob($dir . $file, "ingame", $romid);
					var_dump($db->errorInfo());
				}

				if (substr($file, strlen($file)-3) == "nds")
				{
					printf("Found ROM\n");
					$res = insert_blob($dir . $file, "rom", $romid);
					var_dump($db->errorInfo());
				}

				if (substr($file, strlen($file)-3) == "nfo")
				{
					printf("Found NFO\n");
					$res = insert_blob($dir . $file, "info", $romid);
					var_dump($db->errorInfo());
				}
			}
			closedir($dh);
		}
		if ($cover == false)
		{
			echo "Missing cover for $romid\n";
/*
			exec("/usr/sfw/bin/wget http://img.files-ds-scene.net/boxarts/3501-3750/3608.jpg -O sdsdsds");
			insert_blob("/tmp/crop-0.png", "cover", $romid);
*/
		}

	}
}
?>
