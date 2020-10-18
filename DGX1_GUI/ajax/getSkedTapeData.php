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
            `t`.`task_ID` AS `name`,
            `t`.`approved_from` AS `start_date`,
            `t`.approved_duration AS `duration`,
            `t`.`requested_from` AS `requested_start_date`,
            `t`.`request_duration` AS `requested_duration`,
            `r`.`value` AS `location`
        FROM `tasks`            AS  `t`
        JOIN `task_resources`   AS  `tr`    ON (`t`.`task_ID` = `tr`.`task_ID`)
        JOIN `resources`        AS  `r`     ON (`tr`.`resource_ID` = `r`.`resource_ID`)
        WHERE
            NOT `t`.`status_ID` IN (3, 5)
            AND DATE_ADD(
                    IFNULL(`t`.`approved_from`, `t`.`requested_from`),
                    INTERVAL (
                        IFNULL(`t`.`approved_duration`, `t`.`request_duration`) + 24
                    ) HOUR
                ) > NOW()
        ORDER BY `t`.`task_ID`, `tr`.`resource_ID`";

$stmt = $conn->prepare($sql);
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
        "name" => "Task " . $task["name"],
        "location" => $task["location"],
        "start" => date('M d Y H:00', strtotime($start)) . " GMT +04:00",
        "end" => date('M d Y H:00', strtotime($start . "+" . $duration . " hours")) . " GMT +04:00",
        "className" => "customEventSkedTapeEvent" . $task["name"]
    ));
}

header("Content-type: application/json");
header("X-Content-Type-Options: nosniff");
echo (json_encode($tmp));
?>
