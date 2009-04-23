
<?php

	$html_header  = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN' 'http://www.w3.org/TR/html4/strict.dtd'>
	<html xmlns='http://www.w3.org/1999/xhtml' lang='en' xml:lang='en:us'>
	<head>
    <meta http-equiv='Content-type' content='text/html; charset=utf-8'>
    <title>$title</title>
    <style type='text/css' media='screen'>
      body {
        background: #111;
        color: #fff;
        font: 100% georgia,times,serif;
      }
      h1, p {
        font-weight: normal;
        margin: 0;
        padding: 0 0 .5em 0;
      }
      p {
        cursor: pointer;
      }
    </style>";

 
	$PHP_SELF=$_SERVER['PHP_SELF'];
	session_name("nds");
	session_start();

    $debug=($_REQUEST['debug'] == "y") ? true : false;
    $action=($_REQUEST['action'] == "") ? "none" : $_REQUEST['action'];
    $subaction=($_REQUEST['subaction'] == "") ? "none" : $_REQUEST['subaction'];
	
	//ob_start();
	//echo $html_header;

	mysql_pconnect("localhost", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 


	if (!isset($_COOKIE['sessionid']))
	{
		do_login();
	}
	else
	{
		$sess = $_COOKIE['sessionid'];
		$query = "select id from user where sess_id = '$sess'";
		$res = mysql_query($query);
		$row = mysql_fetch_assoc( $res );
		if ($row == false)
			do_login();
		else
		{
			$userid = $row['id'];
//			$_SESSION['userid'] = $userid;
		}
		
		switch(strtolower($action))
		{
			case 'getimg':
						$image = get_blob($_REQUEST['blobid']);
						ob_clean();
						header('Content-Length: '.strlen($image));
						header("Content-Transfer-Encoding: base64");
						header("Content-type: image/png");
						print $image;
						ob_end_flush();
						exit(1);
						break;
			case 'details':
						show_details();
						break;
			
			case 'edit':
						if ($subaction == "card")
							edit_card($_REQUEST['cardid']);
						break;
						
			case 'add':
						if ($subaction == "card")
							add_card();
						if ($subaction == "rom")
							show_roms();
						break;
						
			case 'show_roms':
						echo $html_header;
						show_roms();
						break;
						
			case 'update_roms':
						update_roms();
						break;
						
			case 'logout':
						header('Location: ' . $_SERVER['PHP_SELF']);
						setcookie("sessionid", null);
						break;

			case 'show_cards':
			default:
						show_cards();
						break;
		}
		echo "<table>";
		echo "<tr>";
		echo "<td><a href=?action=logout>Logout</a></td>";
		echo "</tr></table>";
	}	
	ob_end_flush();
	
function do_login()
{
	$name = $_REQUEST['name'];
	$pass = $_REQUEST['pass'];
	if ($name != "" || $pass != "")
	{
		$query = "select id, MD5(UNIX_TIMESTAMP() + RAND(UNIX_TIMESTAMP())) gses from user where name = '$name' and password = '$pass'";
		$res = mysql_query($query);
		mysql_error();
		
		if($row = mysql_fetch_array( $res ))
		{
			$sessid = $row[1];
			$query = "update user set sess_id = '$sessid' where name = '$name';";
			mysql_query($query);
			setcookie("sessionid", $row[1]);
			header('Location: ' . $_SERVER['PHP_SELF']);
			
		}
		
	}
	$self=$_SERVER['PHP_SELF'];
	echo "<html><head><title>Login</title></head><body>	
<form action='$self' method='Post'>
Username:<br />
<input type='Text' name='name' />
<br />
Password:<br />
<input type='password' name='pass' />
<br />
<input type='submit' value='Login' />
<input type='hidden' name='psRefer' value='$refer'>
</form>
</body>
</html>";

}

function edit_card($cardid)
{
	global $html_header;
	global $PHP_SELF;
	
	echo $html_header;
	
	global $userid;
	$cardid = $_REQUEST['cardid'];

	echo "Below is a list of ROMS you have currently selected (if any) to go onto your card.<br>";
	echo "You may remove ROMS that you do not want by clicking the checkbox next to the rom, and pressing the 'Delete' button.<br>";
	echo "You may click 'Add Roms' to be show a list of ROMS to select from.<br>";
	echo "'Show Cards' will take you back to select a different card (useful only if you have more than one card...).<br>";
	echo "<p>(BTW, You can click on a ROM picture to get a bit more detail....)";
	

	echo "<form action='$PHP_SELF' id='myform' method='get'>";
	echo "<input type='hidden' name='userid' value='$userid'>";

	echo "<table >";
//	echo "<th align=left>Rom</th>";
	echo "<th align=center>Select</th>";
	echo "<th align=left>Image</th>";
	echo "<th align=left>Name</th>";
	echo "<th>Size(Mb)</th>";
	echo "</tr>";

	$query = "select size csize from card where id = '$cardid'";
	$res = mysql_fetch_assoc(mysql_query($query));
	$csize = $res['csize'];
	

	if ($_REQUEST['select'])
	foreach($_REQUEST['select'] as $romid)
	{
		$query = "delete from card_rom where cardid = '$cardid' and romid ='$romid'";
		//echo $query;
		mysql_query($query);
	}

	$query = "select
				r.id,
				r.name,
				r.size,
				r.cover_blobid,
				sum(r.size) used
			from
				card_rom cr,
				rom r
			where
					cr.cardid = '$cardid'
				and
					cr.romid = r.id
				group by 1,2,3,4
				order by r.name";
	$ucard_res = mysql_query($query);
	$count=0;
	while($ucard_row = mysql_fetch_assoc( $ucard_res )) 
	{
		$romid = $ucard_row['id'];
		$cover_blobid = $ucard_row['cover_blobid'];
		$name = urldecode($ucard_row['name']);
		$size = round($ucard_row['size'] / 1024 / 1024);
//		$csize = $ucard_row['csize'];
		$used += $ucard_row['used'];

		$query = "select blobid from save where userid='$userid' and romid='$romid'";
		$res = mysql_fetch_assoc(mysql_query($query));
		$saveid = $res['blobid'];
		

		if ($count % 2 == 0)
		echo "<tr>";
		echo "<td align=center><input type='checkbox' class='chk' name='select[]'value='$romid'></td>";
//		echo "<td>$romid</td>";
		echo "<td width=35><a href=?action=details&romid=$romid><img src=?action=getimg&blobid=$cover_blobid width=27 height=39 alt='Click for details' ></a></td>";
		echo "<td>";
		if ($saveid > 0)
			echo "<a href=?action=savegame&saveid=$saveid><img src=diskimg.png width=20 height=20 border=0 alt='SAVE file exists' ></a>";
		else
			echo "<a href=?action=savegame&saveid=$saveid><img src=nodiskimg.png width=20 height=20 border=0 alt='No SAVE file on record'></a>";
		
		echo "</td>";
		echo "<td>$name</td>";
		echo "<td align=right>$size</td>";
		if ($count % 2 == 1)
		echo "</tr>";
		$count++;
	}
	$used = (int)round($used / 1024 / 1024);

	echo "</table>";
	echo "<table>";
	echo "<tr><td>" . ($csize - $used) . "Mb Free</td></tr>";
	echo "<tr>";
	echo "<td></td><td></td><td><a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', true);\">Check All</a></td>\n";
	echo "<td></td><td></td><td><a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', false);\">Uncheck All</a></td>\n";
	echo "<td><input type=submit name=submit value=Delete></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><a href=?action=add&userid=$userid&cardid=$cardid&subaction=rom>Add Roms</a></td>";

	echo "<td><a href=?action=none>Show Cards</a></td>";
	echo "</tr>";
	echo "</table>";

	echo "<input type='hidden' name='subaction' value='card'>";
	echo "<input type='hidden' name='action' value='edit'>";
	echo "<input type='hidden' name='cardid' value='$cardid'>";
	echo "</form>";

}
function add_card()
{
	global $subaction;
	global $userid;
	
	if ($_REQUEST['name'] != "")
	{
		$name = $_REQUEST['name'];
		$size = $_REQUEST['size'];
		
		$query = "insert into card values ('0', '$userid', '$name', '$size')";
		mysql_query($query);
		$cardid = mysql_insert_id();
		
		show_cards();
		return;
	}
	echo "<form action='$PHP_SELF' id='myform' method='get'>";
	echo "<input type='hidden' name='userid' value='$userid'>";

	echo "<table >";
	echo "<tr><td>Card Name</td><td><input type='Text' name='name' /></td></tr>";
	echo "<tr><td>Card Size</td><td><input type='Text' name='size' />(Mb)</td></tr>";
	echo "<tr><td><input type=submit name=action value=Add></td></tr>";
	echo "</table>";
	echo "<input type='hidden' name='subaction' value='card'>";
	echo "</form>";
	
}

function show_cards()
{
	global $userid;

	echo "<form action='$PHP_SELF' id='myform' method='get'>";
	echo "<input type='hidden' name='userid' value='$userid'>";
	echo "Select the card you wish to edit the contents of, <br>or hit Add to create a new card.(You probably *dont* want to do this!).<br>";
	echo "<table border=1>";
	echo "<tr><td>Card</td><td>name</td></td><td>Size(M)</td><td>Used(M)</td><td>Free</td><td>Select</td></tr>";

	
	$query = "select
				c.id,
				c.name,
				c.size
			from
				card c
			where
				c.userid = '$userid'";

	$ucard_res = mysql_query($query);
	while($ucard_row = mysql_fetch_assoc( $ucard_res )) 
	{
		$cardid = $ucard_row['id'];
		$name = urldecode($ucard_row['name']);
		$size = $ucard_row['size'];
		$used = $ucard_row['used'];
		$query = "select sum(size) used from card_rom cr, rom r where r.id = cr.romid and cr.cardid = '$cardid'";
		$res = mysql_fetch_assoc(mysql_query($query));
		$used = round($res['used'] / 1024 / 1024);
		$free = $size - $used;
		echo "<tr><td>$cardid</td><td>$name</td><td>$size</td><td>$used</td><td>$free</td><td><a href=?action=edit&userid=$userid&cardid=$cardid&subaction=card>Edit</a></td></tr>";
	}
	echo "<tr><td><input type='hidden' name='subaction' value='card'></td><td><a href=?action=Add&userid=$userid&cardid=$cardid&subaction=card>Add</a></td></tr>";
	echo "</table>";

}

//=========================================================================================
function show_roms()
{
	global $html_header;
	global $PHP_SELF;
	global $debug;
	global $userid;
	
	echo $html_header;
	if ($debug)var_dump($_REQUEST);
	$rowsPerPage = 50;

	$c_in = "'E','F','G','I','J','K','R','S','U'";
	$c_in = "'E','U'";

/*	
	if ($_REQUEST['country'])
	{
		$c_in = "";
		foreach($_REQUEST['country'] as $country)
		{
			$checked[$country]="checked";
			debug("[$country]");
			$c_in .= "'$country',";
		}
		$c_in = rtrim($c_in, ",");
	}
*/
	$cardid = $_REQUEST['cardid'];
	$search = trim($_REQUEST['search']);

	if ($_REQUEST['select'])
	foreach($_REQUEST['select'] as $romid)
	{
		$query = "insert into card_rom values ('$cardid', '$romid')";
		mysql_query($query);
	}

	// by default we show first page

	if(isset($_REQUEST['page']))
	{
		$pageNum = (int)$_REQUEST['page'];
	}

	if ($pageNum == 0)$pageNum = 1;

	if ($_REQUEST['submit'] == "Search")
		$pageNum=1;


	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;

	$where = "where country in ($c_in) ";
	if ($cardid != "")
		$where .= " and id not in (select romid from card_rom where cardid = '$cardid' ) ";

	
	if ($search != "")
	{
		$s=urlencode($search);
		$where .= " and ( name like '%$s%' or id like '%$s%' )" ;
	}



	echo "<form action='$PHP_SELF' method='get'>\n";
	echo "<input type=text name=search value='$search'>\n";
	echo "<input type='hidden' name='action' value='add'>\n";
//	echo "<input type='hidden' name='country' value='${_REQUEST['country']}'>\n";
	echo "<input type='hidden' name='subaction' value='rom'>\n";
//echo "<input type='hidden' name='search' value='$search'>";
	echo "<input type='hidden' name='userid' value='$userid'>";
	echo "<input type='hidden' name='page' value='$pageNum'>";
	if ($cardid != 0) echo "<input type='hidden' name='cardid' value='$cardid'>";
	echo "<input type=submit name=submit value=Search>\n";

//	echo "</form>\n";
	
		

	$query = "select id, name, country, filename, description, size, cover_blobid, icon_blobid, ingame_blobid from rom $where order by name, country limit $offset, $rowsPerPage";
	$data = mysql_query($query);
debug("<br>$query</br>");

	if ($cardid != 0)
	{
		$query = "select size csize from card where id = '$cardid'";
		$res = mysql_fetch_assoc(mysql_query($query));
		$csize = $res['csize'];

		$query = "select sum(r.size) used from card_rom cr, rom r where r.id = cr.romid and cr.cardid = '$cardid'";
		$res = mysql_fetch_assoc(mysql_query($query));
		$used = $res['used'];
		$free = round($csize - ($used / 1024 / 1024));

		echo "You have $free(Mb) free space on card $cardid";     
	}
//	echo "<form action='$PHP_SELF' id='myform' method='get'>";
	echo "<table>";

	if ($cardid != 0) echo "<tr><td><a href=?action=edit&userid=$userid&cardid=$cardid&subaction=card>Back to Card</a></td></tr>\n";
	if ($cardid != 0) echo "<tr><td><input type=submit name=action value=Add></td></tr>\n";
	
	echo "<tr>";
	echo "<td>
		<a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', true);\">Check All</a>
		<a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', false);\">Uncheck All</a>
		</td>\n";
	echo "</tr>";
	echo "</table>";
/*
	echo "<table>";
	echo "<tr>";
	echo "<td align=center> </td>";
	echo "<td align=center>E</td>";
	echo "<td align=center>F</td>";
	echo "<td align=center>G</td>";
	echo "<td align=center>I</td>";
	echo "<td align=center>J</td>";
	echo "<td align=center>K</td>";
	echo "<td align=center>R</td>";
	echo "<td align=center>S</td>";
	echo "<td align=center>U</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><input type=checkbox name=country[] value=' ' ${checked[' ']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='E' ${checked['E']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='F' ${checked['F']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='G' ${checked['G']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='I' ${checked['I']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='J' ${checked['J']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='K' ${checked['K']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='R' ${checked['R']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='S' ${checked['S']}></td>";
	echo "<td align=center><input type=checkbox name=country[] value='U' ${checked['U']}></td>";
	echo "</tr>";
	echo "</table>";
*/
	echo "<table border=1 >";


	$pname="";
	$count = 0;
	while($row = mysql_fetch_array( $data )) 
	{
		$romid=$row['id'];
		$name=urldecode($row['name']);
		$country=$row['country'];
		$size=round($row['size'] / 1024 / 1024);
		$icon_blobid=$row['icon_blobid'];
		$cover_blobid=$row['cover_blobid'];
		$ingame_blobid=$row['ingame_blobid'];

		$query = "select blobid from save where userid='$userid' and romid='$romid'";
		$res = mysql_fetch_assoc(mysql_query($query));
		$saveid = $res['blobid'];

		
		if ($count % 5 == 0)
			echo "<tr>";
		echo "<td>";
		
		echo "<table >";
		echo "<tr>";
		echo "<td>ID:$romid </td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign=top width=120 height=60>$name ($country) ${size}Mb</td>";
		echo "<td><input type='checkbox' name='select[]' class='chk' value='$romid'></td>";
		echo "</tr>";
		echo "<td><a href=?action=details&userid=$userid&romid=$romid><img src=?action=getimg&blobid=$cover_blobid width=107 height=192 alt='Click for details'></a></td>";
		echo "<td>";

			echo "<table >";
			echo "<tr>";
			echo "<td><img src=?action=getimg&blobid=$icon_blobid width=32 height=32></td>";
			echo "</tr>";
			echo "<tr><td>";
		if ($saveid > 0)
			echo "<a href=?action=savegame&saveid=$saveid><img src=diskimg.png width=20 height=20 border=0 alt='SAVE file exists' ></a>";
		else
			echo "<a href=?action=savegame&saveid=$saveid><img src=nodiskimg.png width=20 height=20 border=0 alt='No SAVE file on record'></a>";
			echo "</td></tr>";

			echo "</table>";



		
		echo "</td>";
		
		echo "</tr>";
		echo "</table>";

		echo "</td>\n";
		if ($count % 5 == 4)
			echo "</tr>";

		$count++;
	}
	echo "</table>";
	echo "<table>";
	if ($cardid != 0) echo "<tr><td></td><td><input type=submit name=action value=Add></td></tr>";

	echo "</table>";
	echo "</form>";

	$query   = "SELECT COUNT(id) AS numrows FROM rom $where ";
	$result  = mysql_query($query) or die('Error, query failed');
	$row     = mysql_fetch_array($result, MYSQL_ASSOC);
	$numrows = $row['numrows'];

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);

	$self = "index.php?action=add&userid=$userid&cardid=$cardid&subaction=rom&search=$search";

	// creating 'previous' and 'next' link
	// plus 'first page' and 'last page' link

	// print 'previous' link only if we're not
	// on page one
	if ($pageNum > 1)
	{
		$page = $pageNum - 1;
		$prev = " <a href=\"$self&page=$page\">[Prev]</a> ";
		$first = " <a href=\"$self&page=1\">[First Page]</a> ";
	} 
	else
	{
		$prev = ' [Prev] ';       // we're on page one, don't enable 'previous' link
		$first = ' [First Page] '; // nor 'first page' link
	}

	// print 'next' link only if we're not
	// on the last page
	if ($pageNum < $maxPage)
	{
		$page = $pageNum + 1;
		$next = " <a href=\"$self&page=$page\">[Next]</a> ";
		
		$last = " <a href=\"$self&page=$maxPage\">[Last Page]</a> ";
	} 
	else
	{
		$next = ' [Next] ';      // we're on the last page, don't enable 'next' link
		$last = ' [Last Page] '; // nor 'last page' link
	}

	// print the page navigation link
	echo $first . $prev . " Showing page <strong>$pageNum</strong> of <strong>$maxPage</strong> pages " . $next . $last;
	if ($cardid != 0) echo "<BR><a href=?action=edit&userid=$userid&cardid=$cardid&subaction=card>Back to Card</a>";



}	

function get_blob($blobid)
{
	$query = "select length(data) length, data from blobdata where id = '$blobid'";
	$res = mysql_fetch_assoc(mysql_query($query));
	return $res['data'];
}

function show_details()
{
	$romid = $_REQUEST['romid'];

	$query = "select id, name, country, filename, description, size, cover_blobid, icon_blobid, ingame_blobid from rom where id = '$romid'";
	$data = mysql_query($query);

	echo "<table>";         
	while($row = mysql_fetch_array( $data )) 
	{
		$romid=$row['id'];
		$name=urldecode($row['name']);
		$country=$row['country'];
		$size=round($row['size'] / 1024 / 1024);
		$desc = stripslashes($row['description']);
		$icon_blobid=$row['icon_blobid'];
		$cover_blobid=$row['cover_blobid'];
		$ingame_blobid=$row['ingame_blobid'];
		echo "<tr>";
		echo "<td colspan=2>$romid - $name ($country) ${size}Mb</td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td>";
		echo "<img src=?action=getimg&blobid=$cover_blobid width=321 height=576>";
		echo "</td>";
		echo "<td>";
		echo "<img src=?action=getimg&blobid=$ingame_blobid width=321 height=576>";
		echo "</td>";
		echo "<tr>";
		echo "<td colspan=2>";
		echo "<pre>$desc</pre>";
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
}
function debug($str)
{
	global $debug;
	
	if ($debug == true)
		echo $str;
}
?>