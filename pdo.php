<?php
	$db = new PDO('mysql:host=localhost;dbname=gallery', 'gallery');

	$stmt = $db->prepare("select g_userName from g2_User where g_id=?");

	$stmt->bindParam(1, $id, PDO::PARAM_INT);
	$stmt->bindColumn(1, $name, PDO::PARAM_STR, 256);

	for($id = 1 ; $id < 10; $id++)
	{
		$stmt->execute();
		$stmt->fetch(PDO::FETCH_BOUND);
		echo "For id:[$id] name is [$name]\n"; 
	}

?>
