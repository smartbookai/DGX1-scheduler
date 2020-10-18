<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isAdmin'])) {
    http_response_code(401);
    echo("Unauthorized access is forbidden");
    exit();
}

include(__BASE_PATH__ . '/database_connection.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['user_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['Affiliation'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['Account'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['isAdmin'])) {
        $is_admin = 0;
    } else {
        $is_admin = 1;
    }

    //TODO: check if account and affiliation is valid
    $user_id = (int) $_POST['user_id'];
    $account = (int) $_POST['Account'];
    $affiliation = (int) $_POST['Affiliation'];


    $conn->autocommit(FALSE);

    $sql = "UPDATE `users`
            SET
                `is_admin` = ?,
                `affiliation_ID` = ?,
                `server_account_ID` = ?
            WHERE
                `user_ID` = ?";

    // Check DB connection
    if ($conn->connect_error) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "iiii",
            $is_admin,
            $affiliation,
            $account,
            $user_id
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $stmt->close();

    //commit changes
    $conn->commit();
} else {
    http_response_code(400);
    echo("Invalid request");
    exit();
}
