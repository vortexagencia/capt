<?php
ob_start();
session_start();
require_once 'dbconnect.php';
require_once 'mac.php';
// it will never let you open index(login) page if session is set
if ( isset($_SESSION['user'])!="" ) {
	header("Location: home.php");
    exit;
	}
if( isset($_POST['submit']) ) {	
		
		// prevent sql injections/ clear user invalid inputs
		$email = trim($_POST['email']);
		$email = strip_tags($email);
		$email = htmlspecialchars($email);
		
		$pass = trim($_POST['pass']);
		$pass = strip_tags($pass);
		$pass = htmlspecialchars($pass);
		// prevent sql injections / clear user invalid inputs
		
		if(empty($email)){
			$error = true;
			$msgerror = "Please enter your email address.";
		} else if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
			$error = true;
			$msgerror = "Please enter valid email address.";
		}
		
		if(empty($pass)){
			$error = true;
			$msgerror = "Please enter your password.";
		}
    // if there's no error, continue to login
		if (!$error) {
			/*
			$password = hash('sha256', $pass); // password hashing using SHA256
		
			$res=mysql_query("SELECT userId, userName, userPass FROM users WHERE userEmail='$email'");
			$row=mysql_fetch_array($res);
			$count = mysql_num_rows($res); // if uname/pass correct it returns must be 1 row
			
			if( $count == 1 && $row['userPass']==$password ) {
				$_SESSION['user'] = $row['userId'];
				header("Location: home.php");
			} else {
				$errMSG = "Incorrect Credentials, Try again...";
			}
				
		} */
      
		$query = "INSERT INTO users(userName,userEmail,mac) VALUES('$pass','$email','$mac')";
			$res = mysql_query($query);
				
			if ($res) {
				$errTyp = "success";
				$msgerror = "Successfully registered, you may login now";
              
                $res=mysql_query("SELECT userId, userName, userPass FROM users WHERE userEmail='$email'");
			    $row=mysql_fetch_array($res);
			    $_SESSION['user'] = $row['userId'];
			
                header("Location: home.php");
			} else {
				$errTyp = "danger";
				$msgerror = "Something went wrong, try again later...";	
			}	
	}
}
?>
<html>
<head></head>
<body>
      <?php
			if ( isset($msgerror) ) {
echo $msgerror;
            }
				?>
    <hr>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <input type="email" name="email" placeholder="Email">
    <input type="text" name="pass" placeholder="Nome">
    <input type="submit" value="submit" name="submit">
    
    
    
    </form>    
    
    
    
    
    </body>

    
</html>
<?php ob_end_flush(); ?>