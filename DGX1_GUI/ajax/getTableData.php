<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_ID'])) {
    http_response_code(401);
    echo ("Unauthorized access is forbidden");
}

include(__BASE_PATH__ . '/database_connection.php');
    
$sql = "SELECT
            t.task_ID 'Task_ID',
            u.name 'Name',
            u1.name 'Approver',
            a.name 'Affiliation',
            t.requested_from 'Requested_From',
            t.approved_from 'Approved_From',
            t.num_resources_requested 'Num_Resources_Requested',
            t.num_resources_approved 'Num_Resources_Approved',
            t.request_duration 'Request_Duration',
            t.approved_duration 'Approved_Duration',
            c.name 'Container',
            s.name 'Status',
            sa.name 'Account',
            r1.res AS 'Resources'
        FROM    `tasks`             AS `t`
        JOIN    `users`             AS `u`  ON (t.`user_ID` = u.`user_ID`)
        LEFT JOIN `users`           AS `u1` ON (t.`approved_by` = u1.`user_ID`)
        JOIN    `containers`        AS `c`  ON (t.`container_ID` = c.`container_ID`)
        JOIN    `status`            AS `s`  ON (t.`status_ID` = s.`status_ID`)
        JOIN    `affiliation`       AS `a`  ON (u.`affiliation_ID` = a.`affiliation_ID`)
        LEFT JOIN    (
            SELECT
                tr.`task_ID`,
                GROUP_CONCAT(r.`value`) AS `res`
            FROM `task_resources` AS `tr`
            JOIN `resources` AS r ON (r.`resource_ID` = tr.`resource_ID`)
            GROUP BY tr.`task_ID`
        ) AS `r1`  ON (r1.`task_ID` = t.`task_ID`)
        LEFT JOIN `server_accounts` AS `sa` ON (u.`server_account_ID` = sa.`account_ID`)" .
        ((isset($_SESSION['isAdmin']) && isset($_GET["unfiltered"]))?"" : "WHERE u.`user_ID` = ? ").
        "ORDER BY t.`requested_at`";

$stmt = $conn->prepare($sql);
if (!(isset($_SESSION['isAdmin']) && isset($_GET["unfiltered"]))) {
    $stmt->bind_param("i", $_SESSION["user_ID"]);
}
$stmt->execute();   
$tasks = array();
$result = $stmt->get_result();
if ($result->num_rows != 0) {
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$rows_out = $tasks;
$column_out = array();

$merged_tasks = array();
if ($tasks) {
    foreach ($tasks as &$row) {
        $merged_row = array();
        $merged_row["Task_ID"] = $row["Task_ID"];
        $merged_row["Name"] = $row["Name"];
        $merged_row["Status"] = $row["Status"];
        $merged_row["Affiliation"] = $row["Affiliation"];
        $merged_row["Account"] = $row["Account"];
        $merged_row["Container"] = $row["Container"];
        $merged_row["Approver"] = $row["Approver"];

   
        // echo "<br>";

        //Set From 
        if ($row["Approved_From"]) {
            $from = $row["Approved_From"];
        } else {
            $from = $row["Requested_From"];
        }
        $merged_row["Start"] = $from;

        //Set Duration
        if ($row["Approved_Duration"]) {
            $duration = $row["Approved_Duration"];
        } else {
            $duration = $row["Request_Duration"];
        }
        $merged_row["Duration"] = $duration;

        //Set Duration
        if ($row["Num_Resources_Approved"]) {
            $resources = $row["Num_Resources_Approved"];
        } else {
            $resources = $row["Num_Resources_Requested"];
        }

        $merged_row["Resources"] = $resources;
        $merged_row["ResourceIDs"] = array_map(function($val) {return (int)$val;}, explode(",", $row["Resources"]));

        //Create the new row
        //Add the new row to the main list of rows
        array_push($merged_tasks, $merged_row);
    }
    $rows_out = $merged_tasks;
}

$columns_headers = [
        'Task_ID',
        'Name',
        'Affiliation',
        'Start',
        'Resources',
        'Duration',
        'Container',
        'Status',
        'Approver',
        'Account',
        'ResourceIDs',
    ];

foreach ($columns_headers as &$title) {
    if ($title == "Task_ID") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "sm md lg", "style" => "text-align: center; vertical-align: middle", "sorted" => true, "direction" => "DESC"));
    } elseif ($title == "Container") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "xs sm md", "style" => "text-align: center; vertical-align: middle"));
    } elseif ($title == "Affiliation") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "xs sm", "style" => "text-align: center; vertical-align: middle"));
    } elseif ($title == "Status") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "xs sm", "style" => "text-align: center; vertical-align: middle", "visible" => false));
    } elseif ($title == "Account") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "visible" => false));
    } elseif ($title == "ResourceIDs") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "visible" => false));
    } else {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "xs", "style" => "text-align: center; vertical-align: middle"));
    }
}

$output = json_encode(array("columns" => json_encode($column_out), "rows" => json_encode($rows_out)));

header("Content-type: application/json");
header("X-Content-Type-Options: nosniff");
print_r($output);
?>
