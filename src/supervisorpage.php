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
        
        .hidden {
            display: none;
        }
    </style>
    <script>
        let conditionCount = 1;





        // Display of Second or further condition rows
        function addConditionRow() {
            const filterSection = document.getElementById('filter-section');
            // event.preventDefault();

            const newFilterRow = document.createElement('div');
            newFilterRow.setAttribute('id', `filter-row-${conditionCount}`);
            newFilterRow.innerHTML = `
                <label for="attribute">Attribute:</label>
                <select name="filters[${conditionCount}][attribute]" required>
                    <option value="Project_Status">Project Status</option>
                    <option value="Project_Start_Date">Start Date</option>
                    <option value="Project_End_Date">End Date</option>
                    <option value="Budget_ID">Budget ID</option>
                    <option value="Owner_ID">Owner ID</option>
                </select>

                <label for="operator">Condition:</label>
                <select name="filters[${conditionCount}][operator]" required>
                    <option value="=">Equals</option>
                    <option value="!=">Not Equals</option>
                    <option value="<">Before</option>
                    <option value="<=">On or Before</option>
                    <option value=">">After</option>
                    <option value=">=">On or After</option>
                </select>

                <label for="value">Value:</label>
                <input type="text" name="filters[${conditionCount}][value]" required>

                <label for="clause">Clause:</label>
               <select name="filters[${conditionCount}][clause]">
    <option value="">Select Clause</option>
    <option value="AND">AND</option>
    <option value="OR">OR</option>
</select>
            
                 <button type="button" onclick="removeConditionRow(${conditionCount})">Remove</button>
            `;

            filterSection.appendChild(newFilterRow);
            conditionCount++;
        }

        function removeConditionRow(id) {
            const row = document.getElementById(`filter-row-${id}`);
            if (row) {
                row.remove();
            }
        }
    </script>


</head>



<!-- Starting from the top of the page -->
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
                    <option value="<">Less than</option>
                    <option value="<=">Less than or Equal to</option>
                    <option value=">">Greater than</option>
                    <option value=">=">Greater than or Equal to</option>
                </select>

                <label for="value">Value:</label>
                <input type="text" name="filters[0][value]" required>

                <label for="clause">Clause:</label>
                <select name="filters[0][clause]">
                <option value="">Select Clause</option>
    
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>    
                </select>

            </div>
        </div>
        <br />
        <br />

        <button type="submit" onclick="addConditionRow()">Add Condition </button>
        <br /> <br />

        <button type="submit" name="filterSubmit">Filter</button>
    </form>
</div>

<div class="filter-container">
    <form method="POST" action="">
        <button type="submit" name="aggregationQuerySubmit">View Average Materials Cost</button>
    </form>
</div>







<!-- Connecting to DB -->
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







// Executes general SQL query 
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






// Prints the table on to the screen
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
        // Correct query: SELECT *
//   2  FROM Project
//   3  WHERE Supervisor_ID = 'S001' AND (Project_Status = 'In Progress' OR Project_Status = 'Not Started');
}








// Does all the filtering with all the conditions here
function handleFilterRequest($supervisor_id)
{
    global $db_conn;

    // Exit if no filters are provided
    if (empty($_POST['filters'])) {
        echo "<p style='color:red; text-align:center;'>No filters provided.</p>";
        return;
    }

    $filters = $_POST['filters']; // Chosen Attribute
    $query = "SELECT * FROM Project WHERE Supervisor_ID = '$supervisor_id'"; // Base query
    $conditions = []; // To store all filter conditions

    // foreach ($filters as $index => $filter) {
    //     $attribute = trim($filter['attribute']); 
    //     $operator = trim($filter['operator']);  // SQL operator
    //     $value = trim($filter['value']);        // Value to filter on
    //     $clause = isset($filter['clause']) && trim($filter['clause']) !== "" ? trim($filter['clause']) : ""; // Logical clause

    //     // echo "<p style='color:red; text-align:center;'>$clause</p>";


    //     if ($attribute === 'Project_Start_Date' || $attribute === 'Project_End_Date') {
    //         $value = "TO_DATE('$value', 'YYYY-MM-DD')";
    //     } elseif (!is_numeric($value)) {
    //         $value = "'$value'";
    //     }


    //     $condition = "$attribute $operator $value";
    //     echo "<p style='color:red; text-align:center;'>Condition (line 287): $condition</p>";
    //     echo "<p style='color:red; text-align:center;'>Clause (line 288): $clause</p>";
    //     echo "<p style='color:red; text-align:center;'>Index (line 289): $index</p>";


    
    //     if ($index > 0 && $clause) {
    //         $conditions[] = "$clause $condition";
    //         // echo "<p style='color:red; text-align:center;'>Conditions array: $attribute</p>";
    //         echo "<p style='color:red; text-align:center;'>Conditions array: " . implode(', ', $conditions) . "</p>";
    //     } 
    //         // $conditions[] = "$condition";
    // }


    foreach ($filters as $index => $filter) {
        $attribute = trim($filter['attribute']); 
        $operator = trim($filter['operator']);  // SQL operator
        $value = trim($filter['value']);        // Value to filter on
        $clause = isset($filter['clause']) && trim($filter['clause']) !== "" ? trim($filter['clause']) : ""; // Logical clause
    
        // Format date values and strings properly
        if ($attribute === 'Project_Start_Date' || $attribute === 'Project_End_Date') {
            $value = "TO_DATE('$value', 'YYYY-MM-DD')";
        } elseif (!is_numeric($value)) {
            $value = "'$value'";
        }
    
        // Construct condition for this filter
        $condition = "$attribute $operator $value";
            echo "<p style='color:red; text-align:center;'>Condition (line 287): $condition</p>";
        echo "<p style='color:red; text-align:center;'>Clause (line 288): $clause</p>";
        echo "<p style='color:red; text-align:center;'>Index (line 289): $index</p>";
    
        // Add condition to the array
        if ($index === 0 && $clause || $index > 0) {
            // For the first condition, add it without any clause
            $conditions[] = "$condition $clause";
        } else {
            // For subsequent conditions, append the clause after the condition
            $conditions[] = $condition;
        }
    
        // Debug: Show all conditions so far
        echo "<p style='color:red; text-align:center;'>Conditions array: " . implode(' ', $conditions) . "</p>";
    }


    if (!empty($conditions)) {
        // echo "<p style='color:red; text-align:center;'>$conditions</p>";
        $query .= " AND (" . implode(" ", $conditions) . ")";
        echo "<p style='color:red; text-align:center;'>LINE 336: $query</p>";


    }
   

    
    $result = executePlainSQL($query);

    if ($result) {
        $resultsFound = false;
        while ($row = oci_fetch_assoc($result)) {
            if (!$resultsFound) {
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($row) as $column) {
                    echo "<th>" . htmlentities($column) . "</th>";
                }
                echo "</tr>";
                $resultsFound = true;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlentities($value) . "</td>";
            }
            echo "</tr>";
        }
        if ($resultsFound) {
            echo "</table>";
        } else {
            echo "<p style='color:red; text-align:center;'>No projects match the filter criteria.</p>";
        }
    } else {
        
        $e = oci_error($db_conn);
        echo "<p style='color:red; text-align:center;'>Error executing query: " . htmlentities($e['message']) . "</p>";
    }
}



// Aggregation with GROUP BY
if (isset($_POST['aggregationQuerySubmit'])) {
    if (connectToDB()) {
        // Aggregation query to calculate average material cost grouped by project type
        $groupby_sql = "
            SELECT OE.Owner_Type AS Project_Type, COUNT(P.Project_ID) AS Total_Projects, ROUND(AVG(B.BUDGET_MATERIAL_COST),2) AS Avg_Material_Cost
            FROM Project P
            INNER JOIN OwnerEntity OE ON P.Owner_ID = OE.Owner_ID
            INNER JOIN Budget B ON P.Budget_ID = B.Budget_ID
            GROUP BY OE.Owner_Type
        ";

        // Execute the query
        $groupby_result = executePlainSQL($groupby_sql);
        printResult($groupby_result);

    }
}

?>


        <!-- // // Display the results in a table
        // echo "<div class='result-container'>";
   
        // echo "<table>";
        // echo "<tr>
        //         <th>Project Type</th>
        //         <th>Total Projects</th>
        //         <th>Average Material Cost</th>
        //       </tr>";

        // while ($row = oci_fetch_assoc($groupby_result)) {
        //     echo "<tr>";
        //     echo "<td>" . htmlentities($row['Project_Type']) . "</td>";
        //     echo "<td>" . htmlentities($row['Total_Projects']) . "</td>";
        //     echo "<td>" . htmlentities(number_format($row['Avg_Material_Cost'], 2)) . "</td>";
        //     echo "</tr>";
        // }

        // echo "</table>";
        // echo "</div>";

  -->







<!-- 
// function handleFilterRequest($supervisor_id)
// {

//     global $db_conn;

//     if (empty($_POST['filters'])) {
//         echo "<p style='color:red; text-align:center;'>No filters provided.</p>";
//         return;
//     }

//     $filters = $_POST['filters'];
//     $query = "SELECT * FROM Project WHERE Supervisor_ID = '$supervisor_id'";
//     $conditions = [];

//     foreach ($filters as $index => $filter) {
//         $attribute = trim($filter['attribute']);
//         $operator = trim($filter['operator']);
//         $value = trim($filter['value']);
//         $clause = isset($filter['clause']) && trim($filter['clause']) !== "" ? trim($filter['clause']) : "";

//         if ($attribute === 'Project_Start_Date' || $attribute === 'Project_End_Date') {
//             $value = "TO_DATE('$value', 'YYYY-MM-DD')";
//         } elseif (!is_numeric($value)) {
//             $value = "'$value'";
//         }

//         $conditions[] = ($index > 0 && $clause ? "$clause " : "") . "$attribute $operator $value";
//     }

//     if (!empty($conditions)) {
//         $query .= " AND (" . implode(" ", $conditions) . ")";
//     }

//     $result = executePlainSQL($query);

//     if ($result) {
//         if (oci_fetch_assoc($result)) {
//             oci_execute($result); // Re-execute to fetch rows
//             printResult($result);
//         } else {
//             echo "<p style='color:red; text-align:center;'>No projects match the filter criteria.</p>";
//         }
//     } else {
//         echo "<p style='color:red; text-align:center;'>Error executing query.</p>";
//     }
// }


 -->

<?php

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