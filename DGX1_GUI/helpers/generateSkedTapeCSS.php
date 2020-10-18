<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header("Content-type: text/css");
header("X-Content-Type-Options: nosniff");
if (!isset($_SESSION['user_ID'])) {
    http_response_code(401);    
    exit();
}

include(__BASE_PATH__ . '/database_connection.php');

$sql = "SELECT  t.`task_ID` AS `name`,
                t.`approved_from` AS `start_date`,
                t.`approved_duration` AS `duration`
        FROM `tasks` t
        ORDER BY t.`task_ID`";

$stmt = $conn->prepare($sql);
$stmt->execute();

$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$css = "";
foreach ($tasks as &$task) {
    srand($task["name"] + 1);
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    $css = $css . ".customEventSkedTapeEvent{$task["name"]}{background-color:rgba($r, $g, $b, 0.73);z-index: 1}";
}

echo($css);
?>
