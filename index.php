<?php
	require "ndslib.php";


	$dir = "c:\users\martin\\nds";

	$html_header = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0//EN' 'http://www.w3.org/TR/REC-html40/strict.dtd'>";
	$html_header .= "<html lang=en-us>\n";
	$html_header .= "<head>\n";
	$html_header .= "<TITLE>Rom Manager</TITLE>\n";
	$html_header .= "<link   type='text/css' href='css/style.css' rel='stylesheet'>\n";
	$html_header .= "<script type='text/javascript' src='js/jquery/jquery.js'></script>\n";
	$html_header .= "<script type='text/javascript' src='js/javascript.js'></script>\n";
	$html_header .= "<script type='text/javascript' src='js/script.js'></script>\n";
	$html_header .= "<script type='text/javascript' src='js/altalt.js'></script>\n";
	$html_header .= "</head>\n";
	$html_header .= "<BODY >\n";



 
   $PHP_SELF=$_SERVER['PHP_SELF'];
	session_name("nds");
	session_start();

    @$debug=($_REQUEST['debug'] == "y") ? "Y" : "N";
    @$action=($_REQUEST['action'] == "") ? "none" : $_REQUEST['action'];
    @$subaction=($_REQUEST['subaction'] == "") ? "none" : $_REQUEST['subaction'];
	
	//ob_start();
	//echo $html_header;

        $db = new PDO('mysql:host=ndsdb;dbname=nds', 'nds');

	if (!isset($_COOKIE['sessionid']))
	{
		do_login();
	}
	else
	{
		$sess = $_COOKIE['sessionid'];
		$query = "select id from user where sess_id = '$sess'";
		$row = $db->query($query)->fetch();
		if ($row == false)
			do_login();
		else
		{
			$userid = $row['id'];
			$_SESSION['userid'] = $userid;

		
			switch(strtolower($action))
			{
				case 'users':
							user_maint();
							break;
				case 'getimg':
							$image = get_blob($_REQUEST['blobid']);
							ob_clean();
							header('Content-Length: '.strlen($image));
							header("Content-Transfer-Encoding: base64");
							header("Content-type: image/png");
							print $image;
							ob_end_flush();
							exit(1);
							break;
				case 'details':
							ob_clean();
							show_details();
							exit(1);
				
				case 'edit':
							edit_card();
							break;
							
				case 'quickadd':
							quickadd_roms();
							exit(1);
							break;
							
				case 'add_card':
							add_card();
							break;
				case 'add':
							show_roms();
							break;
							
				case 'show_roms':
							echo $html_header;
							show_roms();
							break;
							
				case 'update_roms':
							update_roms();
							break;
							
				case 'logout':
							header('Location: ' . $_SERVER['PHP_SELF']);
							setcookie("sessionid", null);
							break;
				case 'vote':
							//var_dump($_REQUEST);
							$romid=$_REQUEST['romid'];
							$rating=$_REQUEST['value'];
							$ret=setvote($romid, $rating);
							echo $ret['avg'];
							exit(1);
							break;

				case 'show_cards':
				default:
							show_cards();
							break;
			}
			echo "<table>";
			echo "<tr>";
			echo "<td><a href='?action=logout'>Logout</a></td>";
			echo "</tr></table>";
		}
	}	
	//header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	ob_end_flush();
	



?>
