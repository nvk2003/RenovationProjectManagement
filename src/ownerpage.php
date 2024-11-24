<?php

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

function connectToDB()
	{
		global $db_conn;
		global $config;

		
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

    function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/php'>alert('" . $message . "');</script>";
		}
	}

    function getOwnerProject($owner_id) 
    {
        global $db_conn;
        $query = "SELECT Project_ID,Project_Name,Project_Address,Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Supervisor_Phone, Budget_ID
                  FROM Project
                  WHERE Owner_ID = :owner_id";
        
        $statement = oci_parse($db_conn, $query);
        oci_bind_by_name($statement, ":owner_id", $owner_id);
         
        if (!oci_execute($statement)) {
			$e = oci_error($statement);
			echo "<p style='color:red;'>Error fetching projects: " . htmlentities($e['message']) . "</p>";
			return [];
		}

        $projects = [];
         while ($row = oci_fetch_assoc($statement)) {
        $projects[] = $row;
        }

        return $projects;

    }

    $owner_id = '0001';
 
    
    if (connectToDB()) {
        $projects = getOwnerProject($owner_id);
        disconnectFromDB();
    } else {
        $projects = [];
    }
    
    
?>

<!DOCTYPE html>
<html>
<head> 
    <title> Owner Dashboard </title>
</head>
<body>
    <div style="text-align: center;">
        <h1>Owner Dashboard</h1>
        <h2>Your Projects</h2>
        <table style="margin: 0 auto;">
            <thead>
            <tr>
                <th>Project ID</th>
                <th>Project Name</th>
                <th>Project Address</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Supervisor Id</th>
                <th>Supervisor Phone</th>
                <th>Budget</th>
            </tr>
            </thead>
            <tbody>
                 <?php if (!empty($projects)): ?>
                     <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlentities($project['PROJECT_ID']); ?></td>
                        <td><?php echo htmlentities($project['PROJECT_NAME']); ?></td>
                        <td><?php echo htmlentities($project['PROJECT_ADDRESS']); ?></td>
                        <td><?php echo htmlentities($project['PROJECT_START_DATE']); ?></td>
                        <td><?php echo htmlentities($project['PROJECT_END_DATE']); ?></td>
                        <td><?php echo htmlentities($project['PROJECT_STATUS']); ?></td>
                        <td><?php echo htmlentities($project['SUPERVISOR_ID']); ?></td>
                        <td><?php echo htmlentities($project['SUPERVISOR_PHONE']); ?></td>
                        <td><?php echo htmlentities($project['BUDGET_ID']); ?></td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No projects found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
        
        <div class = "button-container">

        <form method="GET" action="addReview.php">
            <input type="hidden" name="Owner_ID" value="<?php echo htmlentities($owner_id); ?>">
            <button type="submit">Add Review</button>
        </form>
        </div>
      
</body>
</html>


