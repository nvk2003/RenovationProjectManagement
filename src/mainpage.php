
<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_jagathi";			// change "cwl" to your own CWL
$config["dbpassword"] = "a81887028";	// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = NULL;	// login credentials are used in connectToDB()

$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP
?>


<!DOCTYPE html>
<html>

<head>
	<title>Renovation Project Management</title>

</head>

<body>
	<div style = "text-align: center; margin-top: 200px;">
		<h1>Welcome</h1>
		<p> Please select you role and enter your credentials to login.</p>
		<form method="POST" action="mainpage.php">
			<label for="Role"> Select your role:</label><br>
			<select name="Role" id= "Role" required>
				<option value="Supervisor">Supervisor</option>
				<option value="Owner">Owner</option>
			</select><br><br>

			<label for="user_id">Enter your ID:</label><br>
			<input type="text" id="user_id" name="user_id" required><br><br>
			<label for ="phone"> Enter Phone Number: </label><br>
			<input type="text" id="phone" name="phone" required><br><br>
			<input type="submit" value="Login" name="loginSubmit">
		</form>
	</div>





	<?php

		function debugAlertMessage($message)
		{
			global $show_debug_alert_messages;

			if ($show_debug_alert_messages) {
				echo "<script type='text/php'>alert('" . $message . "');</script>";
			}
		}


		function connectToDB()
		{
			global $db_conn;
			global $config;

			// Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
			// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
			$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

			if ($db_conn) {
				// echo "<p style='color:green;'>Connected to the database successfully!</p>";
				debugAlertMessage("Database is Connected");
				return true;
			} else {
				debugAlertMessage("Cannot connect to Database");
				$e = OCI_Error(); // For oci_connect errors pass no handle
				echo htmlentities($e['message']);
				return false;
			}
		}

		function initializeDatabase() {
			global $db_conn;
		
			$sql = "
			start script.sql;
			";
		
			$statements = explode(";", $sql); // Split SQL into individual statements
		
			foreach ($statements as $statement) {
				if (trim($statement)) {
					$parsedStatement = oci_parse($db_conn, $statement);
		
					if (!$parsedStatement || !oci_execute($parsedStatement)) {
						$e = oci_error($parsedStatement);
						echo "<p style='color:red;'>Error executing statement: " . htmlentities($e['message']) . "</p>";
					} else {
						echo "<p style='color:green;'>Statement executed successfully: " . htmlentities($statement) . "</p>";
					}
				}
			}
		}
		

		function disconnectFromDB()
		{
			global $db_conn;

			debugAlertMessage("Disconnect from Database");
			oci_close($db_conn);
		}

		



		function isUserValid($role,$id,$phone) 
		{
			global $db_conn;

			$query = "";
			if ($role === "Owner") {
				$query = "SELECT COUNT(*) AS COUNT FROM OwnerEntity WHERE Owner_ID = :id AND Owner_Phone = :phone";
			}elseif ($role === "Supervisor") {
				$query = "SELECT COUNT(*) AS COUNT FROM Supervisor WHERE Supervisor_ID = :id AND Supervisor_Phone = :phone";

			}

			$statement = oci_parse($db_conn, $query);

			if (!$statement) {
				echo "<p style='color:red;'>Cannot parse query: " . htmlentities($query) . "</p>";
				$e = oci_error($db_conn);
				echo htmlentities($e['message']);
				return false;
			}

			oci_bind_by_name($statement, ":id", $id);
			oci_bind_by_name($statement, ":phone", $phone);

			if (!oci_execute($statement)) {
				$e = oci_error($statement);
				echo "<p style='color:red;'>Error executing query: " . htmlentities($e['message']) . "</p>";
				return false;
			}

			$row = oci_fetch_assoc($statement);
			return $row['COUNT'] > 0;

				
			
		}

		//form submission

		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['loginSubmit'])) 
		{
			$role = $_POST['Role'];
			$id = $_POST['user_id'];
			$phone = $_POST['phone'];
		
			if (connectToDB()) {
				if (isUserValid($role, $id, $phone)) {
					if ($role === "Owner") {
						header("Location: ownerpage.php?owner_id=" . urlencode($id));
					} else {
						header("Location: supervisorpage.php?supervisor_id=" . urlencode($id));
					}
					exit();
				} else {
					echo "<p style='color:red; text-align:center;'>Invalid ID or Phone Number. Please try again.</p>";
				}
				disconnectFromDB();

			}

			
		}

	?>
	
</body>
</html>


	
