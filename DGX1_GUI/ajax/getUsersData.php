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
            `u`.`user_ID` AS 'ID',
            `u`.`name` AS 'Name',
            `u`.`is_admin` AS 'isAdmin',
            `u`.`email` AS 'Email',
            `a`.`name` AS 'Affiliation',
            `sa`.`name` AS 'Account'
        FROM    `users`             AS `u`
        JOIN    `affiliation`       AS `a`  ON (u.`affiliation_ID` = a.`affiliation_ID`)
        LEFT JOIN `server_accounts` AS `sa` ON (u.`server_account_ID` = sa.`account_ID`) ";

$stmt = $conn->prepare($sql);
if (!(isset($_SESSION['isAdmin']) && isset($_GET["unfiltered"]))) {
    $stmt->bind_param("i", $_SESSION["user_ID"]);
}
$stmt->execute();
$users = array();
$result = $stmt->get_result();
if ($result->num_rows != 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

$rows_out = $users;
$column_out = array();

$columns_headers = [
    'ID',
    'Name',
    'Email',
    'Affiliation',
    'Account',
    'isAdmin'
];

foreach ($columns_headers as &$title) {
    if ($title == "Name") {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "sm md lg", "style" => "text-align: center; vertical-align: middle", "sorted" => true, "direction" => "DESC"));
    } else {
        array_push($column_out, array("name" => $title, "title" => str_replace("_", " ", $title), "breakpoints" => "xs", "style" => "text-align: center; vertical-align: middle"));
    }
}

$output = json_encode(array("columns" => json_encode($column_out), "rows" => json_encode($rows_out)));

header("Content-type: application/json");
header("X-Content-Type-Options: nosniff");
print_r($output);
?>
