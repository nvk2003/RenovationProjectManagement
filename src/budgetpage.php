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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Budget for your Projects</title>
    <style>

    #back_button {
            margin: 20px;
            position: absolute;
            top: 20px;
            right: 10px;
        }


        table {
            margin: 20px auto;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px;
            text-align: center;
            border: 1px solid black;
        }

        .form-container {
           
            margin: 20px;
        }

        
    </style>

</head>
<body>
    <?php
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
            $e = OCI_Error(); 
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


    function executePlainSQL($cmdstr) 
{ 
    global $db_conn, $success;

    $statement = oci_parse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = oci_error($db_conn); 
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); 
        echo htmlentities($e['message']);
        $success = False;
    }

    return $statement;
}


    function printResult($result) 
    { 
    echo "<table>";
    echo "<tr>";
    $ncols = oci_num_fields($result);
    
    for ($i = 1; $i <= $ncols; $i++) {
        $column_name = oci_field_name($result, $i);
        echo "<th>" . htmlentities($column_name, ENT_QUOTES) . "</th>";
    }
    
    echo "</tr>";
    
    while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
        echo "<tr>";
        foreach ($row as $item) {
            echo "<td>" . htmlentities($item, ENT_QUOTES) . "</td>";
        }
        echo "</tr>";
    }
    
        echo "</table>";
    } 


?>


<h1 style="text-align: center;">Budget Details</h1>

<!-- Back button -->
<div id="back_button">
    <form method="GET" action="ownerpage.php">
        <input type="hidden" name="owner_id" value="<?php echo isset($_GET['owner_id']) ? htmlspecialchars($_GET['owner_id']) : ''; ?>">
        <button type="submit">Back to Owner Page</button>
    </form>
</div>

<div class="form-container">
    
        <h3>View Project and Budget Details</h3>
        <form method="POST" action="">
            <label for="project_id">Enter Project ID:</label>
            <input type="text" id="project_id" name="project_id" required>
            <br><br>
            <button type="submit" name="viewProjectSubmit">View Details</button>
        </form>
    </div>	



 
<!-- JOIN FUNCTIONALITY -->
 <div class = "result-container">
<?php

    if (connectToDB()) {
    if (isset($_POST['viewProjectSubmit'])) {
        // Get the input values
        $project_id = $_POST['project_id'];
        $owner_id = isset($_GET['owner_id']) ? $_GET['owner_id'] : null;




        // Check if Project_ID already exists in the database
        $check_sql = "SELECT COUNT(*) FROM PROJECT WHERE PROJECT_ID = :project_id";
        $check_stmt = oci_parse($db_conn, $check_sql);
        oci_bind_by_name($check_stmt, ":project_id", $project_id);
        oci_execute($check_stmt);

        // Fetch the result
        $row = oci_fetch_assoc($check_stmt);
        $project_exists = $row['COUNT(*)']; // This will be 0 if no match is found

        

        // Check if the Project ID exists for the specific Owner ID
        $check_project_sql = "SELECT COUNT(*) FROM PROJECT WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";
        $check_project_stmt = oci_parse($db_conn, $check_project_sql);

        // Bind parameters
        oci_bind_by_name($check_project_stmt, ":project_id", $project_id);
        oci_bind_by_name($check_project_stmt, ":owner_id", $owner_id);

        // Execute the query
        oci_execute($check_project_stmt);
        $owner_row = oci_fetch_assoc($check_project_stmt);
        $owner_project_exists = $owner_row['COUNT(*)'];

        if ($project_exists > 0 && $owner_project_exists > 0) {
            // SQL query to join Project and Budget tables for the specific Project_ID and Owner_ID
                $select_sql = "
                SELECT 
                    p.PROJECT_ID, p.PROJECT_NAME, p.PROJECT_ADDRESS, p.PROJECT_STATUS, 
                    b.BUDGET_ID, b.BUDGET_MATERIAL_COST, b.BUDGET_INITIAL_ESTIMATE, 
                    b.BUDGET_CONTRACTOR_FEES, b.BUDGET_TOTAL_COST, b.BUDGET_WAGE_WORKER_COST
                FROM PROJECT p
                INNER JOIN BUDGET b ON p.BUDGET_ID = b.BUDGET_ID
                WHERE p.PROJECT_ID = :project_id AND p.OWNER_ID = :owner_id
                ";

                $select_stmt = oci_parse($db_conn, $select_sql);

                // Bind parameters
                oci_bind_by_name($select_stmt, ":project_id", $project_id);
                oci_bind_by_name($select_stmt, ":owner_id", $owner_id);

                // Execute the select query
                oci_execute($select_stmt);
                echo "<table>";
            echo "<tr>
                    <th>Project ID</th>
                    <th>Project Name</th>
                    <th>Project Address</th>
                    <th>Project Status</th>
                    <th>Budget ID</th>
                    <th>Material Cost</th>
                    <th>Initial Estimate</th>
                    <th>Contractor Fees</th>
                    <th>Total Cost</th>
                    <th>Wage Worker Cost</th>
                  </tr>";

            while ($row = oci_fetch_assoc($select_stmt)) {
                echo "<tr>";
                echo "<td>" . htmlentities($row['PROJECT_ID']) . "</td>";
                echo "<td>" . htmlentities($row['PROJECT_NAME']) . "</td>";
                echo "<td>" . htmlentities($row['PROJECT_ADDRESS']) . "</td>";
                echo "<td>" . htmlentities($row['PROJECT_STATUS']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_ID']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_MATERIAL_COST']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_INITIAL_ESTIMATE']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_CONTRACTOR_FEES']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_TOTAL_COST']) . "</td>";
                echo "<td>" . htmlentities($row['BUDGET_WAGE_WORKER_COST']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";

                // Fetch the joined data
                // $project_found = false;
                // while ($row = oci_fetch_assoc($select_stmt)) {
                //     $project_found = true;
                    
                //     // echo "<p style='color:blue; text-align:center;'>Project Details:</p>";
                //     // echo "<ul style='text-align:center;'>";
                //     // echo "<td><strong>Project ID:</strong> " . htmlentities($row['PROJECT_ID']) . "</td>";
                //     // echo "<li><strong>Project Name:</strong> " . htmlentities($row['PROJECT_NAME']) . "</li>";
                //     // echo "<li><strong>Project Address:</strong> " . htmlentities($row['PROJECT_ADDRESS']) . "</li>";
                //     // echo "<li><strong>Project Status:</strong> " . htmlentities($row['PROJECT_STATUS']) . "</li>";
                //     // echo "<li><strong>Budget ID:</strong> " . htmlentities($row['BUDGET_ID']) . "</li>";
                //     // echo "<li><strong>Material Cost:</strong> " . htmlentities($row['BUDGET_MATERIAL_COST']) . "</li>";
                //     // echo "<li><strong>Initial Estimate:</strong> " . htmlentities($row['BUDGET_INITIAL_ESTIMATE']) . "</li>";
                //     // echo "<li><strong>Contractor Fees:</strong> " . htmlentities($row['BUDGET_CONTRACTOR_FEES']) . "</li>";
                //     // echo "<li><strong>Total Cost:</strong> " . htmlentities($row['BUDGET_TOTAL_COST']) . "</li>";
                //     // echo "<li><strong>Wage Worker Cost:</strong> " . htmlentities($row['BUDGET_WAGE_WORKER_COST']) . "</li>";
                //     // echo "</ul>";
                // }

                // Output error message if no project found
                // if (!$project_found) {
                //     echo "<p style='color:red; text-align:center;'>No matching project found for the given Project ID or you do not have access to this project.</p>";
                // }js
        } else if ($project_exists > 0 && $owner_project_exists == 0) {
            echo "<p style='color:red; text-align:center;'>You do not have access to this project</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>No matching project found for the given Project ID</p>";
        }
    }
  
}


?>
</div>









</body>
</html>