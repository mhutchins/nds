<?php

	mysql_pconnect("ndsdb", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 

	//var_dump($_REQUEST);

	$card_location=$_REQUEST['card_location'];
	$rom_location=$_REQUEST['rom_location'];

	if ($_REQUEST['action'])$action=strtolower($_REQUEST['action']);
	if ($_REQUEST['cardid'])$cardid=$_REQUEST['cardid'];
	if ($_REQUEST['userid'])$userid=$_REQUEST['userid'];


//	if ($card_location == "")
	{
		echo "<form action='$PHP_SELF' id='myform' method='get'>\n";
		echo "Username: ";
		echo "<select name=userid ";
		$query = "select id, name from user";
		$ucard_res = mysql_query($query);
		while($ucard_row = mysql_fetch_assoc( $ucard_res )) 
		{
			$cid = $ucard_row['id'];		
			$cnm = $ucard_row['name'];		
			$sel = "";
			if ($userid == $cid)
				$sel="selected='1'";
			echo "<option value=$cid $sel>$cnm\n";
		}
		echo "</select><BR>\n";
		echo "Choose card location:";
		echo "<select name=card_location ";
		for ($d = ord('D') ; $d < ord('Z') ; $d++)
		{
			$sel = "";
			$let = chr($d);
			if ($card_location == $let)
				$sel="selected='1'";
			echo "<option value=$let $sel>$let\n";
		}
		echo "</select><BR>\n";
		echo "Card: ";
		echo "<select name=cardid ";
		$query = "select id, name from card where userid = '$userid'";
		$ucard_res = mysql_query($query);
		while($ucard_row = mysql_fetch_assoc( $ucard_res )) 
		{
			$cid = $ucard_row['id'];		
			$cnm = $ucard_row['name'];		
			$sel = "";
			if ($cardid == $cid)
				$sel="selected='1'";
			echo "<option value=$cid $sel>$cnm\n";
		}
		echo "</select><BR>\n";
		echo "Rom Location: <input type=text name=rom_location value='$rom_location'>";
		echo "<input type=submit name=action value=change>";
		echo "<input type=submit name=action value=Rebuild>";
		echo "<input type=submit name=action value=scan>";
//if ($cardid)		echo "<input type=hidden name=cardid value='$cardid'>";
//if ($userid)		echo "<input type=hidden name=userid value='$userid'>";
		echo "</form>";
	}


	echo "<pre>";

	if ($action == "scan")
	{
		scan_location();
	}
	if ($action == "rebuild")
	{
		rebuild();
	}
	echo "</pre>";
	
function rebuild()
{

	global $card_location, $rom_location;
	global $userid;
	global $cardid;

	$dir="$card_location:\\";
	echo "<pre>";
	
	// sweep the card and remove any roms not on list. store saves of any removed games.
	// build array of games already on card as an exclusion list for the 'add' part
	echo "Opening: [$dir]\n";
	$romlist=array();
	if ($dh = opendir($dir)) {
		$count=1;
		while (($file = readdir($dh)) !== false)
		{
			$count++;
			if ($file == ".") continue;
			if ($file == "..") continue;
			$subdir=get_subdir($romid);
			$fullname=$dir . "" .$file;
			switch (fileExtension($file))
			{
				case "nds":
							set_time_limit(45);
							$save = substr($file, 0, strlen($file)-3) . "sav";
							$file_string = file_get_contents($fullname);
							$crc = strtoupper(sprintf("%08x", crc32($file_string)));

							// We have a rom, is it known
							$query = "select id,name from rom where crc32 = '$crc'";
							$res = mysql_fetch_assoc(mysql_query($query));
							$name=$res['name'];
							$romid=$res['id'];
							if ($romid > 0)
							{
								// Yup.
								$query = "select blobid from save where userid='$userid' and romid='$romid'";
								$res = mysql_fetch_assoc(mysql_query($query));
								$blobid=$res['blobid'];

								echo "ROM:\t[$file] CRC32:$crc\t";
								if (is_file($dir . $save))
									echo "Save:\t[$save]\n";
								else
									echo "** NO SAVE **\n";
								array_push($romlist, $romid);
							}
							else
									echo "Unknown Rom: $fullname\n";
							
							// Should it be here?
							$query = "select romid from card_rom where cardid = '$cardid' and romid = '$romid'";
							$res = mysql_fetch_assoc(mysql_query($query));
							if ($romid != $res['romid'])
							{
								echo "Foreign rom\n";
								unlink($fullname);
							}
							break;
				default:
							echo "Unknown: [$file]\n";
							break;
			}
			ob_flush();
			flush();
			//if ($count > 5) break;
		}
	
		echo "Processing section2<br>";
		// For each entry in the rom list, add the game (if not in exclusion list)
		// add saves for each game (if present)
		$count = 0;
		$query = "select
					r.id,
					r.name,
					r.size,
					r.filename
				from
					card_rom cr,
					rom r
				where
						cr.cardid = '$cardid'
					and
						cr.romid = r.id";
						
		$res = mysql_query($query);
		$count=0;
		while($row = mysql_fetch_assoc( $res ))
		{
			$romid=$row['id'];
			$name = $row['name'];
			echo "Processing $romid\n";
			
			if (in_array($romid, $romlist))
				echo "$name: Already on card\n";
			else
			{
				$subdir=get_subdir($romid);
				$fullname = $rom_location . '\\' . $subdir . '\\' . urldecode($row['filename']);
				$fullname = $rom_location . '\\' . urldecode($row['filename']);

				echo "Extracting $fullname\n";

				$cmd="mjh.bat \"$fullname\"";
				$ret=array();
				$err=array();
				var_dump($ret);
				var_dump($err);
				set_time_limit(65);
				ob_flush();
				flush();
				exec($cmd, $ret, $err);
				var_dump($ret);
				var_dump($err);
				$desc = $ret[0];
				$nds = $ret[1];
				//$romname="c:\\temp\\" . $nds;
				$fullname = $rom_location . '\\' . urldecode($row['filename']);
				$nromid = sprintf("%04d", $romid);
				$nname = str_replace(".7z", ".nds", urldecode($row['filename']));
				$romname="$rom_location\\$nname" ;
				set_time_limit(75);
				echo "Copying : [$romname] to [$dir]\n";
				ob_flush();
				flush();

				//copy ($romname, "$dir\\$nds");
				copy ($romname, "$dir\\$nname");
				echo "\n";
				ob_flush();
				flush();
				
			}
			ob_flush();
			flush();

		}
		ob_flush();
		flush();


	}
	echo "</pre>";
	closedir($dh);
	
}
	
//	update_roms("C:\\Users\\martin\\Documents\\Downloads");
	
function scan_location()
{
	global $card_location, $rom_location;
	global $userid;
	global $cardid;

	$dir="$card_location:\\";
	echo "<pre>";
	if ($dh = opendir($dir)) {
		$count=1;
		while (($file = readdir($dh)) !== false)
		{
			$count++;
			if ($file == ".") continue;
			if ($file == "..") continue;
			$fullname=$dir . "" .$file;
			switch (fileExtension($file))
			{
				case "nds":
							set_time_limit(25);
							$save = substr($file, 0, strlen($file)-3) . "sav";
							$file_string = file_get_contents($fullname);
							$crc = strtoupper(sprintf("%08x", crc32($file_string)));
							$query = "select id,name from rom where crc32 = '$crc'";
							$res = mysql_fetch_assoc(mysql_query($query));
							$name=$res['name'];
							$romid=$res['id'];

							$query = "select blobid from save where userid='$userid' and romid='$romid'";
							$res = mysql_fetch_assoc(mysql_query($query));
							$blobid=$res['blobid'];

							echo "ROM:\t[$file] CRC32:$crc\n";
							if (is_file($dir . $save))
								echo "Save:\t[$save]\n";
							else
								echo "** NO SAVE **\n";
							
							if ($blobid != 0)
							{
								$query="delete from blobdata where id = '$blobid'";
								mysql_query($query);
							}
							
							$save_blobid=file_to_blob($dir . $save, "save");
							$query="insert into save set blobid='$save_blobid', romid='$romid', userid='$userid'";
							mysql_query($query);
							
							if ($res['name'] != "")
							{
								echo "Correct: $name\n";
								$query="insert into card_rom set cardid='$cardid', romid='$romid'";
								mysql_query($query);
								echo "$query\n";
								if (mysql_affected_rows() == 1)
									echo "Added to card.";
								
							}
							else
								echo "** Unknown **\n";
							
							break;
				default:
							//echo "Unknown: [$file]\n";
							break;
			}
			ob_flush();
			flush();
			//if ($count > 5) break;
		}

	}
	echo "</pre>";
	closedir($dh);
	
}
	
function update_roms($dir)
{
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false)
		{
			if ($file == ".") continue;
			if ($file == "..") continue;
			$fullname=$dir . "\\" .$file;
			if (is_dir($fullname) ==true)
			{
				update_roms($fullname);
				continue;
			}
			echo "<br>$fullname</br>\n";
			
			if (fileExtension($file) == "7z")
			{
					$info = fixupname($file);
					$name = urlencode($info['name']);
					$country = $info['country'];
					$romid = $info['romid'];
					$clan = $info['clan'];
					$size = filesize($fullname);
					$filename = urlencode($file);

					$query = "select 1 found from rom where id = '$romid' and crc32=null";
					$res = mysql_fetch_assoc(mysql_query($query));

					if ($res['found'] == 2211)
					{
						$cover_blobid=file_to_blob("c:\\temp\\$romid - Cover.png", "cover");
						$icon_blobid=file_to_blob("c:\\temp\\$romid - Icon.png", "icon");
						$ingame_blobid=file_to_blob("c:\\temp\\$romid - InGame.png", "ingame");
						$query = "insert into rom set 
								id='$romid', 
								name='$name',
								country='$country',
								filename='$filename',
								cover_blobid='$cover_blobid',
								icon_blobid='$icon_blobid',
								ingame_blobid='$ingame_blobid',
								description='$description',
								available='1',
								crc32='$crc',
								size='$size'";
					}
					else
					{

					echo "Checking $romid...";
						$cmd="mjh.bat \"$fullname\"";
						$ret=array();
						$err=array();
						exec($cmd, $ret, $err);
						$desc = $ret[0];
						$nds = $ret[1];
						$file_string = file_get_contents("c:\\temp\\".$nds);
						$crc = strtoupper(sprintf("%08x", crc32($file_string)));						
						$size = filesize("c:\\temp\\".$nds);

						var_dump($ret);
						echo "Desc: [$desc]<br>\n";
						$description=addslashes(file_get_contents("c:\\temp\\$desc"));



						$query = "update rom set 
								crc32='$crc',
								size='$size'
								where id = '$romid'";
					}
						echo "<pre>$query\n</pre>";
						mysql_query($query);
						echo mysql_error();
						if (mysql_affected_rows() == 1)
						{
							echo "Added.";
						}
						else
							echo "Incomplete/damaged.";
							
			
					echo "<br>";
			}
			ob_flush();
			flush();
			set_time_limit(25);

		}
		closedir($dh);
	}
}

function file_to_blob($filename, $type)
{
	$retval=null;
	@$data=mysql_real_escape_string(file_get_contents($filename));
	if (strlen($data) > 0)
	{
		$query = "insert into blobdata set type='$type', data='$data'";
		mysql_query($query);
		$retval = mysql_insert_id();
	}
	return $retval;
}


function fixupname($name)
{
	$country="";
	$romid="";
	$newname = $name;

	// Lose '.nds'
	
	$info = array();
	
	$loc = strpos($name, '.7z');
	$name=rtrim(substr($name, 0, $loc));

	// Get the country code
	if (($pos = preg_match("/[\(\[]([UEJKSIFGRNuejksifgrn]{1})[\)\]]/", $name, $matches)))
	{
		$country=trim($matches[0], "()");
		$loc = strpos($name, $country);
		$front=substr($name, 0, $loc);
		$end = rtrim(substr($name, $loc + 3));
		$name=rtrim($front . $end);
	}

	// Lose any 'clan' tags
	if (($pos = preg_match("/[\(\[][A-Za-z0-9]{2,}[\)\]]/", $name, $matches)))
	{
		$clan=$matches[0];
		$loc = strpos($name, $clan);
		$front=substr($name, 0, $loc);
		$end = rtrim(substr($name, $loc + strlen($clan)));
		$name=rtrim($front . $end);
	}

	if (($pos = preg_match("/^([0-9]{3,})/", $name, $matches)))
	{
		$romid=$matches[0];
		$loc = strpos($name, $romid);
		$name=ltrim(substr($name, $loc+4), " -");
	}

	$info['name'] = $name;
	$info['country'] = $country;
	$info['romid'] = $romid;
	$info['clan'] = $clan;
	
	return $info;
}

function fileExtension($file) {
    $fileExp = explode('.', $file); // make array off the periods
    return $fileExp[count($fileExp) -1]; // file extension will be last index in array, -1 for 0-based indexes
	
}



function get_subdir($romid)
{
	return("");
	if ($romid <= 500)
		return("0001-0500");	
	if ($romid > 500 && $romid <= 1000)
		return("0501-1000");	
	if ($romid > 1000 && $romid <= 1500)
		return("1001-1500");	
	if ($romid > 1500 && $romid <= 2000)
		return("1501-2000");	
	if ($romid > 2000 && $romid <= 2100)
		return("2001-2100");	
	if ($romid > 2100 && $romid <= 2200)
		return("2101-2200");	
	if ($romid > 2200 && $romid <= 2300)
		return("2201-2300");	
	if ($romid > 2300 && $romid <= 2400)
		return("2301-2400");	
	if ($romid > 2400 && $romid <= 2500)
		return("2401-2500");	
}
?>
