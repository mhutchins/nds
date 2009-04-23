<?php
function do_login()
{
	global $db;

	$name = $_REQUEST['name'];
	$pass = $_REQUEST['pass'];
	if ($name != "" || $pass != "")
	{
		$query = "select id, MD5(UNIX_TIMESTAMP() + RAND(UNIX_TIMESTAMP())) gses from user where name = :name and password = :pass";

		$sth = $db->prepare($query);
		$sth->execute(array(':name' => $name, ':pass' => $pass));
		$rowcount = $sth->rowCount();
		$res = $sth->fetchAll();
		$row = $res[0];

		if($rowcount == 1)
		{
			$sessid = $row['gses'];
			$query = "update user set sess_id = :sessid where name = :name ;";

			$sth = $db->prepare($query);
			$sth->execute(array(':sessid' => $sessid, ':name' => $name));

			setcookie("sessionid", $sessid);
			header('Location: ' . $_SERVER['PHP_SELF']);
		}
	}
	$self=$_SERVER['PHP_SELF'];
	echo "<html><head><title>Login</title></head><body>	
<form action='$self' name=myform id=myform method='Post'>
Username:<br />
<input type='Text' name='name' />
<br />
Password:<br />
<input type='password' name='pass' />
<br />
<input type='submit' value='Login' />
<input type='hidden' name='psRefer' value='$refer'>
</form>
<script type='text/javascript'>
 document.myform.name.focus();
</script>
</body>
</html>";

}
function edit_card()
{
	global $db;
	global $html_header;
	global $PHP_SELF;
	
	echo $html_header;
	
	global $userid;
	$cardid = $_REQUEST['cardid'];

	echo "Below is a list of ROMS you have currently selected (if any) to go onto your card.<br>";
	echo "You may remove ROMS that you do not want by clicking the checkbox next to the rom, and pressing the 'Delete' button.<br>";
	echo "You may click 'Add Roms' to be shown a list of ROMS to select from.<br>";
	echo "'Show Cards' will take you back to select a different card (useful only if you have more than one card...).<br>";
	echo "<p>(BTW, You can click on a ROM picture to get a bit more detail....)";
	

	echo "<form action='$PHP_SELF' name=myform id=myform method='post'>";
	echo "<input type='hidden' name='userid' value='$userid'>";

	echo "<table >";
//	echo "<th align=left>Rom</th>";
	echo "<th align=center>Select</th>";
	echo "<th align=left>Image</th>";
	echo "<th align=left>Name</th>";
	echo "<th>Size(Mb)</th>";
	echo "</tr>";

	$query = "select size csize from card where id = :cardid";
	$sth = $db->prepare($query);
	$sth->execute(array(':cardid' => $cardid));
	$res = $sth->fetchAll();
	$row = $res[0];

	$csize = $row['csize'];

	if ($_REQUEST['select'])
	foreach($_REQUEST['select'] as $romid)
	{
		$query = "delete from card_rom where cardid = '$cardid' and romid ='$romid'";
		$query = "delete from card_rom where cardid = :cardid and romid =:romid";
                $sth = $db->prepare($query);
                $sth->execute(array(':cardid' => $cardid, ':romid' => $romid));
	}

	$query = "select
				r.id,
				r.name,
				r.size,
				sum(r.size) used
			from
				card_rom cr,
				rom r
			where
					cr.cardid = :cardid
				and
					cr.romid = r.id
				group by 1,2,3
				order by r.name";
	$count=0;

	$sth = $db->prepare($query);
	$sth->execute(array(':cardid' => $cardid));
	$rowcount = $sth->rowCount();
	$res = $sth->fetchAll();

	foreach($res as $ucard_row)
	{
		$romid = $ucard_row['id'];
		$name = urldecode($ucard_row['name']);
		$size = round($ucard_row['size'] / 1024 / 1024);
//		$csize = $ucard_row['csize'];
		$used += $ucard_row['used'];

		$query = "select blobid from save where userid=:userid and romid=:romid";
                $sth = $db->prepare($query);
                $sth->execute(array(':userid' => $userid, ':romid' => $romid));
                $res = $sth->fetchAll();
                $row = $res[0];

		$saveid = $row['blobid'];

		if ($count % 2 == 0)
		echo "<tr>";
		echo "<td align=center><input type='checkbox' class='chk' name='select[]'value='$romid'></td>";
//		echo "<td>$romid</td>";
		$txtromid=sprintf("%04d", $romid);
//<a href=?action=details&amp;romid=$romid>
		echo "<td width=35>
<a href=\"javascript:;void($romid);\" onclick=\"window.open('$PHP_SELF?action=details&amp;userid=$userid&amp;romid=$romid')\" >
<img src='artwork/split/$txtromid - Cover-0.png' width=27 height=39 alt='Click for details' ></a></td>";
		echo "<td>";
		if ($saveid > 0)
			echo "<a href=?action=savegame&amp;saveid=$saveid><img src=diskimg.png width=20 height=20 border=0 alt='SAVE file exists' ></a>";
		else
			echo "<a href=?action=savegame&amp;saveid=$saveid><img src=nodiskimg.png width=20 height=20 border=0 alt='No SAVE file on record'></a>";
		
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
	echo "<td></td>";
	echo "<td></td>";
	echo "<td><a href=\"javascript:void(0);\" onclick=\"checkset(document.getElementById('myform'), 'chk');\">All</a></td>\n";
	echo "<td><a href=\"javascript:void(0);\" onclick=\"checkinv(document.getElementById('myform'), 'chk');\">Invert</a></td>\n";
	echo "<td><input type=submit name=submit value=Delete></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><a href=?action=add&amp;userid=$userid&amp;cardid=$cardid&amp;subaction=rom>Add Roms</a></td>";

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
	global $db;
	global $subaction;
	global $userid;
	
	if ($_REQUEST['name'] != "")
	{
		$name = $_REQUEST['name'];
		$size = $_REQUEST['size'];
		
		$query = "insert into card values ('0', :userid, :name, :size)";
		$sth = $db->prepare($query);
		$sth->execute(array(':userid' => $userid, ':name' => $name, ':size' => $size));
		$cardid = $db->lastInsertId();
		
		show_cards();
		return;
	}
	echo "<form action='$PHP_SELF' name=myform id=myform method='post'>";
	echo "<input type='hidden' name='userid' value='$userid'>";

	echo "<table >";
	echo "<tr><td>Card Name</td><td><input type='Text' name='name' /></td></tr>";
	echo "<tr><td>Card Size</td><td><input type='Text' name='size' />(Mb)</td></tr>";
	echo "<tr><td><input type=submit name=action value=Add></td></tr>";
	echo "</table>";
	echo "<input type='hidden' name='subaction' value='card'>";
	echo "</form>";
	
}
function user_maint()
{
	global $userid;
	global $db;

	echo "<pre>";
	var_dump($_REQUEST);
	echo "</pre>";
	$subaction = $_REQUEST['subaction'];

	echo "<br>$subaction</br>";
	echo "<form action='$PHP_SELF' name=myform id=myform method='post'>";
	echo "<input type='hidden' name='userid' value='$userid'>";
	echo "<input type='hidden' name=action value=users>";
	echo "<table border=1>";

	
	if ($subaction == "add")
	{
		echo "<tr><th>Name</th><th>Password</th></tr>\n";
		echo "<td><input type=text name=username ></td>";
		echo "<td><input type=text name=password ></td>";
		echo "</table>";
		echo "<input type=submit name=subaction value=save>";
		return;
	}
	if ($subaction == "save")
	{
		$username = $_REQUEST['username'];
		$password = $_REQUEST['password'];
		$query = "insert into user set name = :username, password = :password";
                $sth = $db->prepare($query);
                $sth->execute(array(':username' => $username, ':password' => $password));
                $rowcount = $sth->rowCount();

		if ($rowcount == 1)
			echo "Updated.";
	}
	if ($subaction == "update")
	{
		foreach($_REQUEST['username'] as $KEY => $value)
		{
			$username = $_REQUEST['username'][$KEY];
			$password = $_REQUEST['password'][$KEY];
			$uid = $_REQUEST['uid'][$KEY];
			$query = "update user set name = :username, password = :password where id = :uid";
			$sth = $db->prepare($query);
			$sth->execute(array(':username' => $username, ':password' => $password, ':uid' => $uid));
			$rowcount = $sth->rowCount();

			if ($rowcount == 1)
				echo "Updated.";
		}
		
	}

	echo "<tr><th>Id</th><th>Name</th><th>Password</th></tr>\n";
	
	$query = "select
				id,
				name,
				password
			from
				user";
	foreach($db->query($query) as $row)
	{
		$uid = $row['id'];
		$name = $row['name'];
		$password = $row['password'];
		echo "<tr><td><input type=text name=uid[] value=$uid size=2></td>";
		echo "<td><input type=text name=username[] value=$name ></td>";
		echo "<td><input type=text name=password[] value=$password></td>";
		//echo "<td><input type=submit name=subaction[] value=update></td>";
		//echo "<td><a href=?action=users&amp;userid=$userid&amp;subaction=edit>delete</a><td>";
		echo "<tr>\n";
	}
	echo "</table>";
	echo "<input type=submit name=subaction value=update>";
	echo "</form>";
	echo "<a href=?action=users&amp;userid=$userid&amp;subaction=add>Add User</a>";
}
function show_cards()
{
	global $userid;
	global $db;

	$add = $_REQUEST['add'];

	echo "<form action='$PHP_SELF' name=myform id=myform method='post'>";
	echo "<input type='hidden' name='userid' value='$userid'>";
	echo "Select the card you wish to edit the contents of.<br>";
	//echo "Hit Add to create a new card.(You probably *dont* want to do this!).<br>";
	echo "<table border=1>";

	
	$query = "select
				c.id,
				c.name,
				c.size,
				u.name user
			from
				user u,
				card c
			where
				u.id = c.userid";
	if ($userid == 1)
		$query .= "";
	else
		$query .= " and c.userid = :userid";

	$sth = $db->prepare($query);
	$sth->execute(array(':userid' => $userid));
	$res = $sth->fetchAll();

	$oldname = "";

        foreach($res as $ucard_row)
	{
		$cardid = $ucard_row['id'];
		$uname = $ucard_row['user'];
		if ($uname != $oldname)
		{
			if ($oldname != "")
				echo "<tr><td colspan=6><input type=button name=btn_add value='Add Card' onclick='btn_sub()' ></td></tr>";
		echo "<tr><td colspan=6>${uname}'s cards</td></tr>";
		echo "<tr><td>Card</td><td>name</td></td><td>Size(M)</td><td>Used(M)</td><td>Free</td><td>Select</td></tr>";
		$oldname = $uname;
		}
		$name = urldecode($ucard_row['name']);
		$size = $ucard_row['size'];
		$used = $ucard_row['used'];

		$query = "select sum(size) used from card_rom cr, rom r where r.id = cr.romid and cr.cardid = '$cardid'";
		$res = $db->query($query)->fetch();

		$used = round($res['used'] / 1024 / 1024);
		$free = $size - $used;
		echo "<tr><td>$cardid</td><td>$name</td><td>$size</td><td>$used</td><td>$free</td><td><a href=?action=edit&amp;userid=$userid&amp;cardid=$cardid&amp;subaction=card>Edit</a></td></tr>";
	}
	echo "	<tr><td><input type='hidden' name='subaction' value='card'></td>";
//	echo "	<td><a href=?action=Add&amp;userid=$userid&amp;cardid=$cardid&amp;subaction=card>Add</a></td>";
	echo "	</tr>";
	echo "</table>";
	if ($add) echo "<tr><td><input type=submit name=action value=Add_card></td></tr>";

	if ($userid == 1)
	echo "<a href=?action=users&amp;userid=$userid&amp;subaction=list>Maintain Users</a>";
}
function show_roms()
{
	global $db;
	global $html_header;
	global $PHP_SELF;
	global $debug;
	global $userid;
	
	echo $html_header;
	if ($debug == "Y")var_dump($_REQUEST);
	//var_dump($_REQUEST);

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
	$show_hidden = $_REQUEST['show_hidden'];
	$cardid = $_REQUEST['cardid'];

	$search = trim($_REQUEST['search']);

	$sort = trim($_REQUEST['sort']);
	if ($sort == "") $sort = "name";

	$show = trim($_REQUEST['show']);
	if ($show == "") $show = "20";

	$fstate = trim($_REQUEST['fstate']);
	if ($fstate == "") $fstate = "none";

	$rowsPerPage = $show;


	$showsel[$show] = "selected";
	
	$sortsel[0] = "";
	$sortsel[1] = "";
	$sortsel[2] = "";
	$sortsel[3] = "";
	$sortsel[4] = "";
	
	$filter = $_REQUEST['filter'];

	$chk_flt=array();
	$genre_filter="";
	if ($filter != false)
	foreach($filter as $flt)
	{
		$chk_flt[$flt] = "CHECKED";
		$genre_filter .= "'$flt',";
	}

	if ($genre_filter != "")
	{
		$genre_filter = trim($genre_filter, ',');
		$genre_filter = " and genre in ($genre_filter)";
	}

	if ($sort == "name") $sortsel[0] = "selected";
	if ($sort == "id") $sortsel[1] = "selected";
	if ($sort == "size") $sortsel[2] = "selected";
	if ($sort == "genre") $sortsel[3] = "selected";
	if ($sort == "rating desc") $sortsel[4] = "selected";

	if (@$_REQUEST['select'])
	foreach($_REQUEST['select'] as $romid)
	{
		$query = "insert into card_rom values ('$cardid', '$romid')";
		$db->exec($query);
	}

	if(@isset($_REQUEST['page']))
		$pageNum = (int)$_REQUEST['page'];

	if ($pageNum == 0)$pageNum = 1;

	// Deliberate lack of 'break' used here!!
	switch(@$_REQUEST['submit_button'])
	{
		case "Apply Filter":
		case "Clear":
			$search = "";
		case "Search":
			$pageNum=1;
	}

	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;

	// Provide a null where clause so I can just
	// tack on ' and this' or 'and that' etc
	$where = " ( 1 ) ";
	if ($cardid != "")
		$where .= " and id not in (select romid from card_rom where cardid = '$cardid' ) ";
	
	if ($search != "")
	{
		$s=urlencode($search);
		$where .= " and ( (name like '%$s%' or id like '%$s%') ) " ;
		//$show_hidden = 1;
	}

	//else
		//$where .= "and available=true ";
		$where .= "and country in ('E', 'U') ";

	if ($show_hidden != 1)
		$where .= "and available = true ";

	echo "<form action='$PHP_SELF' name=myform id=myform method='get'>\n";
	echo "<p>";
	echo "<input type='hidden' name='action' value='add'>\n";
//	echo "<input type='hidden' name='country' value='${_REQUEST['country']}'>\n";
	echo "<input type='hidden' name='subaction' value='rom'>\n";
	echo "<input type='hidden' name='userid' value='$userid'>\n";
	echo "<input type='hidden' name='page' value='$pageNum'>\n";
	echo "<input type='hidden' name='debug' value='$debug'>\n";
	echo "<input type='hidden' id='fstate' name='fstate' value=$fstate>";
	if ($cardid != 0) echo "<input type='hidden' name=cardid value='$cardid'>\n";

	echo "<div>Search for ";
	echo "<input type=text name=search value='$search'>\n";
	echo "<input type=submit name=submit_button value=Search>\n";
	if ($search != "") echo "<input type=submit name=submit_button value=Clear>\n";
	echo "</div>";

	echo "<div>Sort by <SELECT NAME='sort' onchange=\"submitform()\">
			<OPTION VALUE='name' ${sortsel[0]} >Name
			<OPTION VALUE='id' ${sortsel[1]} >Release
			<OPTION VALUE='size' ${sortsel[2]} >Size
			<OPTION VALUE='genre' ${sortsel[3]} >Genre
			<OPTION VALUE='rating desc' ${sortsel[4]} >Rating
			</SELECT>";
	if ($show_hidden == 1)
		$show_hidden_chk = "checked";
	else
		$show_hidden_chk = "";

	echo "<input type='checkbox' id='show_hidden' name='show_hidden' value=1 $show_hidden_chk onclick='this.blur()' onchange='document.myform.submit()' >Show Hidden</div>";

	echo "<div>Show <SELECT NAME='show' onchange='submitform()' >";
	for ($i = 1 ; $i < 200 ; $i++)
		echo "<OPTION VALUE='$i' ${showsel[$i]} >$i";
	echo "</SELECT> per page </div>\n";

	echo "<p>";
	echo "<a href='#' onClick=\"javascript:ShowHide('filter_div');return false\" >Show/hide filters</a>";
	echo "</p>";
	echo "<div id=filter_div style='align: center; width:520px ; display: $fstate; border: blue 4px solid;' >";
	echo "<table >\n";
	echo "<tr>";

	$query="select distinct genre from adv a, rom r where a.romid = r.id and r.available=1 order by genre";
	$g = 0;
        foreach($db->query($query) as $row)
	{
		if (($g % 4) == 4)
		{
			echo "<tr>";
			$open=true;;
		}
		$flt = $row['genre'];
		$chk = $chk_flt[$flt];

		if ($genre_filter == "") $chk = "CHECKED";

		echo "<td ><input name='filter[]' class=chk type=checkbox value='$flt' $chk >$flt</td>\n";

		if (($g % 4) == 3)
		{
			echo "</tr>";
			$open=false;
		}
		$g++;
	}
	if ($open == true)
		echo "</tr>";
	echo "<tr>";

	echo "<td>";
	echo "<a href=\"javascript:void(0);\" onclick=\"checkinv(document.getElementById('myform'), 'chk');\">Invert</a> / ";
	echo "<a href=\"javascript:void(0);\" onclick=\"checkset(document.getElementById('myform'), 'chk');\">All</a> / ";
	echo "<a href=\"javascript:void(0);\" onclick=\"checkclr(document.getElementById('myform'), 'chk');\">None</a>";
	echo "</td>";

	echo "<td colspan=3><input type=submit name=submit_button value='Apply Filter'>(<b>Warning</b>: will reset search)</td>";
	echo "</tr>";
	echo "</table>";
	echo "</div>";


	if ($cardid != 0)
	{
		$query = "select size csize from card where id = '$cardid'";
		$res = $db->query($query)->fetch();
		$csize = $res['csize'];

		$query = "select sum(r.size) used from card_rom cr, rom r where r.id = cr.romid and cr.cardid = '$cardid'";
		$res = $db->query($query)->fetch();
		$used = $res['used'];
		$free = round($csize - ($used / 1024 / 1024));

		echo "<table><tr><td>You have </td><td id=free>$free</td><td>(Mb) free space on this card.</td></tr></table>";
	}
	if ($cardid != 0)
	{
		echo "<table>";
		echo "<tr><td><a href=\"$PHP_SELF?action=edit&amp;userid=$userid&amp;cardid=$cardid&amp;subaction=card\">Back to Card</a></td></tr>\n";
		echo "</table>";
	}

	echo "<table >";
	echo "<tr><td id=roms>";
	
	$pname="";
	$count = 0;

	$query = "
		select
			x.rating rating,
			r.id,
			r.name,
			r.country,
			r.filename,
			r.description,
			r.size,
			r.available,
			a.genre,
			a.wifi
		from
			adv a,
			rom r
			left outer join rating x on r.id = x.romid
		where
			$where
			and
				r.id = a.romid $genre_filter
			order by $sort , name
			limit $offset, $rowsPerPage
		";
	debug("<br>$query</br>");
        foreach($db->query($query) as $row)
	{
		$available=$row['available'];
		
		if ($available == 0 && $show_hidden != 1)
			continue;
		$romid=$row['id'];
		$rating=$row['rating'];
		$wifi=$row['wifi'];
		$name=urldecode($row['name']);
		$country=$row['country'];
		$size=round($row['size'] / 1024 / 1024);
		$genre=$row['genre'];

		$query = "select blobid from save where userid='$userid' and romid='$romid'";
		$res = $db->query($query)->fetch();
		$saveid = $res['blobid'];
		$a_rating = getvote($romid);
		if($a_rating['avg'] <= 0 ) $a_rating['avg'] = 0;

		$country_name = code2country($country);
		$txtromid=sprintf("%04d", $romid);
		if ($available == 1)
			echo " <div id=div_$romid class='img' style=background-color:white >";
		else
			echo " <div id=div_$romid class='img' style=background-color:red >";
		echo "
				<a href=\"javascript:;void($romid);\" onclick=\"window.open('$PHP_SELF?action=details&amp;userid=$userid&amp;romid=$romid')\" >
				<img src='artwork/split/$txtromid - Cover-0.png' width=137 height=120 alt='Click for details' >
				</a>
				<div class='desc'   >$name</div>
				<div class='rom'   >$genre $rating</div>
				<div class='rom'   >${size}Mb";
		if ($wifi == 1)
				echo "<img src='images/wifi.png' alt='Wifi Game' onmouseover=\"showAltAlt(this, 'Wifi', 100);\" >";
		if ($userid == 1)
			echo " $country_name";
 		echo "</div>";
		echo "
				<div class='rom' onclick='quickadd($romid);return false;' ><a href='void(0)'>Add</a></div>
				<div class='rating' id='rating_$romid'>${a_rating['avg']}</div>
			</div>\n";
	}

	echo "</td></tr>";
	echo "</table>\n";
	echo '<div id="Wifi" style="visibility: hidden">
		<span>
		This game is Wifi enabled</span>
		</div> ';

//	if ($cardid != 0)
//		echo "<table><tr><td></td><td><input type=submit name=action value=Add></td></tr></table>\n";

	//echo "<div id='mytarget'>Hello! I am a target!</div>";

	$query   = "SELECT COUNT(r.id) AS numrows from rom r, adv a where $where and r.id = a.romid $genre_filter";
	$row = $db->query($query)->fetch();
	$numrows = $row['numrows'];

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);

	echo "<p>";
	echo "<input type=hidden id=var_numrows name=var_numrows value='$numrows' >";
	echo "<input type=hidden id=var_rowsperpage name=var_rowsperpage value='$rowsPerPage' >";
	echo "<input type=hidden id=var_maxpage name=var_maxpage value='$maxPage' >";
	echo "</p>";

	$self = "index.php?action=add&amp;userid=$userid&amp;show_hidden=$show_hidden&amp;cardid=$cardid&amp;sort=$sort&amp;show=$show&amp;subaction=rom&amp;search=$search";

	echo "<div class=nav>";
	// creating 'previous' and 'next' link
	// plus 'first page' and 'last page' link

	// print 'previous' link only if we're not
	// on page one
	if ($pageNum > 1)
	{
		$page = $pageNum - 1;
		$prev = " <a href=\"$self&amp;page=$page\">[Prev]</a> ";
		$first = " <a href=\"$self&amp;page=1\">[First Page]</a> ";
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
		$next = " <a href=\"$self&amp;page=$page\">[Next]</a> ";
		
		$last = " <a href=\"$self&amp;page=$maxPage\">[Last Page]</a> ";
	} 
	else
	{
		$next = ' [Next] ';      // we're on the last page, don't enable 'next' link
		$last = ' [Last Page] '; // nor 'last page' link
	}

	// print the page navigation link
	echo $first . $prev . " Showing page <strong>$pageNum</strong> of <strong>$maxPage</strong> pages " . $next . $last;

	if ($cardid != 0)
		echo "<BR><a href='?action=edit&amp;userid=$userid&amp;cardid=$cardid&amp;subaction=card'>Back to Card</a>";

	echo "</div>";

	echo "<script type='text/javascript'>";
 	echo "document.myform.search.focus();";
	echo "</script>";

	echo "</form>\n";

}	
function get_blob($blobid)
{
	global $db;
	$query = "select length(data) length, data from blobdata where id = '$blobid'";
	$res = $db->query($query)->fetch();
	return $res['data'];
}
function show_details()
{
	global $db;
	$romid = $_REQUEST['romid'];

	$query = "select id, name, crc32, country, filename, description, size from rom where id = '$romid'";

	echo "<div onclick='window.close()' ><p align=center style='font-size:150%'><b>CLICK ANYWHERE TO CLOSE</b></p><table>";         
        foreach($db->query($query) as $row)
	{
		$romid=$row['id'];
		$name=urldecode($row['name']);
		$country=$row['country'];
		$crc32=$row['crc32'];
		$size=round($row['size'] / 1024 / 1024);
		$desc = stripslashes($row['description']);
		echo "<tr>";
		echo "<td colspan=2>$romid - $name ($country) ${size}Mb ($crc32)</td>";
		echo "</tr>";

		echo "<tr>";

		echo "<td>";
		$txtromid=sprintf("%04d", $romid);
		echo "<div><img src='artwork/split/$txtromid - Cover-0.png' width=321 height=288></div>";
		echo "<div><img src='artwork/split/$txtromid - Cover-1.png' width=321 height=288></div>";
		echo "</td>";

		echo "<td>";
		echo "<img src='artwork/$txtromid - InGame.png' width=321 height=576>";
		echo "</td>";

		echo "<tr>";

		echo "<td colspan=2>";
		echo "<pre>$desc</pre>";
		echo "</td>";
		echo "</tr>";
	}
	echo "</table></div>";
	echo "<a href=\"javascript:void(0);\" onclick='window.close();'>Close</a>";
}
function code2country($code)
{
	switch($code)
	{
		case 'U':
				return("USA");
				break;
		case 'E':
				return("EUR");
				break;
		case 'J':
				return("JPN");
				break;
		case 'K':
				return("KOR");
				break;
		case 'S':
				return("SPN");
				break;
		case 'I':
				return("ITA");
				break;
		case 'F':
				return("FRA");
				break;
		case 'G':
				return("GER");
				break;
		case 'R':
				return("RUS");
				break;
		case 'N':
				return("NRL");
				break;
		default:
				return("???");
	}
}
function debug($str)
{
	global $debug;
	
	if ($debug == "Y")
		echo $str;
}
function getvote($romid)
{
	global $db;
	global $userid;
	
	$query = "select avg(rating) avg from rating where romid = '$romid'";
	$res = $db->query($query)->fetch();
	$avg = $res['avg'];
	
	$query = "select rating myrating from rating where romid = '$romid' and userid='$userid'";
	$res = $db->query($query)->fetch();
	$myrating = $res['myrating'];
	
	$result=array("avg" => $avg, "myrating" => $myrating);
	return $result;
}
function setvote($romid, $rating)
{
	global $db;
	global $userid;
	
	$query = "insert into rating values ('$userid', '$romid', '$rating') on duplicate key update rating = '$rating'";
	$db->exec($query);
	return getvote($romid);
}
function quickadd_roms()
{
	global $db;
	global $html_header;
	global $PHP_SELF;
	global $debug;
	global $userid;
	
//	echo $html_header;
	if ($debug == "Y")var_dump($_REQUEST);
	$cardid = $_REQUEST['cardid'];
	$search = trim($_REQUEST['search']);

	if ($_REQUEST['select'])
	foreach($_REQUEST['select'] as $romid)
	{
		$query = "insert into card_rom values ('$cardid', '$romid')";
		$db->exec($query);
	}
	$query = "select sum(size) used from card_rom cr, rom r where r.id = cr.romid and cr.cardid = '$cardid'";
	$res = $db->query($query)->fetch();
	$used = $res['used'];
	
	$query = "select size csize from card where id = '$cardid'";
	$res = $db->query($query)->fetch();
	$csize = $res['csize'];
	
	$free = round($csize - ($used / 1024 / 1024));

	echo $free;
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
	if (preg_match("/[\(\[]([UEJKSIFGRNuejksifgrn]{1})[\)\]]/", $name, $matches))
	{
		var_dump($matches);
		$country=trim($matches[0], "()");
		$cloc = strpos($name, $matches[0]);
		$front=substr($name, 0, $cloc);
		$end = rtrim(substr($name, $cloc + 3));
		$name=rtrim($front . $end);
	}

	// Lose any 'clan' tags
	if (($pos = preg_match("/[\(\[][A-Za-z0-9 \']{2,}[\)\]]/", $name, $matches,null, $cloc)))
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
function file_to_blob($filename, $type)
{
	global $db;

	$retval=1;
	@$data=(file_get_contents($filename));
	if (strlen($data) > 0)
	{
		$sth = $db->prepare("insert into blobdata set type=:type, data=:data");
		$sth->execute(array(':type' => $type, ':data' => $data));
		$retval = $db->lastInsertId();
	}
	return $retval;
}
function fileExtension($file)
{
    $fileExp = explode('.', $file); // make array off the periods
    return $fileExp[count($fileExp) -1]; // file extension will be last index in array, -1 for 0-based indexes
	
}

function update_roms($arg)
{
	global $db;

	$src = $arg['source'];
	$dst = $arg['dest'];

	if ($dh = opendir($src)) {
		while (($file = readdir($dh)) !== false)
		{
			if ($file == ".") continue;
			if ($file == "..") continue;
			$fullname=$src . "/" .$file;
			if (is_dir($fullname) ==true)
			{
				echo "<br>DIR: $fullname</br>\n";
				update_roms(array("source" => $src, "dest" => $dst));
				continue;
			}
			
			if (fileExtension($file) == "7z")
			{
					echo "<br>ZIP: $fullname</br>\n";
					continue;
					$info = fixupname($file);
					$name = urlencode($info['name']);
					$country = $info['country'];
					$romid = $info['romid'];
					$txtromid=sprintf("%04d", $romid);
					$clan = $info['clan'];
					$size = filesize($fullname);
					$filename = urlencode($file);
					var_dump($info);
					$query = "select 1 found from rom where id = '$romid'";
					$res = $db->query($query)->fetch();

					echo "Adding $romid...";
					system("mkdir -p /tmp/nds 2>/dev/null");
					system("rm /tmp/nds/* 2>/dev/null");
					system("7z e -y -o$dest \"$fullname\" '*.png'");
					//system("convert -crop 107x192 \" - Cover.png" /tmp/mjh.png
/*
					$cmd="/usr/local/bin/crc32 /tmp/nds/$txtromid*.nds";
					$ret=array();
					$err=array();
					exec($cmd, $ret, $err);
					$crc32 = $ret[0];

					$cmd="ls /tmp/nds/$txtromid*.nfo";
					$ret=array();
					$err=array();
					exec($cmd, $ret, $err);
					$filename_desc=$ret[0];
*/
					$file_string = null;
					//$file_string = file_get_contents("$nds");
					//$crc = strtoupper(sprintf("%08x", crc32($file_string)));						
					$size = filesize("$nds");

					echo "Desc: [$desc]<br>\n";
					$description="";
					if ($desc != "") $description=addslashes(file_get_contents($desc));

					$query = "replace into rom set 
							id='$romid', 
							name='$name',
							country='$country',
							filename='$filename',
							description='$description',
							available='0',
							crc32='$crc',
							size='$size'";
					echo "Query:[$query]\n";
					if ($db->exec($query) == 1)
					{
						echo "Updated.";
					}
					else
						echo "Oopps.";
					echo "<br>";
			}
			ob_flush();
			flush();
			set_time_limit(50);
		}
		closedir($dh);
	}
}
?>