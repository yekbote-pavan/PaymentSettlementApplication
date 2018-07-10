<?php
	define('DBHOST', 'localhost');
	define('DBUSER', 'root');
	define('DBPASS', 'root');
	define('DBNAME', 'doe');
	$conn = mysqli_connect(DBHOST,DBUSER,DBPASS);
	$dbcon = mysqli_select_db($conn,DBNAME);
	if ( !$conn ) {
		die("Connection failed : " . mysqli_error());
	}
	
	if ( !$dbcon ) {
		die("Database Connection failed : " . mysqli_error());
	}
