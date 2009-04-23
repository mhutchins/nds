<?php
	require "ndslib.php";
        $db = new PDO('mysql:host=localhost;dbname=nds', 'nds');

	update_roms(array("source" => "/tmp", "dest" => "/nds"));
?>
