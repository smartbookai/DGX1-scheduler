<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_ID'])) {
    http_response_code(401);
    echo ("Unauthorized access is forbidden");
}

include(__BASE_PATH__ . '/database_connection.php');

//read and validate data
if (!isset($_GET['task_id'])) {
    http_response_code(400);
    echo ("Invalid request");
}

$task_id = $_GET['task_id'];

$sql = "SELECT  
            t.`task_ID` 'name',
            t.`approved_from` 'start_date',
            t.`approved_duration` 'duration',
            t.`requested_from` 'requested_start_date',
            t.`request_duration` 'requested_duration',
            r.`value` 'gpu'
        FROM    `tasks`             AS t
        JOIN    `task_resources`    AS tr   ON (t.task_ID=tr.task_ID)
        JOIN    `resources`         AS r    ON (tr.resource_ID=r.resource_ID)
        WHERE t.status_ID IN (1, 2 ,4) AND t.task_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$tmp = array();
foreach ($tasks as &$task) {
    if (!empty($task["start_date"])) {
        $start = $task["start_date"];
    } else {
        $start = $task["requested_start_date"];
    }

    if (!empty($task["duration"])) {
        $duration = $task["duration"];
    } else {
        $duration = $task["requested_duration"];
    }

    array_push($tmp, array(
        "gpu" => $task["gpu"],
        "start" => date('M d Y H:00', strtotime($start)),
        "end" => date('M d Y H:00', strtotime($start . "+" . $duration . " hours")),
    ));
}

header("Content-type: application/json");
header("X-Content-Type-Options: nosniff");
echo (json_encode($tmp));
?>
