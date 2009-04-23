#!/usr/local/bin/php -q
<?php
	require("ndsinc.php");

        $db = new PDO('mysql:host=ndsdb;dbname=nds', 'nds');

	$last_processed=2380;

	$query="select id, data from blobdata where type='cover' and id = $last_processed order by id";
	foreach($db->query($query) as $row)
	{
		$romid = $row['id'];
		$data  = $row['data'];

		echo "Cleaning up...\n";
		exec("/usr/bin/rm -rf /tmp/crop-0.png");
		exec("/usr/bin/rm -rf /tmp/crop-1.png");
		echo "Cropping png...\n";
		$fh = popen("convert - -crop 214x192 /tmp/crop.png", "w");
		fwrite($fh, $data);
		$res = $db->query($query)->fetch();
		echo "Inserting pieces\n";
		insert_blob("/tmp/crop-0.png", "cover", $romid);
		insert_blob("/tmp/crop-1.png", "unknown", $romid);
		echo "Processed: $romid\n";
		exit();
	}
?>
