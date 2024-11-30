<?php

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_nvk2003";			// change "cwl" to your own CWL
$config["dbpassword"] = "a60336625";	// change to 'a' + your student number
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

        // Display for second or further condition rows
        function addConditionRow() {
            const filterSection = document.getElementById('filter-section');

            // Making "Clause" required for all existing rows except the last
            const existingClauseSelects = filterSection.querySelectorAll('select[name^="filters"][name$="[clause]"]');
            existingClauseSelects.forEach((clauseSelect, index) => {
                clauseSelect.disabled = false; 
                clauseSelect.required = true; 
            });

            const newFilterRow = document.createElement('div');
            newFilterRow.setAttribute('id', `filter-row-${conditionCount}`);
            newFilterRow.classList.add('filter-row'); 
            newFilterRow.innerHTML = `
                <label for="attribute">Column:</label>
                <select name="filters[${conditionCount}][attribute]" class="attribute-select" onchange="updateInputType(${conditionCount})" required>
                    <option value="" disabled selected>Select a Column</option>
                    <option value="Project_Status">Project Status</option>
                    <option value="Project_Start_Date">Start Date</option>
                    <option value="Project_End_Date">End Date</option>
                    <option value="Budget_ID">Budget ID</option>
                    <option value="Owner_ID">Owner ID</option>
                </select>

                <label for="operator">Condition:</label>
                <select name="filters[${conditionCount}][operator]" required>
                    <option value="" disabled selected>Select a Condition</option>
                    <option value="=">Equals</option>
                    <option value="!=">Not Equals</option>
                    <option value="<">Less than</option>
                    <option value="<=">Less than or Equal to</option>
                    <option value=">">Greater than</option>
                    <option value=">=">Greater than or Equal to</option>
                </select>

                <label for="value">Value:</label>
                <input type="text" id="value-input-${conditionCount}" name="filters[${conditionCount}][value]" required>

                <label for="clause">Clause:</label>
                <select name="filters[${conditionCount}][clause]">
                    <option value="">Select Clause</option>
                    <option value="AND">AND</option>
                    <option value="OR">OR</option>
                </select>
                
                <button type="button" onclick="removeConditionRow(${conditionCount})">Remove</button>
            `;

            filterSection.appendChild(newFilterRow);
            
            const newClauseSelect = newFilterRow.querySelector('select[name^="filters"][name$="[clause]"]');
            newClauseSelect.disabled = true; 
            newClauseSelect.required = false; 

            conditionCount++;
        }

        // Remove Condition Rows Other Than the First One
        function removeConditionRow(id) {
            const row = document.getElementById(`filter-row-${id}`);
            if (row) {
                row.remove();
            }

            const filterSection = document.getElementById('filter-section');
            const allRows = filterSection.querySelectorAll('.filter-row');
            const allClauseSelects = filterSection.querySelectorAll('select[name^="filters"][name$="[clause]"]');
            
            allClauseSelects.forEach((clauseSelect, index) => {
                clauseSelect.disabled = index === allRows.length - 1; 
                clauseSelect.required = index !== allRows.length - 1; 
            });
        }


        // Update Value input type based on selected attribute
        function updateInputType(id) {
            const attributeSelect = document.querySelector(`#filter-row-${id} select[name="filters[${id}][attribute]"]`);
            const valueInput = document.getElementById(`value-input-${id}`);

            if (attributeSelect.value === "Project_Start_Date" || attributeSelect.value === "Project_End_Date") {
                valueInput.type = "date";
            } else {
                valueInput.type = "text";
            }
        }
    </script>
</head>



<!-- Starting from the top of the page -->
<body>
    <h1 style="text-align: center;">Supervisor Dashboard</h1>

    <!-- Prints the first row for taking a condition -->
    <div class="filter-container">
        <form method="POST" action="">
            <h3>Filter Projects</h3>
            <div id="filter-section">
                <div class="filter-row">
                    <label for="attribute">Column:</label>
                    <select name="filters[0][attribute]" class="attribute-select" required>
                        <option value="" disabled selected>Select a Column</option>
                        <option value="Project_Status">Project Status</option>
                        <option value="Project_Start_Date">Start Date</option>
                        <option value="Project_End_Date">End Date</option>
                        <option value="Budget_ID">Budget ID</option>
                        <option value="Owner_ID">Owner ID</option>
                    </select>

                    <label for="operator">Condition:</label>
                    <select name="filters[0][operator]" required>
                        <option value="" disabled selected>Select a Condition</option>
                        <option value="=">Equals</option>
                        <option value="!=">Not Equals</option>
                        <option value="<">Less than</option>
                        <option value="<=">Less than or Equal to</option>
                        <option value=">">Greater than</option>
                        <option value=">=">Greater than or Equal to</option>
                    </select>

                    <label for="value">Value:</label>
                    <input type="text" name="filters[0][value]" class="value-input" required>

                    <label for="clause">Clause:</label>
                    <select name="filters[0][clause]" disabled>
                        <option value="">Select Clause</option>
                        <option value="AND">AND</option>
                        <option value="OR">OR</option>
                    </select>
                </div>
            </div>
            <br /><br />

            <button type="button" onclick="addConditionRow()">Add Condition</button>
            <br /><br />

            <button type="submit" name="filterSubmit">Filter</button>
        </form>
    </div>

    <script>
        // Changes the input type for Value to Text or Date Based on Selected Attribute
        document.addEventListener("input", function (e) {
            if (e.target.classList.contains("attribute-select")) {
                const selectedAttribute = e.target.value;
                const valueInput = e.target.closest(".filter-row").querySelector(".value-input");

                if (selectedAttribute === "Project_Start_Date" || selectedAttribute === "Project_End_Date") {
                    valueInput.type = "date";
                } else {
                    valueInput.type = "text";
                }
            }
        });
    </script>








    <!-- Button for Aggregation with GROUP BY -->
    <div class="filter-container">
        <form method="POST" action="">
            <button type="submit" name="aggregationQuerySubmit">View Average Materials Cost</button>
        </form>
    </div>


    <!-- Button for Nested Aggregating with GROUP BY -->
    <div class="filter-container">
        <form method="POST" action="">
            <button type="submit" name="nestedQuerySubmit" margin: 20px;>
                View Supervisors Working on High Cost Projects
            </button>
        </form>
    </div>


    <!-- Button for Division Query -->
    <div class="filter-container">
        <form method="POST" action="">
            <button type="submit" name="divisionQuerySubmit" margin: 20px;>
                View Supervisors Managing Projects of All Property Types
            </button>
        </form>
    </div>





    
    <?php
        // Connecting to DB (from Sample Project)
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



        // Disconnecting from DB (from Sample Project)
        function disconnectFromDB()
        {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            oci_close($db_conn);
        }

        // Function for Alert Messages (from Sample Project)
        function debugAlertMessage($message)
        {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/php'>alert('" . $message . "');</script>";
            }
        }







        // Executes general SQL query (from Sample Project)
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






        // Prints the table on to the screen (from Sample Project)
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








        // Does all the filtering for all the conditions 
        function handleFilterRequest($supervisor_id)
        {
            global $db_conn;

            // Exit if no filters are provided
            if (empty($_POST['filters'])) {
                echo "<p style='color:red; text-align:center;'>No filters provided.</p>";
                return;
            }

            $filters = $_POST['filters'];

            $query = "SELECT * FROM Project WHERE Supervisor_ID = '$supervisor_id'"; // Base query
            $conditions = []; // Array to store all filter conditions


            foreach ($filters as $index => $filter) {
                $attribute = trim($filter['attribute']); 
                $operator = trim($filter['operator']);  
                $value = trim($filter['value']);       
                $clause = isset($filter['clause']) && trim($filter['clause']) !== "" ? trim($filter['clause']) : "";


                // Checks and outputs an error if any Invalid Operators are in combination with Invalid Operators
                $invalidOperators = ['<', '>', '<=', '>=']; 
                $invalidAttributes = ['Project_Status', 'Budget_ID', 'Owner_ID']; 

                if (in_array($attribute, $invalidAttributes) && in_array($operator, $invalidOperators)) {
                    echo "<p style='color:red; text-align:center;'>Error: Invalid operator '$operator' for Column '$attribute'.</p>";
                    exit;
                } else {
                    // Converting value to Date Format if selected attribute is Start or End Date
                    // Converts the value to string otherwise
                    if ($attribute === 'Project_Start_Date' || $attribute === 'Project_End_Date') {
                        $value = "TO_DATE('$value', 'YYYY-MM-DD')";
                    } else {
                        $value = "'$value'";
                    }
                
                    
                    $condition = "$attribute $operator $value";
                    // echo "<p style='color:red; text-align:center;'>Condition (line 287): $condition</p>";
                    // echo "<p style='color:red; text-align:center;'>Clause (line 288): $clause</p>";
                    // echo "<p style='color:red; text-align:center;'>Index (line 289): $index</p>"

                    if (count($conditions) > 0 && (strpos(end($conditions), 'OR') !== false || strpos(end($conditions), 'AND') !== false)) {
                        $lastCondition = array_pop($conditions);
                        $condition = "($lastCondition $condition)";
                    }


                    if ($index === 0 && $clause || $index > 0) {
                        $conditions[] = "$condition $clause";
                    } else {
                        $conditions[] = $condition;
                    }
                
                    // echo "<p style='color:red; text-align:center;'>Conditions array: " . implode(' ', $conditions) . "</p>";
                }
            }


            if (!empty($conditions)) {
                $query .= " AND (" . implode(" ", $conditions) . ")";
                // echo "<p style='color:red; text-align:center;'>LINE 336: $query</p>";


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
        }





        // Aggregation with GROUP BY
        // Finding the average material cost grouped by projects using Owner Type
        if (isset($_POST['aggregationQuerySubmit'])) {
            if (connectToDB()) {
                $groupby_sql = "
                    SELECT OE.Owner_Type AS Project_Type, COUNT(P.Project_ID) AS Total_Projects, ROUND(AVG(B.BUDGET_MATERIAL_COST),2) AS Avg_Material_Cost
                    FROM Project P
                    JOIN OwnerEntity OE ON P.Owner_ID = OE.Owner_ID
                    JOIN Budget B ON P.Budget_ID = B.Budget_ID
                    GROUP BY OE.Owner_Type
                ";

                $groupby_result = executePlainSQL($groupby_sql);
                echo "<h3 style='text-align: center;'>Average Material Cost For Each Project Type</h3>";
                printResult($groupby_result);

            }
        }


        
        // Nested Aggregation with GROUP BY
        // Finding supervisors managing projects With Average Total Cost Higher Than Average Total Cost of All Projects
        if (isset($_POST['nestedQuerySubmit'])) {
            if (connectToDB()) {
               $nested_query = "
                SELECT 
                    S.Supervisor_ID, 
                    S.Supervisor_Name
                FROM 
                    Supervisor S
                WHERE EXISTS (
                    SELECT 1
                    FROM Project P
                    JOIN Budget B ON P.Budget_ID = B.Budget_ID
                    WHERE P.Supervisor_ID = S.Supervisor_ID
                    GROUP BY P.Supervisor_ID
                    HAVING AVG(B.Budget_Total_Cost) > (
                        SELECT AVG(B2.Budget_Total_Cost)
                        FROM Budget B2
                    )
                )
                ";

                $nested_result = executePlainSQL($nested_query);

                echo "<h3 style='text-align: center;'>Supervisors Managing Projects With Average Total Cost Higher Than Average Total Cost of All Projects</h3>";


                if ($nested_result) {
                    printResult($nested_result);
                } else {
                    echo "<p style='color: red; text-align: center;'>No results found.</p>";
                }
            }
        }

        // DIVISION
        // Find the supervisors managing projects of all property types
        if (isset($_POST['divisionQuerySubmit'])) {
            if (connectToDB()) {
                $division_sql = "
                SELECT S.Supervisor_ID, S.Supervisor_Name
                FROM Supervisor S
                WHERE NOT EXISTS (
                    SELECT OE.Owner_Type
                    FROM OwnerEntity OE
                    WHERE OE.Owner_Type NOT IN (
                        SELECT DISTINCT OE2.Owner_Type
                        FROM Project P
                        JOIN OwnerEntity OE2 ON P.Owner_ID = OE2.Owner_ID
                        WHERE P.Supervisor_ID = S.Supervisor_ID
                    )
                )";

                $division_result = executePlainSQL($division_sql);
                echo "<h3 style='text-align: center;'>Supervisors Manging Projects of All Property Types</h3>";
                printResult($division_result);
            }
        }
    ?>


    <?php

        if (connectToDB()) {
            // Get the supervisor id from the url
            if (isset($_GET['supervisor_id'])) {
            $supervisor_id = $_GET['supervisor_id'];
            } 
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