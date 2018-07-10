<?php
	ob_start();
	session_start();
    require_once 'doeconnect.php';
    if( isset($_POST['btn-login']) ) 
    {	
        $username = $_POST['username'];
        $pass = $_POST['password']; 
        $query = "SELECT Username,Password,AccountType FROM Users WHERE username = '$username'";
        $result = mysqli_query($conn,$query);
        if($result)
        {
            $row = mysqli_fetch_array($result);
            $password = hash('sha256', $pass);
            if( $row['Password'] == $password )
            {
                $_SESSION['username'] = $row['Username'];
                if($row['AccountType'] == 'Admin')
                {
                    header("Location: adminHome.php");
                }
                else if($row['AccountType'] == 'Merchant')
                {
                    header("Location: merchantHome.php");
                }
                else
                {
                    header("Location: home.php");
                }
            }   
            else
            {
                echo "Incorrect Password";
            }
        }
        else
        {
            echo "Error : Query failed to execute";
        }		
	}
		
?>
<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="CSS/Form.css" type="text/css"/>
</head>
<body background="CSS/thumb-1920-840086.jpg">
<div class="login-page">
  <div class="form">
    <form class ="login-form" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
    <a class="message">LOGIN</a>
    <br>    
    <br>    
            <input type="text" name="username" class="form-control" placeholder="Username" maxlength="64">
            <br>
            <input type="password" name="password" class="form-control" placeholder="Password" maxlength="64">
            <button type="submit" class="btn btn-block btn-primary" name="btn-login">Login</button>
			<br><br>
            <a href="register.php">Not Registered? Sign Up Here</a>
    </form>
  </div>	
</div>
</body>
</html>
<?php ob_end_flush(); ?>