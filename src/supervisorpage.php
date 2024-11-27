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
    <title>Supervisor Dashboard</title>
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
            text-align: center;
        }
        .filter-container {
            margin: 20px;
            text-align: center;
        }
        .add-condition {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<h1 style="text-align: center;">Supervisor Dashboard</h1>

    <div class="filter-container">
    <form method="POST" action="">
        <h3>Filter Projects</h3>
        <div id="filter-section">
            <div>
                <label for="attribute">Attribute:</label>
                <select name="filters[0][attribute]" required>
                    <option value="Project_Status">Project Status</option>
                    <option value="Project_Start_Date">Start Date</option>
                    <option value="Project_End_Date">End Date</option>
                    <option value="Budget_ID">Budget ID</option>
                    <option value="Owner_ID">Owner ID</option>
                </select>

                <label for="operator">Condition:</label>
                <select name="filters[0][operator]" required>
                    <option value="=">Equals</option>
                    <option value="!=">Not Equals</option>
                    <option value="<">Before</option>
                    <option value="<=">On or Before</option>
                    <option value=">">After</option>
                    <option value=">=">On or After</option>
                </select>

                <label for="value">Value:</label>
                <input type="text" name="filters[0][value]" required>
            </div>
        </div>
        <br />
        <button type="submit" name="filterSubmit">Filter</button>
    </form>
</div>


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


function executePlainSQL($cmdstr) 
{ //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;

    $statement = oci_parse($db_conn, $cmdstr);

    if (!$statement) {
        echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
        $e = oci_error($db_conn); // For OCIParse errors pass the connection handle
        echo htmlentities($e['message']);
        $success = False;
    }

    $r = oci_execute($statement, OCI_DEFAULT);
    if (!$r) {
        echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
        $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
        echo htmlentities($e['message']);
        $success = False;
    }

    return $statement;
}


    
function printResult($result) 
{ //prints results from a select statement
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



function handleFilterRequest($supervisor_id)
{
    global $db_conn;
       
 
    // $filters = null;

    // Exit if no filters are provided
    if (empty($_POST['filters'])) {
        return; 
    }
    $filters = $_POST['filters'];
    $query = "SELECT *
              FROM Project 
              WHERE Supervisor_ID = '$supervisor_id'";

if (!empty($filters)) {
    foreach ($filters as $filter) {
        $attribute = $filter['attribute'];
        $operator = $filter['operator'];
        $value = $filter['value'];

    
        if (is_numeric($value)) {
            $value = $value;
        } else {
            $value = "'" . str_replace("'","'",$value)."'";
        }
        $query .= " AND $attribute $operator $value";
    }
}

$result = executePlainSQL($query);

    if ($result) {
        printResult($result);
    } else {
        echo "<p style='color:red; text-align:center;'>No projects match the filter criteria.</p>";
    }

}


if (connectToDB()) {

    if (isset($_GET['supervisor_id'])) {
    $supervisor_id = $_GET['supervisor_id'];
   
    } 

    // $projects = handleFilterRequest($supervisor_id);
    // <p>Total Projects: echo !empty($projects) ? count($projects) : 0;

    disconnectFromDB();
} else {
    $projects = [];
}


// Main logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filterSubmit'])) {
    if (connectToDB()) {
        handleFilterRequest($supervisor_id);
        disconnectFromDB();
    }

        
}



?>

    
</body>
</html>