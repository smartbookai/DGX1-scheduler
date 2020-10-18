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

    //read and validate data
    if (!isset($_POST['user_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $user_id = $_POST['user_id'];

    $conn->autocommit(FALSE);

    $sql = "
        DELETE FROM `users`
        WHERE
            `user_ID` = ?
            ";

    // Check DB connection
    if ($conn->connect_error) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "i",
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
    echo ("Invalid request");
}

