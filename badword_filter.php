<?php


	mysql_pconnect("localhost", "nds") or die(mysql_error()); 
	mysql_select_db("nds") or die(mysql_error()); 

	$query = "select id, description from rom where id > 2300";

	echo "<pre>";

	$stopwords=array();
	
	$fp = fopen("badwords.txt", "r");
	while (!feof($fp))
	{
		$word = rtrim(trim (fgets($fp)), ";");
		echo "Word: [$word]\n";
		array_push($stopwords, $word);
	}

	array_push($stopwords, " rape");
	array_push($stopwords, " long dick");
	array_push($stopwords, " small dick");
	array_push($stopwords, " huge dick");
	array_push($stopwords, " my dick");

	array_push($stopwords, " arse");

	$res = mysql_query($query);
	while($row = mysql_fetch_assoc( $res )) 
	{
		$desc = stripslashes($row['description']);
		$romid = $row['id'];
		echo "ROMID: $romid\n";
		$desc = addslashes(str_ireplace($stopwords, "****", $desc));
		mysql_query("update rom set description = '$desc' where id = '$romid'");
			ob_flush();
			flush();
			set_time_limit(50);
	}
	
	echo "</pre>";
?>
