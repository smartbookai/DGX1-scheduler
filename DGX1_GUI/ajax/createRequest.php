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
    if (!isset($_POST['container-selector'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['date'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['time'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['numHours'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['numGPUs'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['comments'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    if (!isset($_POST['resource_ids'])) {
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }
    $user_id = $_SESSION['user_ID'];
    $container_id = json_decode($_POST['container-selector'])->{'id'};

    $date = date("Y-m-d", strtotime($_POST['date']));
    $time = date("H:i:s", strtotime($_POST['time']));
    $requested_from = $date . " " . $time;

    $request_duration = $_POST['numHours'];
    $num_resources_requested = $_POST['numGPUs'];
    $comments = $_POST['comments'];
    $resource_ids = $_POST['resource_ids'];

    $user_info = getUserInfoFor($user_id);
    $container_info = getContainerInfoFor($container_id);

    $conn->autocommit(FALSE);

    $sql = "INSERT INTO `tasks` (
            `user_ID`,
            `container_ID`,
            `status_ID`,
            `approved_by`,
            `approved_at`,
            `requested_at`,
            `requested_from`,
            `request_duration`,
            `num_resources_requested`,
            `num_resources_approved`,
            `canceled_at`,
            `approved_from`,
            `approved_duration`,
            `command_run`,
            `comments`) 

        VALUES (
            ?,
            ?,
            1,
            NULL,
            NULL,
            current_timestamp(),
            ?,
            ?,
            ?,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            ?
        )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "iisiis",
            $user_id,
            $container_id,
            $requested_from,
            $request_duration,
            $num_resources_requested,
            $comments
    );
    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo ("Invalid request");
        exit();
    }

    $new_task_id = $stmt->insert_id;

    // allocate task resources

    $sql2 = "
        INSERT INTO `task_resources` (`task_ID`, `resource_ID`)
        VALUES (?, ?)
    ";

    $stmt2 = $conn->prepare($sql2);
    foreach ($resource_ids as $resource_id) {
        $stmt2->bind_param(
                "ii",
                $new_task_id,
                $resource_id
        );
        if (!$stmt2->execute()) {
            $conn->rollback();
            http_response_code(400);
            echo ("Invalid request");
            exit();
        }
    }


    //send email
    $message_type = 5;
    $to = $user_info["email"];
    $subject = "DGX-1 Request Received";
    $message = file_get_contents(__BASE_PATH__ . '/email_templates/request_received.html');
    $message = str_replace('%name%', $user_info["name"], $message);
    $message = str_replace('%task_id%', $new_task_id, $message);
    $message = str_replace('%date%', $requested_from, $message);
    $message = str_replace('%duration%', $request_duration, $message);
    $message = str_replace('%numGPUs%', $num_resources_requested, $message);
    $message = str_replace('%container%', $container_info["name"], $message);
    $message = str_replace('%comments%', $comments, $message);


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
            $new_task_id,
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
    echo ("Invalid request");
    exit();
}

function getUserInfoFor($user_id) {
    global $conn;
    $sql = "
        SELECT 
            u.`name` AS `name`,
            u.`email` AS `email`
        FROM `users` AS u
        WHERE 
            u.`user_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "i",
            $user_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($result) > 0)
        $result = $result[0];

    $stmt->close();
    return $result;
}

function getContainerInfoFor($container_id) {
    global $conn;
    $sql = "
        SELECT 
            c.`name` AS `name`
        FROM `containers` AS c
        WHERE 
            c.`container_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
            "i",
            $container_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($result) > 0)
        $result = $result[0];

    $stmt->close();
    return $result;
}
