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
include(__BASE_PATH__ . '/helpers/email.php');
include(__BASE_PATH__ . '/helpers/getInfoFunctions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //read and validate data
    if (!isset($_POST['user_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['approved_date'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['approved_duration'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['task_id'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    if (!isset($_POST['approved_num_resources'])) {
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $user_id = $_POST['user_id'];
    $date = $_POST['approved_date'];
    $duration = $_POST['approved_duration'];
    $task_id = $_POST['task_id'];
    $numGPUs = $_POST['approved_num_resources'];

    $task_info = getTaskInfoFor($task_id);
    $resources = getTaskResourcesFor($task_id);
    $user_info = getUserInfoFor($task_id);

    // calculate offset for ports
    // it will be based on the # of first available GPU for allocation
    $min_gpu_number = 8;
    $gpus = [];
    foreach ($resources as $record) {
        if ($record["type"] === "GPU") {
            $gpu = $record["value"];
            if ($gpu < $min_gpu_number)
                $min_gpu_number = $gpu;
            $gpus[] = $gpu;
        }
    }

    $jupyter_port = 8000 + $min_gpu_number;
    $tensor_board_port = 6000 + $min_gpu_number;
    $ssh_port = 7000 + $min_gpu_number;
    $task_name = "Task_" . $task_id;
    $jupyter_token = substr(hash("sha256", $task_name . $jupyter_port), 0, 48);
    $docker_working_dir = "/projects";
    //build command for execution
    // TODO: externalize runner account as parameter
    $command = "docker run " .
            "--gpus '\\\"device=" . implode(",", $gpus) . "\\\"' " .
            "-it --rm -d " .
            "--name $task_name " .
            "-p $tensor_board_port:6006 " .
            "-p $jupyter_port:8888 " .
            "-p $ssh_port:22 " .
            "--env JUPYTER_TOKEN=$jupyter_token " .
            "--mount type=bind,source=/home/runner/keys/" . $task_info["server_account"] . ".pub,target=/root/.ssh/authorized_keys " .
            "--mount type=bind,source=/home/runner/shared/" . $task_info["server_account"] . "/$task_name,target=$docker_working_dir " .
            "-w $docker_working_dir " .
            $task_info["container"];


    $conn->autocommit(FALSE);

    //start sql query
    $sql = "
        UPDATE `tasks` 
        SET 
            `status_ID` = '2', 
            `canceled_at` = NULL, 
            `approved_at` = CURRENT_TIMESTAMP(), 
            `approved_by` = ?, 
            `approved_from` = ?, 
            `approved_duration` = ?, 
            `num_resources_approved` = ?,
            `command_run` = ?
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
            "isiisi",
            $user_id,
            $date,
            $duration,
            $numGPUs,
            $command,
            $task_id
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }

    $stmt->close();

    //send email
    $message_type = 2;
    $to = $user_info["email"];
    $subject = "DGX-1 Request Approved";
    $message = file_get_contents(__BASE_PATH__ . '/email_templates/request_approved.html');
    $message = str_replace('%name%', $user_info["name"], $message);
    $message = str_replace('%task_id%', $task_id, $message);
    $message = str_replace('%date%', $date, $message);
    $message = str_replace('%duration%', $duration, $message);
    $message = str_replace('%numGPUs%', $numGPUs, $message);
    $message = str_replace('%container%', $task_info["container"], $message);
    $message = str_replace('%docker_working_dir%', $docker_working_dir, $message);

    $message = str_replace('%server_account%', $task_info["server_account"], $message);
    
    $message = str_replace('%jupyter_port%', $jupyter_port, $message);
    $message = str_replace('%jupyter_token%', $jupyter_token, $message);
    $message = str_replace('%ssh_port%', $ssh_port , $message);
    $message = str_replace('%tensorboard_port%', $tensor_board_port , $message);





    $status = sendEmail($to, $subject, $message, __BASE_PATH__.'/email_templates/putty_instruction.pdf');
    $status = ($status == 1) ? "SENT" : "FAILED";
    //save status of senc into db
    $sql4 = "INSERT INTO `emails`(
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

    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param(
            "iiss",
            $task_id,
            $message_type,
            $message,
            $status
    );

    if (!$stmt4->execute()) {
        $conn->rollback();
        http_response_code(400);
        echo("Invalid request");
        exit();
    }
    $stmt4->close();

    //commit changes
    $conn->commit();
} else {
    http_response_code(400);
    echo ("Invalid request");
}
