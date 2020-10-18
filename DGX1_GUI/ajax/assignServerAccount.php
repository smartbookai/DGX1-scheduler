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
    if (!isset($_POST['task_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    //read and validate data
    if (!isset($_POST['server_account_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $task_id    = $_POST['task_id'];
    $server_account_id = $_POST['server_account_id'];  

    //start sql query       
    $sql = "
        UPDATE
            users u
        INNER JOIN tasks t ON u.user_ID = t.user_ID
        SET
            u.server_account_ID = ?
        WHERE
            t.task_ID = ?
        ";


    // Check DB connection
    if ($conn->connect_error) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ii",
        $server_account_id,
        $task_id
    );
    if (!$stmt->execute()) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }
    
} else {
    http_response_code(400);
    echo("Invalid request");
    exit();
}
