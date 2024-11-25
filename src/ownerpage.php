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
            margin: 10px;
            text-align: center;
            position: absolute;
            
        }

        #add_review_form {
            margin-top: 20px; 
            position: absolute;
            left:10px;
            
        
        }

        #add_project_form {
            margin-top: 20px; 
            position: absolute;
            right:40px;
            
        }

        #delete_project_form {
            margin-top: 30px; 
            position: absolute;
            right:150px;
            
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

    // //to get supervisors for drop down in project
    // $supervisors = [];
    // if (connectToDB()) {
    //     $query = "SELECT Supervisor_ID, Supervisor_Name 
    //               FROM Supervisor";

    //     $statement = oci_parse($db_conn, $query);
    //     if (oci_execute($statement)) {
    //         while ($row = oci_fetch_assoc($statement)) {
    //             $supervisors[] = $row;
    //         }
    //     } else {
    //         $e = oci_error($statement);
    //         echo "<p style='color:red;'>Error fetching supervisors: " . htmlentities($e['message']) . "</p>";
    //     }
    //     disconnectFromDB();
    // }

    function getOwnerProject($owner_id) 
    {
        global $db_conn;
        // Changed $query to $sql
        $sql = "SELECT Project_ID, Project_Name, Project_Address, Project_Start_Date, Project_End_Date, Project_Status, Supervisor_ID, Supervisor_Phone, Budget_ID
                FROM Project
                WHERE Owner_ID = :owner_id";
        
        $statement = oci_parse($db_conn, $sql); // Changed $query to $sql
        oci_bind_by_name($statement, ":owner_id", $owner_id);
        // echo "<p style='color:blue; text-align:center;'>Owner ID In getOwnerProject: " . htmlentities($owner_id) . "</p>"; // Should comment it out
        
        if (!oci_execute($statement)) {
            $e = oci_error($statement);
            echo "<p style='color:red;'>Error fetching projects: " . htmlentities($e['message']) . "</p>";
            return [];
        }

        $projects = [];
        while ($row = oci_fetch_assoc($statement)) {
        $projects[] = $row;
        }

        // foreach ($projects as $project) {
        //     echo "<p style='color:green; text-align:center;'>Project ID: " . htmlentities($project['Project_ID']) . ", Project Name: " . htmlentities($project['Project_Name']) . "</p>";
        // }

        // echo "<p style='color:blue; text-align:center;'>Number of projects: " . count($projects) . "</p>";

        


        return $projects;

    }

    // $owner_id = '0001';


    if (connectToDB()) {

        if (isset($_GET['owner_id'])) {
        $owner_id = $_GET['owner_id'];
        // echo "<p style='color:blue; text-align:center;'>Owner ID: " . htmlentities($owner_id) . "</p>"; // Debug statement to verify the owner_id
        } 
        // else {
        //     echo "<p style='color:red;'>Owner ID not set!</p>"; // should comment this and the above echo statements
        // }

        $projects = getOwnerProject($owner_id);
        // <p>Total Projects: echo !empty($projects) ? count($projects) : 0;

        disconnectFromDB();
    } else {
        $projects = [];
    }


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









    <div style="text-align: center;">
    <h1>Owner Dashboard</h1>
    <h2>Your Projects</h2>
    <!-- <p>Total Projects: <?php echo !empty($projects) ? count($projects) : 0; ?></p> -->
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

    <div class = "button-container" style="right: 30px;">
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

            <label for="Status">Status:</label>
            <input type="text" id="status" name="status" required><br><br>

            <label for="Supervisor_ID">Supervisor ID:</label>
            <select id="supervisor_id" name="supervisor_id" required>
                <option value="" disabled selected>Select a Supervisor</option>
                <?php foreach ($supervisors as $supervisor): ?>
                    <option value="<?php echo htmlentities($supervisor['SUPERVISOR_ID']); ?>">
                        <?php echo htmlentities($supervisor['SUPERVISOR_ID'] . " - " . $supervisor['SUPERVISOR_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

        
            <!-- <label for="Supervisor_Phone">Supervisor Phone:</label>
            <input type="text" id="supervisor_phone" name="supervisor_phone" required><br><br> -->

            <label for="Budget_ID">Budget ID:</label>
            <input type="text" id="budget_id" name="budget_id" required><br><br>

            <button type="submit" name="addProjectSubmit">Submit Project</button>

        </form>
    </div>

    <div class = "button-container" style="right: 150px;">
            <button onclick="toggleForm('delete_project_form')"> Delete Project </button>
        </div>

    <div id="delete_project_form" class="hidden">
        <h3>Delete Project</h3>
        <form method="POST" action="">
            <label for="Project_ID">Project ID:</label>
            <input type="text" id="project_id" name="project_id" required><br><br>

            <button type="submit" name="deleteProjectSubmit">Delete Project</button>

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