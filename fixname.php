
<?php

	mysql_pconnect("ndsdb", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 

	$query = "select filename, id from rom";
	$res = mysql_query($query);
	while($row = mysql_fetch_assoc( $res )) 
	{
		$romid = $row['id'];		
		$filename = urldecode($row['filename']);		
		$fname = preg_replace('#^.*\\\#', '', $filename);

		echo "ID: [$romid] Name: $fname\n";
/*
		$query = "update rom set 
			filename='$fname'
			where id = '$romid'";
		echo "$romid: ";
		mysql_query($query);
		if (mysql_affected_rows() == 1)
			echo "Done\n";
		else
		{
			echo "Oops!\n";
			echo mysql_error();
		}
*/
	}
?>
