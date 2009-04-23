<?php

        mysql_pconnect("localhost", "nds") or die(mysql_error());
        mysql_select_db("nds") or die(mysql_error());

	$query = "select id, name, size from rom where available=true order by name ";
	$data = mysql_query($query);

	while($row = mysql_fetch_array( $data )) 
	{
		$romid=$row['id'];
		$name=urldecode($row['name']);
		$size=round($row['size'] / 1024 / 1024);
		printf("%04d\t% -65s\t%3d\n", $romid, $name, $size);
	}

?>
