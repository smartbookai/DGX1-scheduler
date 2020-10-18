<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_ID'])) {
    http_response_code(401);
    echo ("Unauthorized access is forbidden");
}

include(__BASE_PATH__ . '/database_connection.php');
include(__BASE_PATH__ . '/helpers/email.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //read and validate data
    if (!isset($_POST['server_account'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    $server_account = $_POST['server_account'];

    //validate server account
    // it can be only lower case latin symbols numbers and underscores
    // and '-' symbol also length should be < 31
    $pregmatch_result = preg_match("/^[a-z_][a-z0-9_-]*$/", $server_account);
    if ( $pregmatch_result == 0 || strlen($server_account) > 31) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    $sql = "INSERT INTO `server_accounts` (
            `name`
            )

        VALUES (
            ?
        )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "s",
            $server_account
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }
}