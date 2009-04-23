<?php
        ini_set("zlib.output_compression", 1);

	require("ndsinc.php");
	//echo "<pre>";
	//var_dump($GLOBALS);

        $db = new PDO('mysql:host=ndsdb;dbname=nds', 'nds');

        if (!isset($_COOKIE['sessionid']))
        {
                do_login();
		exit();
        }
        else
        {
                $sess = $_COOKIE['sessionid'];
                $query = "select id from user where sess_id = '$sess'";
                $row = $db->query($query)->fetch();
                if ($row == false)
		{
                        do_login();
			exit();
		}
	}


	$romid=$_REQUEST['romid'];
	$type=$_REQUEST['type'];

	$query="select b.data, length(b.data) len, a.title from blobdata b, adv a where a.romid = b.id and b.id = '$romid' and b.type = '$type'";
	//echo "Query: $query";
	$res = $db->query($query)->fetch();
	$data = $res['data'];
	$len = $res['len'];
	$name = $res['title'];
	
	switch($type)
	{
		case "rom":
			header("Pragma: public"); 
			header("Expires: 0"); // set expiration time 
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			// browser must download file from server instead of cache 
			// force download dialog 
			header("Content-Type: application/force-download"); 
			header("Content-Type: application/octet-stream", FALSE); 
			header("Content-Type: application/download", FALSE); 
			header("Content-Disposition: attachment; filename=${name}.nds"); 
			header("Content-Transfer-Encoding: binary"); 
			break;
		case "info":
			header('Content-Type: text/plain'); 
			break;
		default:
			header("Content-Transfer-Encoding: base64");
			header("Content-type: image/png");
	}
	header('Content-Length: '. $len);

	print $data;
?>
