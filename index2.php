<html>
<head><title>RSA Test</title></head>
<body bgcolor=#ffffff text=#000000>
<?php
$challenge = false;
$loginAccepted = false;
$error = false;

if (isset($_POST['uname'])) {
	$radius = radius_auth_open();

	if (!radius_add_server($radius,'{SERVERS_IP}',1645,'{SHARED_SECRET}',60,1)) {
		$error = radius_strerror($radius);
	} else if (!radius_create_request($radius,RADIUS_ACCESS_REQUEST)) {
		$error = radius_strerror($radius);
	} else {

		radius_put_attr($radius,RADIUS_USER_NAME,$_POST['uname']);
		if (isset($_POST['challenge'])) {
			radius_put_attr($radius,RADIUS_USER_PASSWORD,$_POST['challenge']);
			radius_put_attr($radius, RADIUS_STATE, $_POST['state']);
		} else {
			radius_put_attr($radius,RADIUS_USER_PASSWORD,$_POST['upw']);
		}


		$result = radius_send_request($radius);
		if ($result == RADIUS_ACCESS_ACCEPT) {
			$loginAccepted = true;
	
		} else if ($result == RADIUS_ACCESS_REJECT) {
			$loginAccepted = false;
	
		} else if ($result == RADIUS_ACCESS_CHALLENGE) {
			// When we get a challenge, return the response as the password
			// and return RADIUS_STATE as given
		
			$challenge = true;
			$challengePrompt = false;
			$challengeState = false;
			$challengeStatus = false;
	
	
			// loop through attributes.
			while ($attrArray = radius_get_attr($radius)) {
				if (!is_array($attrArray)) break;
				if ($attrArray['attr'] == RADIUS_REPLY_MESSAGE) $challengePrompt = $attrArray['data'];
				if ($attrArray['attr'] == RADIUS_STATE) {
					$challengeState = false;
					$parts = explode('|', $attrArray['data']);
					if (sizeof($parts) == 2) {
						if (strlen($parts[0]) == 12) {
							if (strcmp(substr($parts[0], 0, 8), "SECURID_") == 0) {
								$challengeStatus = substr($parts[0], 8);
								$challengeState = $attrArray['data'];
							}
						}
					}
				}
			}
			if ($challengePrompt === false) $error = "Error receiving challenge prompt";
	
		} else {
			$error = radius_strerror($radius);
		}
	}
	if ($error !== false) {
		print "There was an error trying to authenticate.<br>";
		print "<i>" . $error . "</i><br>";
		print "<hr>";
	}
}
?>
<!-- PRESENT THE LOGIN FORM -->
<?
	if ($challenge && (strcmp($challengeStatus, "WAIT") != 0)) {
		print "<h1>SecurID Challenge</h1>";
	} else if ($challenge && (strcmp($challengeStatus, "WAIT") == 0)) {
		print "<h1>SecurID Response Accepted</h1>";
		print "<h3>" . $challengePrompt . "</h3>";
	} else if ($loginAccepted) {
		print "<h1>SecurID Login Accepted</h1>";
	} else if (!isset($_POST['uname'])) {
		print "<h1>SecurID Login</h1>";
	} else {
		print "<h1>SecurID Login Failure</h1>";
		print "<h3>Please try again</h3>";
		print "<em>(if after two tries, you're still getting a failure, try just your token code)</em>";
	}
?>

<form action=<? print $_SERVER['PHP_SELF']; ?> method=POST>
	Username: <input type=text name=uname size=30 value="blt"><br>

<!-- IF WE'RE NOT ANSWERING A CHALLENGE, PRESENT THE REGULAR PROMPT. -->
<!-- WAIT STATE MEANS WE JUST ANSWERED A RESPONSE SUCCESSFULLY. -->
<!-- ALSO SHOW REGULAR PROMPT AFTER ERROR -->
<? if (!$challenge || (strcmp($challengeStatus, "WAIT") == 0) || ($error !== false)) { ?>
	Password: <input type=password name=upw size=30 value=""><br>
<? } else { ?>
	<hr><? print $challengePrompt; ?><br>
	Response: <input type=password name=challenge size=30 value=""><br>
	<input type=hidden name=state value="<? print addslashes($challengeState); ?>">
	<hr>
<? } ?>
<input type=submit name=submit value="Log In">
</form>
</body>
</html>