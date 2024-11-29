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
            padding-top: 40px;
            margin: 10px;
            text-align: center;
            position: absolute;
            
        }

        #add_review_form {
            margin-top: 60px; 
            position: absolute;
            left:10px;
            padding-bottom: 50px;
            
        
        }

        #add_project_form {
            margin-top: 60px; 
            position: absolute;
            right:10px;
            padding-bottom: 50px;
            
        }

        #delete_project_form {
            margin-top: 60px; 
            position: absolute;
            right:150px;
            padding-bottom: 50px;
            
        }

        #update_project_form {
            margin-top: 60px; 
            position: absolute;
            left:150px;
            padding-bottom: 50px;
        }

        #join_project_form {
            margin-top: 60px; 
            position: absolute;
            right:250px;
            padding-bottom: 50px;
        }

        #view_budget_button {
            position: absolute;
            top: 30px;
            right:30px;
            
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

    //to get supervisors for drop down in project
    $supervisors = [];
    if (connectToDB()) {
        $query = "SELECT Supervisor_ID, Supervisor_Name 
                  FROM Supervisor";

        $statement = oci_parse($db_conn, $query);
        if (oci_execute($statement)) {
            while ($row = oci_fetch_assoc($statement)) {
                $supervisors[] = $row;
            }
        } else {
            $e = oci_error($statement);
            echo "<p style='color:red;'>Error fetching supervisors: " . htmlentities($e['message']) . "</p>";
        }
        disconnectFromDB();
    }

    // function getOwnerProject($owner_id) 
    // {
    //     global $db_conn;
    //     // Changed $query to $sql
    //     $sql = "SELECT Project_ID, Project_Name, Project_Address, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Supervisor_Phone, Budget_ID
    //             FROM Project
    //             WHERE Owner_ID = :owner_id";
        
    //     $statement = oci_parse($db_conn, $sql); // Changed $query to $sql
    //     oci_bind_by_name($statement, ":owner_id", $owner_id);
    //     // echo "<p style='color:blue; text-align:center;'>Owner ID In getOwnerProject: " . htmlentities($owner_id) . "</p>"; // Should comment it out
        
    //     if (!oci_execute($statement)) {
    //         $e = oci_error($statement);
    //         echo "<p style='color:red;'>Error fetching projects: " . htmlentities($e['message']) . "</p>";
    //         return [];
    //     }

    //     $projects = [];
    //     while ($row = oci_fetch_assoc($statement)) {
    //     $projects[] = $row;
    //     }

    //     // foreach ($projects as $project) {
    //     //     echo "<p style='color:green; text-align:center;'>Project ID: " . htmlentities($project['Project_ID']) . ", Project Name: " . htmlentities($project['Project_Name']) . "</p>";
    //     // }

    //     // echo "<p style='color:blue; text-align:center;'>Number of projects: " . count($projects) . "</p>";

        


    //     return $projects;

    // }

    function getOwnerType($owner_id) 
    {
        global $db_conn;

        // First, fetch the Owner_Type for the given Owner_ID
        $owner_type_sql = "SELECT Owner_Type FROM OwnerEntity WHERE Owner_ID = :owner_id";
        $owner_type_stmt = oci_parse($db_conn, $owner_type_sql);
        oci_bind_by_name($owner_type_stmt, ":owner_id", $owner_id);

        if (!oci_execute($owner_type_stmt)) {
            $e = oci_error($owner_type_stmt);
            echo "<p style='color:red;'>Error fetching owner type: " . htmlentities($e['message']) . "</p>";
            return [];
        }

        $owner_type = null;
        while ($row = oci_fetch_assoc($owner_type_stmt)) {
            $owner_type = $row['OWNER_TYPE'];
        }

        if (!$owner_type) {
            echo "<p style='color:red;'>Owner type not found for Owner_ID: " . htmlentities($owner_id) . "</p>";
            return [];
        }

        return $owner_type;
    }




    
    function getOwnerProject($owner_id, $owner_type) 
    {
        global $db_conn;

        // Modify the query based on the Owner_Type
        $sql = "SELECT P.Project_ID, P.Project_Name, P.Project_Address, P.Project_Start_Date, P.Project_End_Date, 
                    P.Project_Status, P.Supervisor_ID, P.Supervisor_Phone, P.Budget_ID";

        if ($owner_type === "Residential") {
            $sql .= ", R.Property_Type, R.No_of_rooms_To_Renovate 
                    FROM Project P 
                    JOIN ResidentialProject R ON P.Project_ID = R.Project_ID
                    WHERE P.Owner_ID = :owner_id";
        } elseif ($owner_type === "Commercial") {
            $sql .= ", C.Business_Type 
                    FROM Project P 
                    JOIN CommercialProject C ON P.Project_ID = C.Project_ID
                    WHERE P.Owner_ID = :owner_id";
        }

        $statement = oci_parse($db_conn, $sql);
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

        // echo "<p style='color:blue; text-align:center;'>Number of projects: " . count($projects) . "</p>";

        // echo "<p style='color:blue; text-align:center;'>Owner Type: " . $owner_type . "</p>";

        return $projects;
    }




















    if (connectToDB()) {

        if (isset($_GET['owner_id'])) {
        $owner_id = $_GET['owner_id'];
        // echo "<p style='color:blue; text-align:center;'>Owner ID: " . htmlentities($owner_id) . "</p>"; // Debug statement to verify the owner_id
        } 
        // else {
        //     echo "<p style='color:red;'>Owner ID not set!</p>"; // should comment this and the above echo statements
        // }

        $owner_type = getOwnerType($owner_id);
        $projects = getOwnerProject($owner_id, $owner_type);
        
        // <p>Total Projects: echo !empty($projects) ? count($projects) : 0;

        disconnectFromDB();
    } else {
        $projects = [];
    }
?>


    


<?php
if (isset($_POST['viewColumns'])) {
    if (!empty($_POST['columns'])) {

        $selected_columns = $_POST['columns'];

        $core_columns = [];
        $residential_columns = [];
        $commercial_columns = [];

        foreach ($selected_columns as $column) {
            if (in_array($column, ["PROJECT_ID", "PROJECT_NAME", "PROJECT_ADDRESS", "PROJECT_START_DATE", "PROJECT_END_DATE", "PROJECT_STATUS", "SUPERVISOR_ID", "SUPERVISOR_PHONE", "BUDGET_ID", "OWNER_ID", "OWNER_PHONE"])) {
                $core_columns[] = $column;
            } elseif (in_array($column, ["PROPERTY_TYPE", "NO_OF_ROOMS_TO_RENOVATE"])) {
                $residential_columns[] = $column;
            } elseif ($column === "BUSINESS_TYPE") {
                $commercial_columns[] = $column;
            }
        }

        // Example Usage:
        // echo "Core Columns: " . implode(", ", $core_columns) . "<br>";
        // echo "Residential Columns: " . implode(", ", $residential_columns) . "<br>";
        // echo "Commercial Columns: " . implode(", ", $commercial_columns) . "<br>";

        // $prefixed_core_columns = array_map(function($column) {
        //     return "P." . $column;
        // }, $core_columns);
        
        // // Combine the columns into a string for the SQL query
        // $project_columns_to_select = implode(", ", $prefixed_core_columns);
        
        // Debugging: Output the prefixed columns
        // echo "Prefixed Columns: " . $project_columns_to_select;




        // Join the selected columns into a comma-separated string for the query
        $project_columns_to_select = implode(", ", $core_columns);

        // SQL query to fetch the selected columns
        // $sql = "SELECT $project_columns_to_select";

        // Add conditional columns based on owner type
        if ($owner_type === "Residential" && !empty($residential_columns) && !empty($project_columns_to_select)) {
            $prefixed_core_columns = array_map(function($column) {
                return "P." . $column;
            }, $core_columns);
            
            // Combine the columns into a string for the SQL query
            $prefixed_columns_to_select = implode(", ", $prefixed_core_columns);


            $residential_columns_to_select = implode(", ", $residential_columns);
            $sql = "SELECT $prefixed_columns_to_select, $residential_columns_to_select 
                    FROM Project P 
                    JOIN ResidentialProject R ON P.Project_ID = R.Project_ID 
                    WHERE P.Owner_ID = :owner_id";
        } elseif ($owner_type === "Commercial" && !empty($commercial_columns) && !empty($project_columns_to_select)) {
            $prefixed_core_columns = array_map(function($column) {
                return "P." . $column;
            }, $core_columns);
            
            // Combine the columns into a string for the SQL query
            $prefixed_columns_to_select = implode(", ", $prefixed_core_columns);


            $commercial_columns_to_select = implode(", ", $commercial_columns);
            $sql = "SELECT $prefixed_columns_to_select, $commercial_columns_to_select 
                    FROM Project P 
                    JOIN CommercialProject C ON P.Project_ID = C.Project_ID 
                    WHERE P.Owner_ID = :owner_id";
        } elseif ($owner_type === "Residential" && !empty($residential_columns) && empty($project_columns_to_select)) {
            $residential_columns_to_select = implode(", ", $residential_columns);
            $sql = "SELECT $residential_columns_to_select 
                    FROM Project P 
                    JOIN ResidentialProject R ON P.Project_ID = R.Project_ID 
                    WHERE P.Owner_ID = :owner_id";
        } elseif ($owner_type === "Commercial" && !empty($commercial_columns) && empty($project_columns_to_select)) {
            $commercial_columns_to_select = implode(", ", $commercial_columns);
            $sql = "SELECT $commercial_columns_to_select 
                    FROM Project P 
                    JOIN CommercialProject C ON P.Project_ID = C.Project_ID 
                    WHERE P.Owner_ID = :owner_id";
        } else {
            $sql = "SELECT $project_columns_to_select FROM Project WHERE Owner_ID = :owner_id";
        }


        $statement = oci_parse($db_conn, $sql);
        oci_bind_by_name($statement, ":owner_id", $owner_id);

        if (oci_execute($statement)) {
            $results = [];
            while ($row = oci_fetch_assoc($statement)) {
                $results[] = $row;
            }
        } else {
            $e = oci_error($statement);
            echo "<p style='color:red;'>Error fetching columns: " . htmlentities($e['message']) . "</p>";
        }
    } else {
        echo "<p style='color:red; text-align: center;'>No columns selected!</p>";
    }
}
?>




<div style="text-align: center;">
    <h1>Owner Dashboard</h1>    
    <h2>Select Columns to View From Projects</h2>
    <form method="POST" action="">
        <fieldset style="display: inline-block; text-align: left;">
            <!-- <legend>Select Columns</legend> -->
            <!-- <label>
                <input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this)"> Select All (Remove this & script below (line 409, 436))
            </label><br><br> -->
            <label><input type="checkbox" name="columns[]" value="PROJECT_ID"> Project ID</label><br>
            <label><input type="checkbox" name="columns[]" value="PROJECT_NAME"> Project Name</label><br>
            <label><input type="checkbox" name="columns[]" value="PROJECT_ADDRESS"> Project Address</label><br>
            <label><input type="checkbox" name="columns[]" value="PROJECT_START_DATE"> Start Date</label><br>
            <label><input type="checkbox" name="columns[]" value="PROJECT_END_DATE"> End Date</label><br>
            <label><input type="checkbox" name="columns[]" value="PROJECT_STATUS"> Status</label><br>
            <label><input type="checkbox" name="columns[]" value="SUPERVISOR_ID"> Supervisor ID</label><br>
            <label><input type="checkbox" name="columns[]" value="SUPERVISOR_PHONE"> Supervisor Phone</label><br>
            <label><input type="checkbox" name="columns[]" value="BUDGET_ID"> Budget ID</label><br>
            <label><input type="checkbox" name="columns[]" value="OWNER_ID"> Owner ID</label><br>
            <label><input type="checkbox" name="columns[]" value="OWNER_PHONE"> Owner Phone</label><br>
            <?php if ($owner_type === "Residential"): ?>
                <label><input type="checkbox" name="columns[]" value="PROPERTY_TYPE"> Property Type</label><br>
                <label><input type="checkbox" name="columns[]" value="NO_OF_ROOMS_TO_RENOVATE"> No. of Rooms To Renovate</label><br>
            <?php elseif ($owner_type === "Commercial"): ?>
                <label><input type="checkbox" name="columns[]" value="BUSINESS_TYPE"> Business Type</label><br>
            <?php endif; ?>
        </fieldset>
        <div style="margin-top: 10px;">
            <button type="submit" name="viewColumns">View Selected Columns</button>
        </div>
    </form>
    
</div>

<!-- <script>
    // JavaScript function to toggle all checkboxes
    function toggleCheckboxes(selectAllCheckbox) {
        // Get all checkboxes inside the form
        const checkboxes = document.querySelectorAll('input[name="columns[]"]');
        
        // Set their checked state based on the "Select All" checkbox
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
    }
</script> -->



<?php
// Mapping of database column names to user-friendly names
$column_display_names = [
    "PROJECT_ID" => "Project ID",
    "PROJECT_NAME" => "Project Name",
    "PROJECT_ADDRESS" => "Project Address",
    "PROJECT_START_DATE" => "Start Date",
    "PROJECT_END_DATE" => "End Date",
    "PROJECT_STATUS" => "Status",
    "SUPERVISOR_ID" => "Supervisor ID",
    "SUPERVISOR_PHONE" => "Supervisor Phone",
    "BUDGET_ID" => "Budget ID",
    "OWNER_ID" => "Owner ID",
    "OWNER_PHONE" => "Owner Phone",
    "PROPERTY_TYPE" => "Property Type",
    "NO_OF_ROOMS_TO_RENOVATE" => "No. of Rooms to Renovate",
    "BUSINESS_TYPE" => "Business Type"
];
?>

<?php if (!empty($results)): ?>
    <div style="text-align: center;">
        <h3>Selected Columns From Projects</h3>
        <table style="margin: 0 auto; border: 1px solid black; border-collapse: collapse;">
            <thead>
                <tr>
                    <?php foreach ($selected_columns as $column): ?>
                        <th style="border: 1px solid black; padding: 5px;">
                            <?php echo htmlentities($column_display_names[$column] ?? $column); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <?php foreach ($selected_columns as $column): ?>
                            <td style="border: 1px solid black; padding: 5px;"><?php echo htmlentities($row[$column]); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p style="text-align: center;">No results to display.</p>
<?php endif; ?>






    <!-- <div style="text-align: center;">
    <h1>Owner Dashboard</h1>
    <h2>Your Projects</h2>
    <p>Total Projects: <?php echo !empty($projects) ? count($projects) : 0; ?></p>
    <p>Owner Type: <?php echo $owner_type; ?></p>
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



            <?php if ($owner_type === "Residential"): ?>
                <th>Property Type</th>
                <th>No. of Rooms to Renovate</th>
            <?php elseif ($owner_type === "Commercial"): ?>
                <th>Business Type</th>
            <?php endif; ?>



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



                <?php if ($owner_type === "Residential"): ?>
                    <td><?php echo htmlentities($project['PROPERTY_TYPE']); ?></td>
                    <td><?php echo htmlentities($project['NO_OF_ROOMS_TO_RENOVATE']); ?></td>
                <?php elseif ($owner_type === "Commercial"): ?>
                    <td><?php echo htmlentities($project['BUSINESS_TYPE']); ?></td>
                <?php endif; ?>



                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No projects found.</td>
                        <?php if ($owner_type === "Residential"): ?>
                            <td colspan="11">No projects found.</td>
                        <?php elseif ($owner_type === "Commercial"): ?>
                            <td colspan="10">No projects found.</td>
                        <?php endif; ?>

                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div> -->







    <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addReviewSubmit'])) {
        $review_id = $_POST['review_id'];
        $review_date = $_POST['review_date'];
        $review_rating = $_POST['review_rating'];
        $review_comment = $_POST['review_comment'];


        // Get the supervisor's phone number based on the supervisor_id
        $owner_phone_sql = "SELECT OWNER_PHONE FROM OWNERENTITY WHERE OWNER_ID = :owner_id";
        $owner_stmt = oci_parse($db_conn, $owner_phone_sql);
        oci_bind_by_name($owner_stmt, ":owner_id", $owner_id);
        oci_execute($owner_stmt);

        $owner_phone = null;
        $owner_row = oci_fetch_assoc($owner_stmt);
        if ($owner_row) {
            $owner_phone = $owner_row['OWNER_PHONE'];
            // echo "<p style='color:red;'>Error: Supervisor phone number not found for Supervisor ID $owner_phone.</p>";
            
        }


        if (connectToDB()) {
        
            $query = "INSERT INTO Review (Review_ID, Review_Date, Review_Rating, Review_Comment, Owner_ID, Owner_Phone)
                    VALUES (:review_id, TO_DATE(:review_date, 'YYYY-MM-DD'), :review_rating, :review_comment, :owner_id, :owner_phone)";
            $statement = oci_parse($db_conn, $query);

            oci_bind_by_name($statement, ":review_id", $review_id);
            oci_bind_by_name($statement, ":review_date", $review_date);
            oci_bind_by_name($statement, ":review_rating", $review_rating);
            oci_bind_by_name($statement, ":review_comment", $review_comment);
            oci_bind_by_name($statement, ":owner_id", $owner_id);
            oci_bind_by_name($statement, ":owner_phone", $owner_phone);
            
            

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


        
    <div class = "button-container" style="left: 10px;">
        <button onclick="toggleForm('add_review_form')"> Add Review </button>
    </div>

    <div id="add_review_form" class="hidden">
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





    <?php
        if (isset($_POST['addProjectSubmit'])) {
            // Collect data from the form
            $project_id = $_POST['project_id'];
            $project_name = $_POST['project_name'];
            $project_address = $_POST['project_address'];
            $project_start_date = $_POST['project_start_date'];
            $project_end_date = $_POST['project_end_date'];
            // $project_status = $_POST['status'];
            $supervisor_id = $_POST['supervisor_id'];
            $budget_id = $_POST['budget_id'];
            if ($owner_type === "Residential") {
                $property_type = $_POST['property_type'];
                $no_of_rooms = $_POST['no_of_rooms_to_renovate'];
            } else if ($owner_type === "Commercial") {
                $business_type = $_POST['business_type'];
            }
            // $owner_id = $owner_id;
            // echo "<p style='color:red;'>Error: Supervisor phone number not found for Supervisor ID $business_type.</p>";
            // $owner_id = $_POST['owner_id'];
            // $owner_phone = $_POST['owner_phone'];


            // Get the supervisor's phone number based on the supervisor_id
            $supervisor_phone_sql = "SELECT SUPERVISOR_PHONE FROM SUPERVISOR WHERE SUPERVISOR_ID = :supervisor_id";
            $supervisor_stmt = oci_parse($db_conn, $supervisor_phone_sql);
            oci_bind_by_name($supervisor_stmt, ":supervisor_id", $supervisor_id);
            oci_execute($supervisor_stmt);

            $supervisor_phone = null;
            $supervisor_row = oci_fetch_assoc($supervisor_stmt);
            if ($supervisor_row) {
                $supervisor_phone = $supervisor_row['SUPERVISOR_PHONE'];
                // echo "<p style='color:red;'>Error: Supervisor phone number not found for Supervisor ID $supervisor_phone.</p>";
            }


            // Get the supervisor's phone number based on the supervisor_id
            $owner_phone_sql = "SELECT OWNER_PHONE FROM OWNERENTITY WHERE OWNER_ID = :owner_id";
            $owner_stmt = oci_parse($db_conn, $owner_phone_sql);
            oci_bind_by_name($owner_stmt, ":owner_id", $owner_id);
            oci_execute($owner_stmt);

            $owner_phone = null;
            $owner_row = oci_fetch_assoc($owner_stmt);
            if ($owner_row) {
                $owner_phone = $owner_row['OWNER_PHONE'];
                // echo "<p style='color:red;'>Error: Supervisor phone number not found for Supervisor ID $owner_phone.</p>";
                
            }



            $check_budget_sql = "SELECT COUNT(*) FROM BUDGET WHERE BUDGET_ID = :budget_id";
            $check_budget_stmt = oci_parse($db_conn, $check_budget_sql);
            oci_bind_by_name($check_budget_stmt, ":budget_id", $budget_id);
            oci_execute($check_budget_stmt);

            // Fetch the result
            $row = oci_fetch_assoc($check_budget_stmt);
            $budget_exists = $row['COUNT(*)']; // This will be 0 if no match is found


            // Insert a new Budget record with default values (all numeric fields as 0)
            $insert_budget_sql = "INSERT INTO BUDGET (BUDGET_ID, BUDGET_MATERIAL_COST, BUDGET_INITIAL_ESTIMATE, BUDGET_CONTRACTOR_FEES, BUDGET_TOTAL_COST, BUDGET_WAGE_WORKER_COST)
            VALUES (:budget_id, 0, 0, 0, 0, 0)";
            $insert_budget_stmt = oci_parse($db_conn, $insert_budget_sql);
            oci_bind_by_name($insert_budget_stmt, ":budget_id", $budget_id); // Ensure $budget_id is the same as used for the project
            
            // if (oci_execute($insert_budget_stmt)) {
            //     echo "<p style='color:green; text-align:center;'>Budget added successfully!</p>";
            // } else {
            //     $e = oci_error($insert_budget_stmt);
            //     echo "<p style='color:red; text-align:center;'>Error inserting budget: " . htmlentities($e['message']) . "</p>";
            // }


            // Check if Project_ID already exists in the database
            $check_sql = "SELECT COUNT(*) FROM PROJECT WHERE PROJECT_ID = :project_id";
            $check_stmt = oci_parse($db_conn, $check_sql);
            oci_bind_by_name($check_stmt, ":project_id", $project_id);
            oci_execute($check_stmt);

            // Fetch the result
            $row = oci_fetch_assoc($check_stmt);
            $project_exists = $row['COUNT(*)']; // This will be 0 if no match is found




            if ($project_exists > 0 || $budget_exists > 0) {
                // If the project already exists, show an error message
                if ($project_exists > 0) {
                    echo "<p style='color:red; text-align:center;'>Error: Project ID already exists.</p>";
                } else {
                    echo "<p style='color:red; text-align:center;'>Error: Budget ID already exists.</p>";
                }
                
            } else {
                // Execute the Budget ID Tuple if there is not existing Budget_ID
                oci_execute($insert_budget_stmt);


                // If the project doesn't exist, insert the new project along with supervisor phone
                $insert_sql = "INSERT INTO PROJECT (PROJECT_ID, PROJECT_NAME, PROJECT_ADDRESS, PROJECT_START_DATE, PROJECT_END_DATE, PROJECT_STATUS, SUPERVISOR_ID, SUPERVISOR_PHONE, BUDGET_ID, OWNER_ID, OWNER_PHONE)
                                VALUES (:project_id, :project_name, :project_address, TO_DATE(:project_start_date, 'YYYY-MM-DD'), TO_DATE(:project_end_date, 'YYYY-MM-DD'), 'Not Started', :supervisor_id, :supervisor_phone, :budget_id, :owner_id, :owner_phone)";

                // $insert_sql = "INSERT INTO Project (Project_ID, Project_Name, Project_Address, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Supervisor_Phone, Budget_ID, Owner_ID, Owner_Phone)
                //                 VALUES ('P100', 'New Project', '123 Main St.', TO_DATE('2024-12-01', 'YYYY-MM-DD'), TO_DATE('2025-12-01', 'YYYY-MM-DD'), 'Active', 'S001', '6789012345', 'B100', 'O001', '9876543210')";

                
                $insert_stmt = oci_parse($db_conn, $insert_sql);
                oci_bind_by_name($insert_stmt, ":project_id", $project_id);
                oci_bind_by_name($insert_stmt, ":project_name", $project_name);
                oci_bind_by_name($insert_stmt, ":project_address", $project_address);
                oci_bind_by_name($insert_stmt, ":project_start_date", $project_start_date);
                oci_bind_by_name($insert_stmt, ":project_end_date", $project_end_date);
                // oci_bind_by_name($insert_stmt, ":project_status", $project_status);
                oci_bind_by_name($insert_stmt, ":supervisor_id", $supervisor_id);
                oci_bind_by_name($insert_stmt, ":supervisor_phone", $supervisor_phone);
                oci_bind_by_name($insert_stmt, ":budget_id", $budget_id);
                oci_bind_by_name($insert_stmt, ":owner_id", $owner_id);
                oci_bind_by_name($insert_stmt, ":owner_phone", $owner_phone);

                // Execute the insertion query
                if (oci_execute($insert_stmt)) {
                    echo "<p style='color:green; text-align:center;'>Project added successfully!</p>";
                } else {
                    $e = oci_error($insert_stmt);
                    echo "<p style='color:red; text-align:center;'>Error inserting project: " . htmlentities($e['message']) . "</p>";
                }

                

                if ($owner_type === "Residential") {
                    $property_type = $_POST['property_type'] ?? null;
                    $no_of_rooms = $_POST['no_of_rooms_to_renovate'] ?? null;
                    // Insert into ResidentialProject table

                    $insert_child_sql = "INSERT INTO ResidentialProject (PROJECT_ID, PROPERTY_TYPE, NO_OF_ROOMS_TO_RENOVATE)
                                            VALUES (:project_id, :property_type, :no_of_rooms)";

                    $insert_child_stmt = oci_parse($db_conn, $insert_child_sql);
                    oci_bind_by_name($insert_child_stmt, ":project_id", $project_id);
                    oci_bind_by_name($insert_child_stmt, ":property_type", $property_type);
                    oci_bind_by_name($insert_child_stmt, ":no_of_rooms", $no_of_rooms);

                    if (oci_execute($insert_child_stmt)) {
                        // echo "<p style='color:green; text-align:center;'>Project added successfully!</p>";
                    } else {
                        $e = oci_error($insert_stmt);
                        echo "<p style='color:red; text-align:center;'>Error inserting Child project: " . htmlentities($e['message']) . "</p>";
                    }

                }
                elseif ($owner_type === "Commercial") {
                    $business_type = $_POST['business_type'] ?? null;
                    // Insert into CommercialProject table
                    $insert_child_sql = "INSERT INTO CommercialProject (PROJECT_ID, BUSINESS_TYPE)
                                            VALUES (:project_id, :business_type)";

                    $insert_child_stmt = oci_parse($db_conn, $insert_child_sql);
                    oci_bind_by_name($insert_child_stmt, ":project_id", $project_id);
                    oci_bind_by_name($insert_child_stmt, ":business_type", $business_type);

                    if (oci_execute($insert_child_stmt)) {
                        // echo "<p style='color:green; text-align:center;'>Project added successfully!</p>";
                    } else {
                        $e = oci_error($insert_stmt);
                        echo "<p style='color:red; text-align:center;'>Error inserting Child project: " . htmlentities($e['message']) . "</p>";
                    }
                }

            }
        }
    ?> 


    <div class = "button-container" style="right: 10px;">
        <button onclick="toggleForm('add_project_form')"> Add Project </button>
    </div>

    <div id="add_project_form" class="hidden">
        <h3>Add a Project</h3>
        <form method="POST" action="">
            <label for="Project_ID">Project ID:</label>
            <input type="text" id="project_id" name="project_id" required><br><br>

            <label for="Project_Name">Project Name:</label>
            <input type="text" id="project_name" name="project_name" required><br><br>

            <label for="Project_Address">Project Address:</label>
            <input type="text" id="project_address" name="project_address" required><br><br>

            <label for="Start_Date">Start Date:</label>
            <input type="date" id="project_start_date" name="project_start_date" required><br><br>

            <label for="End_Date">End Date:</label>
            <input type="date" id="project_end_date" name="project_end_date" required><br><br>

            <!-- <label for="Status">Status:</label>
            <input type="text" id="status" name="status" required><br><br> -->

            <label for="Supervisor_ID">Supervisor ID:</label>
            <select id="supervisor_id" name="supervisor_id" required>
                <option value="" disabled selected>Select a Supervisor</option>
                <?php foreach ($supervisors as $supervisor): ?>
                    <option value="<?php echo htmlentities($supervisor['SUPERVISOR_ID']); ?>">
                        <?php echo htmlentities($supervisor['SUPERVISOR_ID'] . " - " . $supervisor['SUPERVISOR_NAME']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

        
            <!-- <label for="Supervisor_Phone">Supervisor Phone:</label>
            <input type="text" id="supervisor_phone" name="supervisor_phone" required><br><br> -->

            <label for="Budget_ID">Budget ID:</label>
            <input type="text" id="budget_id" name="budget_id" required><br><br>

            <?php if ($owner_type === "Residential"): ?>
                <!-- Additional Fields for Residential -->
                <label for="Property_Type">Property Type:</label>
                <input type="text" id="property_type" name="property_type" required><br><br>

                <label for="No_of_Rooms_To_Renovate">No. of Rooms to Renovate:</label>
                <input type="number" id="no_of_rooms_to_renovate" name="no_of_rooms_to_renovate" required><br><br>
            <?php elseif ($owner_type === "Commercial"): ?>
                <!-- Additional Fields for Commercial -->
                <label for="Business_Type">Business Type:</label>
                <input type="text" id="business_type" name="business_type" required><br><br>
            <?php endif; ?>

            <button type="submit" name="addProjectSubmit">Submit Project</button>

        </form>
    </div>








    <?php
        if (isset($_POST['deleteProjectSubmit'])) {
            // Get the input values
            $project_id = $_POST['project_id'];

            // Retrieve and echo the Budget_ID associated with the project before deleting it
            $select_budget_sql = "SELECT BUDGET_ID FROM PROJECT WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";
            $select_budget_stmt = oci_parse($db_conn, $select_budget_sql);

            // Bind parameters
            oci_bind_by_name($select_budget_stmt, ":project_id", $project_id);
            oci_bind_by_name($select_budget_stmt, ":owner_id", $owner_id);

            // Execute the select query to get the Budget_ID
            oci_execute($select_budget_stmt);

            // Fetch the BUDGET_ID
            $budget_id = null;
            while ($row = oci_fetch_assoc($select_budget_stmt)) {
                $budget_id = $row['BUDGET_ID'];
            }

            // Output the BUDGET_ID being removed
            if ($budget_id) {
                echo "<p style='color:blue; text-align:center;'>The Budget ID being removed: " . htmlentities($budget_id) . "</p>";
            }

            // After deleting the project, delete the associated budget
            $delete_budget_sql = "DELETE FROM BUDGET WHERE BUDGET_ID = :budget_id";
            
            // Prepare the delete query for the Budget table
            $delete_budget_stmt = oci_parse($db_conn, $delete_budget_sql);
            
            // Bind the parameter
            oci_bind_by_name($delete_budget_stmt, ":budget_id", $budget_id);




            // SQL query to delete the project for the specific Owner_ID
            $delete_sql = "DELETE FROM PROJECT WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";

            $delete_stmt = oci_parse($db_conn, $delete_sql);

            // Bind the parameters
            oci_bind_by_name($delete_stmt, ":project_id", $project_id);
            oci_bind_by_name($delete_stmt, ":owner_id", $owner_id);

            // Execute the query
            if (oci_execute($delete_stmt)) {
                if (oci_num_rows($delete_stmt) > 0) {
                    echo "<p style='color:green; text-align:center;'>Project deleted successfully!</p>";
                } else {
                    echo "<p style='color:orange; text-align:center;'>No matching project found for this Owner ID.</p>";
                }
            } else {
                $e = oci_error($delete_stmt);
                echo "<p style='color:red; text-align:center;'>Error deleting project: " . htmlentities($e['message']) . "</p>";
            }
            
            // Execute the query to delete the budget
            if (oci_execute($delete_budget_stmt)) {
                //  echo "<p style='color:green; text-align:center;'>Associated Budget deleted successfully!</p>";
            } else {
                $e = oci_error($delete_budget_stmt);
                echo "<p style='color:red; text-align:center;'>Error deleting associated budget: " . htmlentities($e['message']) . "</p>";
            }
        }
    ?>


<div class="button-container" style="right: 150px;">
        <button onclick="toggleForm('delete_project_form')"> Delete Project </button>
    </div>

    <div id="delete_project_form" class="hidden form-container">
        <h3>Delete Project</h3>
        <form method="POST" action="">
            <label for="Project_ID">Project ID:</label>
            <input type="text" id="project_id" name="project_id" required><br><br>

            <button type="submit" name="deleteProjectSubmit">Delete Project</button>
        </form>
    </div>






    <!-- UPDATE FUNCTIONALITY -->

    <?php
    if (isset($_POST['updateProject'])) {
        // Get the input values
        $project_id = $_POST['project_id'];
        $attribute = $_POST['attribute'];
        $new_value = $_POST['new_value'];

        // echo "<p style='color:green; text-align:center;'>Attribute: $attribute</p>";
        // echo "<p style='color:green; text-align:center;'>New Value: $new_value</p>";



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
            if ($attribute === "Supervisor_ID") {
                // Make the Supervisor_ID to UpperCase
                $new_value = strtoupper($new_value);


                // Check if the new Supervisor_ID exists in the Supervisor table
                $check_supervisor_sql = "SELECT * FROM Supervisor WHERE Supervisor_ID = :new_value";
                $check_supervisor_stmt = oci_parse($db_conn, $check_supervisor_sql);

                // Bind the parameter
                oci_bind_by_name($check_supervisor_stmt, ":new_value", $new_value);

                // Execute the query
                oci_execute($check_supervisor_stmt);

                // Fetch the Supervisor_Phone if the Supervisor_ID exists
                $supervisor_phone = null;
                if ($row = oci_fetch_assoc($check_supervisor_stmt)) {
                    $supervisor_phone = $row['SUPERVISOR_PHONE'];

                    // Update both Supervisor_ID and Supervisor_Phone
                    $update_sql = "UPDATE PROJECT SET Supervisor_ID = :new_value, Supervisor_Phone = :supervisor_phone WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";
                    $update_stmt = oci_parse($db_conn, $update_sql);

                    // Bind parameters
                    oci_bind_by_name($update_stmt, ":new_value", $new_value);
                    oci_bind_by_name($update_stmt, ":supervisor_phone", $supervisor_phone);
                    oci_bind_by_name($update_stmt, ":project_id", $project_id);
                    oci_bind_by_name($update_stmt, ":owner_id", $owner_id);

                    // Execute the update query
                    if (isset($update_stmt) && oci_execute($update_stmt)) {
                        echo "<p style='color:green; text-align:center;'>Project updated successfully!</p>";
                    } else {
                        $e = oci_error($update_stmt);
                        echo "<p style='color:red; text-align:center;'>Error updating project: " . htmlentities($e['message']) . "</p>";
                    }
                } else {
                    echo "<p style='color:red; text-align:center;'>Error: Supervisor ID Not In Our Database.</p>";
                }
            } elseif ($attribute === "Budget_ID") {
                // Check if the new Budget_ID exists in the BUDGET table
                $check_project_sql = "SELECT COUNT(*) FROM PROJECT WHERE BUDGET_ID = :new_budget_id"; // Changed FROM BUDGET to FROM PROJECT
                $check_project_stmt = oci_parse($db_conn, $check_project_sql);
                oci_bind_by_name($check_project_stmt, ":new_budget_id", $new_value);
                oci_execute($check_project_stmt);
    
                $budget_row = oci_fetch_assoc($check_project_stmt);
                $project_budget_exists = $budget_row['COUNT(*)'];


                $check_budget_sql = "SELECT COUNT(*) FROM BUDGET WHERE BUDGET_ID = :new_budget_id"; // Changed FROM BUDGET to FROM PROJECT
                $check_budget_stmt = oci_parse($db_conn, $check_budget_sql);
                oci_bind_by_name($check_budget_stmt, ":new_budget_id", $new_value);
                oci_execute($check_budget_stmt);
    
                $budget_row = oci_fetch_assoc($check_budget_stmt);
                $budget_exists = $budget_row['COUNT(*)'];


    
                if ($project_budget_exists > 0 && $budget_exists > 0) {
                    echo "<p style='color:red; text-align:center;'>Error: Budget ID is associated with another project.</p>";
                } else if ($budget_exists == 0 && $project_budget_exists == 0) {
                    echo "<p style='color:red; text-align:center;'>Error: Budget ID does not exist in our database</p>";
                } else {
                    // Retrieve the old Budget_ID for the project
                    $old_budget_sql = "SELECT BUDGET_ID FROM PROJECT WHERE PROJECT_ID = :project_id";
                    $old_budget_stmt = oci_parse($db_conn, $old_budget_sql);
                    oci_bind_by_name($old_budget_stmt, ":project_id", $project_id);
                    oci_execute($old_budget_stmt);
    
                    if ($old_budget_row = oci_fetch_assoc($old_budget_stmt)) {
                        $old_budget_id = $old_budget_row['BUDGET_ID'];
    
                        // Update the PROJECT table with the new Budget_ID
                        $update_project_sql = "UPDATE PROJECT SET BUDGET_ID = :new_budget_id WHERE PROJECT_ID = :project_id";
                        $update_project_stmt = oci_parse($db_conn, $update_project_sql);
                        oci_bind_by_name($update_project_stmt, ":new_budget_id", $new_value);
                        oci_bind_by_name($update_project_stmt, ":project_id", $project_id);
                        
    
                        if (oci_execute($update_project_stmt)) {
                            echo "<p style='color:green; text-align:center;'>Budget ID updated successfully in both PROJECT!</p>";
                            // // Update the BUDGET table to use the new Budget_ID
                            // $update_budget_sql = "UPDATE BUDGET SET BUDGET_ID = :new_budget_id WHERE BUDGET_ID = :old_budget_id";
                            // $update_budget_stmt = oci_parse($db_conn, $update_budget_sql);
                            // oci_bind_by_name($update_budget_stmt, ":new_budget_id", $new_value);
                            // oci_bind_by_name($update_budget_stmt, ":old_budget_id", $old_budget_id);
    
                            // if (oci_execute($update_budget_stmt)) {
                            //     echo "<p style='color:green; text-align:center;'>Budget ID updated successfully in both PROJECT and BUDGET tables!</p>";
                            // } else {
                            //     $e = oci_error($update_budget_stmt);
                            //     echo "<p style='color:red; text-align:center;'>Error updating Budget table: " . htmlentities($e['message']) . "</p>";
                            // }
                        } else {
                            $e = oci_error($update_project_stmt);
                            echo "<p style='color:red; text-align:center;'>Error updating Project table: " . htmlentities($e['message']) . "</p>";
                        }
                    } else {
                        echo "<p style='color:red; text-align:center;'>Error: Could not retrieve old Budget ID for the project.</p>";
                    }
                }
                
            }    else {
                // Change Attribute to Date Format if updating Start or End Dates
                if ($attribute === "Project_Start_Date" || $attribute === "Project_End_Date") {
                    // TODO
                    // TO_DATE(:new_value, 'YYYY-MM-DD')
                    $update_sql = "UPDATE PROJECT SET $attribute = TO_DATE(:new_value, 'YYYY-MM-DD') WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";
                } else {
                    // General update for other attributes
                    $update_sql = "UPDATE PROJECT SET $attribute = :new_value WHERE PROJECT_ID = :project_id AND OWNER_ID = :owner_id";
                }

                
                $update_stmt = oci_parse($db_conn, $update_sql);

                // Bind parameters
                oci_bind_by_name($update_stmt, ":new_value", $new_value);
                oci_bind_by_name($update_stmt, ":project_id", $project_id);
                oci_bind_by_name($update_stmt, ":owner_id", $owner_id);

                // Execute the update query
                if (isset($update_stmt) && oci_execute($update_stmt)) {
                    echo "<p style='color:green; text-align:center;'>Project updated successfully!</p>";
                } else {
                    $e = oci_error($update_stmt);
                    echo "<p style='color:red; text-align:center;'>Error updating project: " . htmlentities($e['message']) . "</p>";
                }
            }
        
        } else if ($project_exists == 0) {
            echo "<p style='color:red; text-align:center;'>Error: Project ID entered does not exist.</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: You don't have acces to Project: $project_id</p>";
        }
    }
    ?>














    <!-- <div class="button-container" style="left: 150px;">
        <button onclick="toggleForm('update_project_form')"> Update Project </button>
    </div>

    <div id="update_project_form" class="hidden form-container">
    <h3>Update Project Details</h3>
    <form method="POST" action="">
        <label for="project_id">Enter Project ID to Update A Project:</label>
        <input type="text" id="project_id" name="project_id" required>
        <br><br>

        <label for="attribute">Choose A Column to Update:</label>
        <select id="attribute" name="attribute" required>
            <option value="" disabled selected>Select a Column</option>
            <option value="Project_Name">Project Name</option>
            <option value="Project_Address">Project Address</option>
            <option value="Project_Status">Project Status</option>
            <option value="Project_Start_Date">Project Start Date</option>
            <option value="Project_End_Date">Project End Date</option>
            <option value="Owner_ID">Owner ID</option>
            <option value="Owner_Phone">Owner Phone</option>
            <option value="Supervisor_ID">Supervisor ID</option>
            <option value="Budget_ID">Budget ID</option>
        </select>
        <br><br>

        <label for="new_value">Enter New Value:</label>
        <input type="text" id="new_value" name="new_value" required>
        <br><br>

        <button type="submit" name="updateProject">Update Project</button>
    </form>
</div> -->


<div class="button-container" style="left: 150px;">
    <button onclick="toggleForm('update_project_form')"> Update Project </button>
</div>

<div id="update_project_form" class="hidden form-container">
    <h3>Update Project Details</h3>
    <form method="POST" action="">
        <label for="project_id">Enter Project ID to Update A Project:</label>
        <input type="text" id="project_id" name="project_id" required>
        <br><br>

        <label for="attribute">Choose A Column to Update:</label>
        <select id="attribute" name="attribute" required onchange="updateInputField()">
            <option value="" disabled selected>Select a Column</option>
            <option value="Project_Name">Project Name</option>
            <option value="Project_Address">Project Address</option>
            <option value="Project_Status">Project Status</option>
            <option value="Project_Start_Date">Project Start Date</option>
            <option value="Project_End_Date">Project End Date</option>
            <option value="Supervisor_ID">Supervisor ID</option>
            <option value="Budget_ID">Budget ID</option>
        </select>
        <br><br>

        <label for="new_value">Enter New Value:</label>
        <input type="text" id="new_value" name="new_value" required>
        <br><br>

        <button type="submit" name="updateProject">Update Project</button>
    </form>
</div>

<script>
    function updateInputField() {
        const attribute = document.getElementById('attribute').value;
        const newValueField = document.getElementById('new_value');

        if (attribute === 'Project_Start_Date' || attribute === 'Project_End_Date') {
            // Change to date input
            newValueField.type = 'date';
        } else {
            // Revert back to text input
            newValueField.type = 'text';
        }
    }
</script>




<?php
// $selectedAttribute = '';
// $newValueInputType = 'text'; // Default input type
// $showForm = false; // Flag to control form visibility

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    

//     if (isset($_POST['attribute'])) {
//         // event.preventDefault();
//         $showForm = true; // Make the form visible after submission
//         $selectedAttribute = $_POST['attribute'];

//         // Determine the input type based on the selected attribute
//         if ($selectedAttribute === 'Project_Start_Date' || $selectedAttribute === 'Project_End_Date') {
//             $newValueInputType = 'date';
//         } else {
//             $newValueInputType = 'text';
//         }
//     }
// }
?>


<!-- <div class="button-container" style="left: 150px;">
        <button onclick="toggleForm('update_project_form')"> Update Project </button>
</div>

<div id="update_project_form" class="<?= $showForm ? '' : 'hidden' ?> form-container">
    <h3>Update Project Details</h3>
    <form method="POST" action="">
        <label for="attribute">Choose A Column to Update:</label>
        <select id="attribute" name="attribute" required onchange="this.form.submit()">
            <option value="" disabled <?= $selectedAttribute === '' ? 'selected' : '' ?>>Select a Column</option>
            <option value="Project_Name" <?= $selectedAttribute === 'Project_Name' ? 'selected' : '' ?>>Project Name</option>
            <option value="Project_Address" <?= $selectedAttribute === 'Project_Address' ? 'selected' : '' ?>>Project Address</option>
            <option value="Project_Status" <?= $selectedAttribute === 'Project_Status' ? 'selected' : '' ?>>Project Status</option>
            <option value="Project_Start_Date" <?= $selectedAttribute === 'Project_Start_Date' ? 'selected' : '' ?>>Project Start Date</option>
            <option value="Project_End_Date" <?= $selectedAttribute === 'Project_End_Date' ? 'selected' : '' ?>>Project End Date</option>
            <option value="Supervisor_ID" <?= $selectedAttribute === 'Supervisor_ID' ? 'selected' : '' ?>>Supervisor ID</option>
            <option value="Budget_ID" <?= $selectedAttribute === 'Budget_ID' ? 'selected' : '' ?>>Budget ID</option>
        </select>
        <br><br>

        <label for="project_id">Enter Project ID to Update:</label>
        <input type="text" id="project_id" name="project_id" required>
        <br><br>

        <label for="new_value">Enter New Value:</label>
        <input type="<?= $newValueInputType ?>" id="new_value" name="new_value" required>
        <br><br>

        <button type="submit" name="updateProject">Update Project</button>
    </form>
</div> -->





    <!-- JOIN FUNCTIONALITY -->



    <?php
    if (isset($_POST['viewProjectSubmit'])) {
        // Get the input values
        $project_id = $_POST['project_id'];




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

                // Fetch the joined data
                $project_found = false;
                while ($row = oci_fetch_assoc($select_stmt)) {
                    $project_found = true;
                    echo "<p style='color:blue; text-align:center;'>Project Details:</p>";
                    echo "<ul style='text-align:center;'>";
                    echo "<li><strong>Project ID:</strong> " . htmlentities($row['PROJECT_ID']) . "</li>";
                    echo "<li><strong>Project Name:</strong> " . htmlentities($row['PROJECT_NAME']) . "</li>";
                    echo "<li><strong>Project Address:</strong> " . htmlentities($row['PROJECT_ADDRESS']) . "</li>";
                    echo "<li><strong>Project Status:</strong> " . htmlentities($row['PROJECT_STATUS']) . "</li>";
                    echo "<li><strong>Budget ID:</strong> " . htmlentities($row['BUDGET_ID']) . "</li>";
                    echo "<li><strong>Material Cost:</strong> " . htmlentities($row['BUDGET_MATERIAL_COST']) . "</li>";
                    echo "<li><strong>Initial Estimate:</strong> " . htmlentities($row['BUDGET_INITIAL_ESTIMATE']) . "</li>";
                    echo "<li><strong>Contractor Fees:</strong> " . htmlentities($row['BUDGET_CONTRACTOR_FEES']) . "</li>";
                    echo "<li><strong>Total Cost:</strong> " . htmlentities($row['BUDGET_TOTAL_COST']) . "</li>";
                    echo "<li><strong>Wage Worker Cost:</strong> " . htmlentities($row['BUDGET_WAGE_WORKER_COST']) . "</li>";
                    echo "</ul>";
                }

                // Output error message if no project found
                if (!$project_found) {
                    echo "<p style='color:red; text-align:center;'>No matching project found for the given Project ID or you do not have access to this project.</p>";
                }
        } else if ($project_exists > 0 && $owner_project_exists == 0) {
            echo "<p style='color:red; text-align:center;'>You do not have access to this project</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>No matching project found for the given Project ID</p>";
        }
    }
    ?>



    <!-- <div class="button-container" style="right: 300px;">
        <button onclick="toggleForm('join_project_form')"> Budget Details</button>
    </div>

    <div id="join_project_form" class="hidden form-container">
        <h3>Check Budget Details for a Project</h3>
        <form method="POST" action="">
            <label for="Project_ID">Project ID:</label>
            <input type="text" id="project_id" name="project_id" required><br><br>

            <button type="submit" name="joinProjectSubmit">Get Details</button>
        </form>
    </div> -->

    <div class="button-container" style="right: 300px;">
    <button onclick="toggleForm('join_project_form')"> View Project and Budget Details </button>
</div>

<div id="join_project_form" class="hidden form-container">
    <h3>View Project and Budget Details</h3>
    <form method="POST" action="">
        <label for="project_id">Project ID:</label>
        <input type="text" id="project_id" name="project_id" required><br><br>

        <button type="submit" name="viewProjectSubmit">View Details</button>
    </form>
</div>





<div id="view_budget_button">
        <form method="GET" action="budgetpage.php">
            <input type="hidden" name="owner_id" value="<?php echo isset($_GET['owner_id']) ? htmlspecialchars($_GET['owner_id']) : ''; ?>">
            <button type="submit" >View Budget</button>
        </form>
</div>



    <script>
        function toggleForm(formid) {
            var form = document.getElementById(formid);
            if (form.classList.contains("hidden")) {
                form.classList.remove("hidden");
            } else {
                form.classList.add("hidden");
            }
        }
    </script>
      
</body>
</html>



    
    




