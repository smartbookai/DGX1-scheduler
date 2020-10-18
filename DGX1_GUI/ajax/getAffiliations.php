<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isAdmin'])) {
    http_response_code(401);
    echo ("Unauthorized access is forbidden");
}

include(__BASE_PATH__ . '/database_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $sql = "
        SELECT
            `a`.affiliation_ID AS `id`,
            `a`.`name` AS `name`
        FROM `affiliation` AS a
    ";

    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $affiliations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    header("Content-type: application/json");
    header("X-Content-Type-Options: nosniff");
    echo (json_encode($affiliations, TRUE));
}
