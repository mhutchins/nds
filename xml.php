#!/usr/local/bin/php -q
<?php

        $db = new PDO('mysql:host=localhost;dbname=nds', 'nds');

	$xml = simplexml_load_file("ADVANsCEne_NDScrc.xml");

	foreach($xml->games->game as $game)
	{
		$title = urlencode($game->title);
		$releasenumber = $game->releaseNumber;
		$imagenumber = $game->imageNumber;
		$romsize = $game->romSize;
		$language = $game->language;
		$version = $game->version;
		$wifi = $game->wifi;
		$duplicateid = $game->duplicateid;
		$romcrc = $game->files->romCRC;
		$genre = $game->genre;
		$romid = $game->comment;

		if ($romid == 0) $romid = 9000 + $releasenumber;
		if ($wifi == 'Yes') $wifi = 1;
		else
			$wifi=0;

                $query = "replace into adv set 
			romid = '$romid',
			imagenumber = '$imagenumber',
			releasenumber = '$releasenumber',
			title = '$title',
			romsize = '$romsize',
			location = '$location',
			language = '$language',
			romcrc = '$romcrc',
			genre = '$genre',
			version = '$version',
			wifi = '$wifi',
			duplicateid   = '$duplicateid'
			";


                if ($db->exec($query) == 1)
                        echo "$romid: Updated.\n";
                else
		{
			$err=$db->errorInfo();
			if($err[0] != 0)
			{
				echo "$query\n";
				print_r($err);
			}
		}
	}
?>
