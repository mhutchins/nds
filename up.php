#!/usr/local/bin/php -q
<?php
$DAT_FILE="http://www.advanscene.com/offline/datas/ADVANsCEne_NDScrc.zip";
$DAT_FILE="http://www.advanscene.com/offline/datas/ADVANsCEne_NDS.zip";
	$TMPFILE=tempnam("/tmp", "advanscene");

	$get_xml = 1;
	if ($get_xml == 1)
	{
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$DAT_FILE);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
		$buffer = curl_exec($curl_handle);
		curl_close($curl_handle);

		file_put_contents($TMPFILE, $buffer);

		$zip = new ZipArchive();
		if ($zip->open($TMPFILE) !== TRUE) {
			die ("Could not open archive");
		}

		//if (($index = $zip->locateName('ADVANsCEne_NDScrc.xml', ZIPARCHIVE::FL_NODIR)) !== false)
		if (($index = $zip->locateName('ADVANsCEne_NDS.xml', ZIPARCHIVE::FL_NODIR)) !== false)
		{
			//$zip->extractTo('/tmp', 'ADVANsCEne_NDScrc.xml');
			$zip->extractTo('/tmp', 'ADVANsCEne_NDS.xml');
			echo "New XML extracted\n";
		}
		else
			echo "Unable to extract XML\n";

		// close archive
		$zip->close();

	}

        $db = new PDO('mysql:host=192.168.2.100;dbname=nds', 'nds');

        //$xml = simplexml_load_file("/tmp/ADVANsCEne_NDScrc.xml");
        $xml = simplexml_load_file("/tmp/ADVANsCEne_NDS.xml");

	echo "Version: " . $xml->configuration->datVersion;
/*
  ["imageNumber"]=> string(4) "3010"
  ["releaseNumber"]=> string(4) "3010"
  ["title"]=> string(23) "007 - Quantum of Solace"
  ["saveType"]=> string(15) "Eeprom - 4 kbit"
  ["romSize"]=> string(8) "33554432"
  ["publisher"]=> string(10) "Activision"
  ["location"]=> string(1) "1"
  ["sourceRom"]=> string(10) "XenoPhobia"
  ["language"]=> string(1) "3"
  ["files"]=> object(SimpleXMLElement)#9 (1) {
    ["romCRC"]=>
    string(8) "2F1ACA8C"
  }
  ["im1CRC"]=> string(8) "954AA489"
  ["im2CRC"]=> string(8) "EF3E4699"
  ["comment"]=> string(4) "2934"
  ["duplicateID"]=> string(3) "707"

*/
	
        foreach($xml->games->game as $game)
        {
                $title = urlencode($game->title);
                $releasenumber = $game->releaseNumber;
                $imagenumber = $game->imageNumber;
                $romsize = $game->romSize;
                $language = $game->language;
		//lang($language);
                $location = location2txt($game->location);
                $version = $game->version;
                $wifi = $game->wifi;
                $duplicateid = $game->duplicateID;

                $romcrc = $game->files->romCRC;
                $genre = $game->genre;
                $romid = $game->comment;
		if ($romid == "3783" )
		{
			var_dump($game);
			lang($language);
		}
		echo "Romid: $romid...";

		if (($language & bindec('00000000000000000010')) == 0)
		{
			echo "Skipping: Not english\n";
			continue;
		}

		if ($romid == "xxxx")
		{
			echo "Skipping: Demo\n";
			continue;
		}

                if ($romid == 0) $romid = 9000 + $releasenumber;

                if ($wifi == 'Yes') $wifi = 1;
                else
                        $wifi=0;

		if ($duplicateid > 0)
		{
			$query = "insert into dupelist values (:dupeid, :romid)";
			echo "$query\n";
			$sth = $db->prepare($query);
			$sth->execute(array(':dupeid' => $duplicateid, ':romid' => $romid));
			// Check if we have a 'bad' master already

			$query = "select  cr.romid from card_rom cr, blobdata b  where cr.romid = $romid and  cr.romid=b.id and b.type='rom' and cr.romid not in (select romid from adv);";
			$row = $db->query($query)->fetch();
			if ($row['romid'] != "")	// This rom *should* be the master
			{
				$query = "delete from dupe where dupeid = :dupeid";
				$sth = $db->prepare($query);
				$sth->execute(array(':dupeid' => $duplicateid));
				$query = "insert into dupe values (:dupeid, :romid)";
				echo "$query\n";
				$sth = $db->prepare($query);
				$sth->execute(array(':dupeid' => $duplicateid, ':romid' => $romid));
			}
		

			$query="select dupeid from dupe where master=$romid";
			$row = $db->query($query)->fetch();
			if ( $row['dupeid'] == "" )	// Has duplicates, and this version is not listed as master
			{
				$query="select dupeid from dupe where dupeid=$duplicateid";
				$row = $db->query($query)->fetch();
				if ( $row['dupeid'] == "" )	// Has duplicates, and this version is not listed as master
				{
					echo "No Master selected for dupeid $duplicateid\n";
				}
				echo "Skipping: potential duplicate ($duplicateid)\n";
				continue;
			}
		}
		echo "Updating...\n";

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
			echo "No change: ";
                        $err=$db->errorInfo();
                        if($err[0] != 0)
                        {
                                echo "$query";
                                print_r($err);
                        }
			echo "\n";
                }

	}


function lang($lang)
{
	if ($lang & bindec('00000000000000000001')) echo "France    ";
	if ($lang & bindec('00000000000000000010')) echo "English   ";
	if ($lang & bindec('00000000000000000100')) echo "Chinese   ";
	if ($lang & bindec('00000000000000001000')) echo "Danish    ";
	if ($lang & bindec('00000000000000010000')) echo "Dutch     ";
	if ($lang & bindec('00000000000000100000')) echo "Finnish   ";
	if ($lang & bindec('00000000000001000000')) echo "German    ";
	if ($lang & bindec('00000000000010000000')) echo "Italian   ";
	if ($lang & bindec('00000000000100000000')) echo "Japanese  ";
	if ($lang & bindec('00000000001000000000')) echo "Norwegian ";
	if ($lang & bindec('00000000010000000000')) echo "Czech     ";
	if ($lang & bindec('00000000100000000000')) echo "Portugese ";
	if ($lang & bindec('00000001000000000000')) echo "Spanish   ";
	if ($lang & bindec('00000010000000000000')) echo "Swedish   ";
	if ($lang & bindec('00010000000000000000')) echo "Korean    ";
	if ($lang & bindec('00100000000000000000')) echo "Russian   ";
	if ($lang & bindec('01000000000000000000')) echo "Greek     ";
	if ($lang >= bindec('10000000000000000000')) echo "'UNKNOWN'-$lang ";
}
function location2txt($location)
{
	switch($location)
	{
		case 0:
			return("Eur");
		case 1:
			return("Usa");
		case 2:
			return("Ger");
		case 3:
			return("Chi");
		case 4:
			return("Spn");
		case 5:
			return("Fra");
		case 6:
			return("Ita");
		case 7:
			return("Jap");
		case 8:
			return("Ndl");
		case 19:
			return("Aus");
		case 22:
			return("Kor");
		case 27:
			return("Rus");
	}
	return("Unknown: $location");
}
?>
