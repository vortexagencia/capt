<?php
	ob_start();
	session_start();
	require_once 'dbconnect.php';
	
	// if session is not set this will redirect to login page
	if( !isset($_SESSION['user']) ) {
		header("Location: index.php");
        
		exit;
	}
	// select loggedin users detail
	$res=mysql_query("SELECT * FROM users WHERE userId=".$_SESSION['user']);
	$userRow=mysql_fetch_array($res);
    $mac=$userRow['mac'];
    

?>
<html>
<head>
    
    </head>
<body>
    
    <p>Hi <?php echo $userRow['userName']; ?></p>
    <p>Voce pode usar a rede livremente a partir daqui.</p>
    </body>
</html>