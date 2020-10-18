#!/usr/bin/php
<?php

//detect how script was ran
//from shell or webserver
if(php_sapi_name() != "cli") {
    echo("can be runned only in CLI mode");
    die();
}

define("REMOTE_SSH_USER", "runner");
define("REMOTE_SSH_HOST", "dgx1-host");
define("DB_HOST", "localhost");
define("DB_NAME", "dgx1_db");
define("DB_USER","dgx1_db");
define("DB_PASSWORD", "53cr3tP@ssw0rd");



// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//Check connection
if(!$conn){
    echo "Connection error: " . mysqli_connect_error();
}

check_server_accounts();
create_approved_tasks_directories();
manage();





function manage() {
    // tasks that are marked with in progress or approved status in the DB now
    $activeTasks = collect_active_tasks();

    // task which is actually executed by docker now
    $actualTasks = collect_actual_tasks();

    // find what should be run
    $toRun = get_tasks_to_run(array_keys($activeTasks), $actualTasks);
    // and run
    foreach ($toRun as $taskID) {
        print("task to run: $taskID\n");
        //print("command to run: " . $activeTasks[$taskID]["command_run"] . "\n");
        // run task on remove host
        $command = "ssh " . REMOTE_SSH_USER . "@" . REMOTE_SSH_HOST . " -t \"" . $activeTasks[$taskID]["command_run"] . "\"";
        exec($command, $output, $retval);
        if ($retval != 0) {
            $output = implode("\n", $output);
            echo("manage(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        } else {
            updateTaskStatus($taskID, 4); // in progress
        }
    }
    
    // find what should be stop
    $toStop = get_tasks_to_stop(array_keys($activeTasks), $actualTasks);
    // and stop
    foreach ($toStop as $taskID) {
        print("task to stop: " . $taskID . "\n");
        $taskName = "Task_" . $taskID;
        // stop task
        $command = "ssh " . REMOTE_SSH_USER . "@" . REMOTE_SSH_HOST . " -t \"docker container kill $taskName\"";
        exec($command, $output, $retval);
        if ($retval != 0) {
            $output = implode("\n", $output);
            echo("manage(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        } else {
            // change DB status of task
            updateTaskStatus($taskID, 6); // completed
        }
    }
}

// collect list of active tasks from DB
function collect_active_tasks() {
    GLOBAL $conn;

    $sql = "
        SELECT 
          t.`task_ID`,
          t.`status_ID`,
          t.`approved_from`,
          t.`approved_duration`,
          t.`num_resources_approved`,
          t.`command_run`
        FROM `tasks` AS t
        WHERE
          t.`status_ID` IN (2,4)
          AND NOW() BETWEEN
            `approved_from` AND
            DATE_ADD(`approved_from`, INTERVAL `approved_duration` HOUR)";

    $result = mysqli_query($conn, $sql);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    $result = array();
    foreach ($tasks as $entry) {
        $result[$entry['task_ID']] = $entry;
    }
    return $result;
}

// parse output of `docker container ls` command
function collect_actual_tasks() {
    $result = array();
    $command = "ssh -q " . REMOTE_SSH_USER . "@" . REMOTE_SSH_HOST . " -t \"docker container ls --format='{{json .}}' 2>/dev/null\"";
    exec($command, $output, $retval);

    //parse output
    if ($retval != 0) {
        $output = implode("\n", $output);
        echo("collect_actual_tasks(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        return $result;
    }

//     echo("BEGIN\n");
//     $output = implode($output, "\n");
//     $c_json = json_decode($output);
    foreach ($output as $line) {
        // parse each line and look for containers related to tasks only
        // for this purpose all containers should be properly named at
        // the moment of run
        //
        //print($line . "\n");
        $json = json_decode($line, true);
        $taskName = $json['Names']; // detected task ID

        if (strpos($taskName, "Task_") !== FALSE){
            $taskID=substr($taskName, 5);
            $result[] = $taskID;
//            echo("to process: " . $taskID . "\n");
        } else {
            echo("[!!] NON SYSTEM task FOUND: " . $taskName . "\n");
        }
    }
//     echo("END\n");
    return $result;
}

// detect list of tasks to run
function get_tasks_to_run($active_task, $actual_task) {
    return array_diff($active_task, $actual_task);
}

// detect list of tasks to stop
function get_tasks_to_stop($active_task, $actual_task) {
    return array_diff($actual_task, $active_task);
}

function updateTaskStatus($taskId, $statusId) {
    GLOBAL $conn;
    $sql = "
        UPDATE `tasks`
        SET
            `status_ID` = ?
        WHERE
            `task_ID` = ?
        ";

    // Check DB connection
    if ($conn->connect_error) {
        die("DB Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $statusId, $taskId);
    $stmt->execute();
    $stmt->close();
}

function create_approved_tasks_directories() {
    GLOBAL $conn;

    $sql = "
        SELECT
            `t`.`task_ID`,
            `sa`.`name` AS `server_account`
        FROM `tasks` AS `t`
        JOIN `users` AS `u` ON (`t`.`user_ID` = `u`.`user_ID` AND NOT `u`.`server_account_ID` IS NULL)
        JOIN `server_accounts` AS `sa` ON (`sa`.`account_ID` = `u`.`server_account_ID`)
        WHERE
            `t`.`status_ID` = 2
        AND DATE_ADD(`t`.`approved_from`, INTERVAL `t`.`approved_duration` HOUR) > NOW()";
    $result = mysqli_query($conn, $sql);
    $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    $result = array();
    foreach ($tasks as $entry) {
        $owner = $entry["server_account"];
        $taskPath = "/home/runner/shared/$owner/Task_".$entry['task_ID'];
        $command = "ssh -q " . REMOTE_SSH_USER . "@" . REMOTE_SSH_HOST . " -t \"mkdir -p $taskPath\"";
        exec($command, $output, $retval);
        if ($retval != 0) {
            $output = implode("\n", $output);
            echo("create_approved_tasks_directories(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        }
    }
    return $result;
}

function check_server_accounts() {
    GLOBAL $conn;
    $command = "ssh -q " . REMOTE_SSH_USER . "@" . REMOTE_SSH_HOST . " -t \"cd /home/runner/keys/; ls -1 *.pub 2>/dev/null\"";
    exec($command, $output, $retval);

    if ($retval != 0) {
        $output = implode("\n", $output);
        echo("check_server_accounts(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        return;
    }

    $select_query = "
        SELECT
            `name`
        FROM `server_accounts`
    ";

    $stmt1 = $conn->prepare($select_query);
    $stmt1->execute();
    $accounts = $stmt1->get_result()->fetch_all();
    $accounts = array_map(function($item) {return $item[0];}, $accounts);
    $stmt1->close();

    $insert_query = "
        INSERT INTO `server_accounts`
            (`name`)
        VALUES
            (?)
    ";

    $stmt2 = $conn->prepare($insert_query);

    foreach ($output as $line) {
        // parse each line and extract name of server account
        $publicKey = $line;

        $account = substr($publicKey, 0, strlen($publicKey) - 4);

        if (!in_array($account, $accounts)) {
            echo("New server account found: $account\n");
            $stmt2->bind_param("s", $account);
            $stmt2->execute();
        }
    }
    $stmt2->close();
}
