<?php

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_cwl";			// change "cwl" to your own CWL
$config["dbpassword"] = "a12345678";	// change to 'a' + your student number
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
    <style>
        table {
            border-collapse: collapse;
            margin: 0 auto;
        }
        table, th, td {
            border: 1px solid black;

        }

        th, td {
            padding: 8px;
        }

        .hidden {
            display: none;
        }
        .button-container {
            margin: 10px;
            text-align: center;
            position: absolute;
            
        }

        #add_review_form {
            margin-top: 20px; 
            position: absolute;
        
        }

    </style>
        
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
            <button onclick="toggleForm()"> Add Review </button>
        </div>

        <div id="add_review_form" class="hidden" style="text-align: center;">
        <h3>Add a Review</h3>
        <form method="POST" action="">
            <label for="Review_ID">Review ID:</label>
            <input type="text" id="review_id" name="review_id" required><br><br>

            <label for="Review_Date">Review Date:</label>
            <input type="date" id="review_date" name="review_date" required><br><br>

            <label for="Review_Rating">Review Rating (1-5):</label>
            <input type="number" id="review_rating" name="review_rating" min="1" max="5" required><br><br>

            <label for="Review_Comment">Review Comment:</label><br>
            <textarea id="review_comment" name="review_comment" rows="4" cols="50" required></textarea><br><br>

            <input type="hidden" name="owner_id" value="<?php echo isset($owner_id) ? htmlentities($owner_id) : ''; ?>">
            <input type="hidden" name="owner_phone" value="<?php echo isset($owner_phone) ? htmlentities($owner_phone) : ''; ?>">

            <button type="submit" name="addReviewSubmit">Submit Review</button>
        </form>
    </div>

    <script>
        function toggleForm() {
            var form = document.getElementById("add_review_form");
            if (form.classList.contains("hidden")) {
                form.classList.remove("hidden");
            } else {
                form.classList.add("hidden");
            }
        }
    </script>
      
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addReviewSubmit'])) {
    $Review_ID = $_POST['review_id'];
    $Review_Date = $_POST['review_date'];
    $Review_Rating = $_POST['review_rating'];
    $Review_Comment = $_POST['review_comment'];
    $Owner_ID = $_POST['owner_id'];
    $Owner_Phone = $_POST['owner_phone'];

    if (connectToDB()) {
       
        $query = "INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID, Owner_Phone)
                  VALUES (:review_id, :review_date, :review_rating, :review_comment, :owner_id, :owner_phone)";
        $statement = oci_parse($db_conn, $query);

        oci_bind_by_name($statement, ":review_id", $Review_ID);
        oci_bind_by_name($statement, ":review_date", $Review_Date);
        oci_bind_by_name($statement, ":review_rating", $Review_Rating);
        oci_bind_by_name($statement, ":review_comment", $Review_Comment);
        oci_bind_by_name($statement, ":owner_id", $Owner_ID);
        oci_bind_by_name($statement, ":owner_phone", $Owner_Phone);
        
        

        if (oci_execute($statement)) {
            echo "<p style='color:green; text-align:center;'>Review added successfully!</p>";
        } else {
            $e = oci_error($statement);
            echo "<p style='color:red; text-align:center;'>Error adding review: " . htmlentities($e['message']) . "</p>";
        }

        disconnectFromDB();
    }
}
?>
