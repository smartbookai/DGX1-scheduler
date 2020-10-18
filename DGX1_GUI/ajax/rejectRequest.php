<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['isAdmin'])) {
    http_response_code(401);
    echo("Unauthorized access is forbidden");
}

include(__BASE_PATH__ . '/database_connection.php');
include(__BASE_PATH__ . '/helpers/email.php');
include(__BASE_PATH__ . '/helpers/getInfoFunctions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //read and validate data
    if (!isset($_POST['user_id'])) {
        http_response_code(400);
        echo("Invalid request");
    }

    if (!isset($_POST['task_id'])) {
        http_response_code(400);
        echo("Invalid request");
    }

    $user_id = $_POST['user_id'];
    $task_id = $_POST['task_id'];
    $user_info = getUserInfoFor($task_id);

    $conn->autocommit(FALSE);

    $sql = "
        UPDATE `tasks` 
        SET 
            `status_ID` = '3', 
            `approved_by` = ?, 
            `approved_at` = CURRENT_TIMESTAMP(), 
            `canceled_at` = NULL, 
            `approved_from` = NULL 
        WHERE 
            `tasks`.`task_ID` = ?
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
            "ii",
            $user_id,
            $task_id
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $stmt->close();

     // free task resources
    $sql2 = "
        DELETE FROM `task_resources`
        WHERE `task_ID` = ?
    ";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $task_id);
    if (!$stmt2->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }
    $stmt2->close();

    //send email
    $message_type = 4;
    $to = $user_info["email"];
    $subject = "DGX-1 Request Rejected";
    $message = file_get_contents(__BASE_PATH__ . '/email_templates/request_rejected.html');
    $message = str_replace('%name%', $user_info["name"], $message);
    $message = str_replace('%task_id%', $task_id, $message);


    $status = sendEmail($to, $subject, $message);
    $status = ($status == 1) ? "SENT" : "FAILED";
    //save status of senc into db
    $sql3 = "INSERT INTO `emails`(
                `task_ID`,
                `type`,
                `content`,
                `status`,
                `time_sent`
            )
            VALUES(
                ?,
                ?,
                ?,
                ?,
                CURRENT_TIMESTAMP()
            )";

    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param(
            "iiss",
            $task_id,
            $message_type,
            $message,
            $status
    );

    if (!$stmt3->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }
    $stmt3->close();

    //commit changes
    $conn->commit();
} else {
    http_response_code(400);
    echo("Invalid request");
    exit();
}
