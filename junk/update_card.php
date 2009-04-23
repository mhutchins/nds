
<?php

	$dir = "c:\users\martin\\nds";
	$html_header = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN" "http://www.w3.org/TR/REC-html40/strict.dtd"><html lang=en-us>';
	$html_header .= '<head><TITLE>Rom Manager</TITLE></head><body>';
	$html_header .= '<script type=\"text/javascript\" src=\"javascript.js\"></script>' ;

	$html_header .= '<script language="javascript">
	
function checkAll(formId, cName, check ) {
    for (i=0,n=formId.elements.length;i<n;i++)
        if (formId.elements[i].className.indexOf(cName) !=-1)
            formId.elements[i].checked = check;
}


</script>';


 
   $PHP_SELF=$_SERVER['PHP_SELF'];

    $debug=($_REQUEST['debug'] == "y") ? "y" : "n";
    $action=($_REQUEST['action'] == "") ? "none" : $_REQUEST['action'];
    $subaction=($_REQUEST['subaction'] == "") ? "none" : $_REQUEST['subaction'];
	
	ob_start();
	echo $html_header;

	mysql_pconnect("localhost", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 

	
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
	
	ob_end_flush();
	
function select_something()
{
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
		if ($count % 2 == 0)
		echo "<tr>";
		echo "<td align=center><input type='checkbox' class='chk' name='select[]'value='$romid'></td>";
//		echo "<td>$romid</td>";
		echo "<td width=35><a href=?action=details&romid=$romid><img src=?action=getimg&blobid=$cover_blobid width=27 height=39 ></a></td>";

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
	echo "<td><a href=?action=add&cardid=$cardid&subaction=rom>Add Roms</a></td>";

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
	echo "<table border=1>";
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
		echo "<tr><td>$cardid</td><td>$name</td><td>$size</td><td>$used</td><td>$free</td><td><a href=?action=edit&cardid=$cardid&subaction=card>Edit</a></td></tr>";
	}
	echo "<tr><td><input type='hidden' name='subaction' value='card'></td><td><a href=?action=Add&cardid=$cardid&subaction=card>Add</a></td></tr>";
	echo "</table>";

}

//=========================================================================================
function show_roms()
{
	global $html_header;
	global $PHP_SELF;
	
	echo $html_header;
	
	$rowsPerPage = 15;

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

	$where = "where (country = 'U' or country ='E') ";
	if ($cardid != "")
		$where .= " and id not in (select romid from card_rom where cardid = '$cardid' ) ";

	
	if ($search != "")
		$where .= " and ( name like '%$search%' or id like '%$search%' )" ;

	echo "<form action='$PHP_SELF' method='get'>\n";
	echo "<input type=text name=search value='$search'>\n";
	echo "<input type='hidden' name='action' value='add'>\n";
	echo "<input type='hidden' name='subaction' value='rom'>\n";
	if ($cardid != 0) echo "<input type='hidden' name='cardid' value='$cardid'>";
	echo "<input type='hidden' name='page' value='$pageNum'>\n";
	echo "<input type=submit name=submit value=Search>\n";
	echo "</form>\n";
	
		

	$query = "select id, name, country, filename, description, size, cover_blobid, icon_blobid, ingame_blobid from rom $where order by name, country limit $offset, $rowsPerPage";
	$data = mysql_query($query);
echo "<br>$query</br>";
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
	echo "<form action='$PHP_SELF' id='myform' method='get'>";
	echo "<table>";

	if ($cardid != 0) echo "<tr><td><a href=?action=edit&cardid=$cardid&subaction=card>Back to Card</a></td></tr>\n";
	if ($cardid != 0) echo "<tr><td><input type=submit name=action value=Add></td></tr>\n";
	
	echo "<tr>";
	echo "<td>
		<a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', true);\">Check All</a>
		<a href=\"javascript:void(0);\" onclick=\"checkAll(document.getElementById('myform'), 'chk', false);\">Uncheck All</a>
		</td>\n";
	echo "</tr>";
	echo "</table>";
	
	echo "<table border=1>";


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
		if ($count % 5 == 0)
			echo "<tr>";
		echo "<td>";
		
		echo "<table>";
		echo "<tr>";
		echo "<td>ID:$romid </td>";
		echo "</tr>";
		echo "<tr>";
		echo "<td valign=top width=120 height=60>$name ($country) ${size}Mb</td>";
		echo "<td><input type='checkbox' name='select[]' class='chk' value='$romid'></td>";
		echo "</tr>";
		echo "<td><a href=?action=details&romid=$romid><img src=?action=getimg&blobid=$cover_blobid width=107 height=192 ></a></td>";
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
	echo "<input type='hidden' name='action' value='add'>";
	echo "<input type='hidden' name='subaction' value='rom'>";
	echo "<input type='hidden' name='page' value='$pageNum'>";

	if ($cardid != 0) echo "<input type='hidden' name='cardid' value='$cardid'>";
	echo "</table>";
	echo "</form>";

	$query   = "SELECT COUNT(id) AS numrows FROM rom $where ";
	$result  = mysql_query($query) or die('Error, query failed');
	$row     = mysql_fetch_array($result, MYSQL_ASSOC);
	$numrows = $row['numrows'];

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);

	$self = "index.php?action=add&cardid=$cardid&subaction=rom&search=$search";

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
	if ($cardid != 0) echo "<BR><a href=?action=edit&cardid=$cardid&subaction=card>Back to Card</a>";



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
?>