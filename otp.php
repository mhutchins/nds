#!/usr/local/bin/php
<?php

	$CLS = `tput reset`;
	$SREV = `tput smso`;
	$EREV = `tput rmso`;
	$SUL = `tput smul`;
	$EUL = `tput rmul`;
	$SBOLD = `tput bold`;
	$EBOLD = `tput rmso`;


	mysql_pconnect("ndsdb", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 

	$mc_list = array();
	$db_list = array();
	$actions = array();


	$dir=rtrim(`pwd`);
	$card=6;

	$db_list = make_db_list($card);
	$mc_list = make_mc_list($dir);
	$actions = make_actions($mc_list, $db_list);

	while(1)
	{
		//echo $CLS;
		echo "Dir: $dir\n";
		echo "Card: $card\n";

		echo "Last command: [$c]\n";
		echo "d: Change Dir\n";
		echo "c: Change Card\n";
		echo "p: Process all actions\n";
		echo "A: Process all actions\n";
		echo "+: Process 'Add' actions\n";
		echo "-: Process 'Delete' actions\n";
		echo "C: Check ALL file roms (CRC32)\n";
		echo "cnnnn: Check rom 'nnnn' (CRC32)\n";

		$c = rtrim(fgets(STDIN));
		switch($c)
		{
			case "q":
				exit();
			case "d":
				echo "Enter new directory: ";
				$dir = rtrim(fgets(STDIN));
				$mc_list = make_mc_list($dir);
				$actions = make_actions($mc_list, $db_list);
				break;
			case "c":
				echo "Enter new card id: ";
				$card = rtrim(fgets(STDIN));
				$db_list = make_db_list($card);
				$actions = make_actions($mc_list, $db_list);
				break;
		
			case "p":
				foreach ($actions as $romid => $action)
				{
					echo "Romid: [$romid] [$action]\n";
				}
				break;

			case "A":
				do_deletes();
				$mc_list = make_mc_list($dir);
				$actions = make_actions($mc_list, $db_list);
			case "+":
				do_adds();
				$mc_list = make_mc_list($dir);
				$actions = make_actions($mc_list, $db_list);
				break;
			case "-":
				do_deletes();
				$mc_list = make_mc_list($dir);
				$actions = make_actions($mc_list, $db_list);
				break;
			case "C":
				check("ALL");
				break;
			default:
				if (substr($c, 0, 1) == 'c')
				{
					if (($romid = intval(substr($c, 1))) > 0)
						check($romid);
				}
				break;
		}
				
	}



	

function check($romid)
{
	global $cardid;
	global $actions;
	global $dir;

	$romlist=array();

	echo "Check $romid....\n";
	if ($romid == "ALL")
	{
		foreach ($actions as $romid => $action)
		{
			if ($action == '=')
				$romlist[] = $romid;
		}
	}
	else
		$romlist[] = $romid;

	foreach ($romlist as $romid)
	{
		echo "Checking $romid....\n";
		$file = rtrim(`ls $dir/${romid}*.nds`);
		$crc = strtoupper(sprintf("%08x", crc32(file_get_contents($file))));

	        $query = "select romid, title, romcrc from adv where romid = '$romid'";
		$res = mysql_query($query);
		$row = mysql_fetch_assoc( $res );
		$dbcrc = $row['romcrc'];
		echo "File: $file ";
		if ($dbcrc != $crc)
		{
			echo "File: $file CRC: $crc\t DBCRC: $dbcrc (Corrective Action Added!)\n";
			$actions[$romid] = "*";
		}
		else
			echo "Ok!\n";
	}
	sleep(2);
}

function do_adds()
{
	global $actions;
	global $dir;

	echo "Doing additions..\n";
	foreach ($actions as $romid => $action)
	{
		if ($action == '+' || $action == '*')
		{
			echo "Adding: $romid...";
			$file = addslashes(rtrim(`cd /nds_roms; ls ${romid}*.nds`));
			$res = `cd /nds_roms ; cp -v "$file" $dir`;
			echo "[res:$res]\n";
		}
		
	}
}
function do_deletes()
{
	global $actions;
	global $dir;

	echo "Doing deletions..\n";
	foreach ($actions as $romid => $action)
	{
		switch($action)
		{
			case "*":
				$actions[$romid] = "+";
			case "-":
				$file = addslashes(rtrim(`ls $dir/${romid}*.nds`));
				echo "Deleting: $file\n";
				`rm -f "$file"`;
				break;
		}
	}
}
function say($something)
{
	echo $something;
}

function make_db_list($cardid)
{
	$list = array();

	$query = "select romid from card_rom where cardid=$cardid";
	$res = mysql_query($query);
	while($row = mysql_fetch_assoc( $res )) 
	{
		$romid = sprintf("%04d", $row['romid']);		
		//echo "$romid: \n";
		$list['rom'][$romid] = $romid;
	}
	return $list;
}

function make_mc_list($dir)
{
	$list = array();
	if ($dh = opendir($dir))
	{
		while (($file = readdir($dh)) !== false)
		{
			    //say("filename: $file : filetype: " . filetype($dir . "/" . $file) . "\n");

			if (substr($file, strlen($file)-3) == "nds")
			{
				$romid=substr($file, 0, 4);
				//say("Found ROM [$romid]\n");
				$list['rom'][$romid] = $romid;
				//$list[$romid] = $romid;
			}
			if (substr($file, strlen($file)-3) == "sav")
			{
				$romid=substr($file, 0, 4);
				//say("Found ROM [$romid]\n");
				$list['sav'][$romid] = $romid;
			}

		}
		closedir($dh);
	}

	var_dump($list);
	return $list;
}

function make_actions($mc_list, $db_list)
{
	$actions = array();
	foreach ($mc_list['rom'] as $item)
	{
		if ( @$db_list['rom'][$item] == $item )
		{
			//echo "File: $item present in DB\n";
			$actions[$item] = "=";
		}
		else
		{
			//echo "File: $item missing from DB\n";
			$actions[$item] = "-";
		}
	
	}

	foreach ($db_list['rom'] as $item)
	{
		if ( @$mc_list['rom'][$item] == $item )
		{
			//echo "DB Item: $item present in Files\n";
			$actions[$item] = "=";
		}
		else
		{
			//echo "DB Item: $item missing from Files\n";
			$actions[$item] = "+";
		}
	}
	return $actions;
}
function file_crc($file)
   {
       $file_string = file_get_contents($file);
       $crc = crc32($file_string);
       return sprintf("%u", $crc);
   }

?>
