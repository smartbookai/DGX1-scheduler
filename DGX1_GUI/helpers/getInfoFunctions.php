<?php
function getTaskResourcesFor($task_id)
{
    global $conn;
    $sql = "
        SELECT 
            r.`type`,
            r.`value`
        FROM `task_resources` AS tr
        JOIN `resources` AS r ON (r.`resource_ID` = tr.`resource_ID`)
        WHERE 
            tr.`task_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "i",
        $task_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $result;
}

function getTaskInfoFor($task_id)
{
    global $conn;
    $sql = "
        SELECT 
            t.`task_ID`,
            c.`name` AS `container`,
            sa.`name` AS `server_account`
        FROM `tasks` AS t
        JOIN `containers` AS c ON (t.`container_ID` = c.`container_ID`)
        JOIN `users` AS u ON (t.`user_ID` = u.`user_ID`)
        JOIN `server_accounts` AS sa ON (sa.`account_ID` = u.`server_account_ID`)
        WHERE 
            t.`task_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "i",
        $task_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($result) > 0) {
        $result = $result[0];
    }
    
    $stmt->close();
    return $result;
}

function getUserInfoFor($task_id)
{
    global $conn;
    $sql = "
        SELECT 
            t.`task_ID`,
            u.`name` AS `name`,
            u.`email` AS `email`
        FROM `tasks` AS t
        JOIN `users` AS u ON (t.`user_ID` = u.`user_ID`)
        WHERE 
            t.`task_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "i",
        $task_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($result) > 0) {
        $result = $result[0];
    }

    $stmt->close();
    return $result;
}

function getResourceInfoFor($resource_id)
{
    global $conn;
    $sql = "
        SELECT
            `resource_ID` AS `id`,
            `type`,
            `value`
        FROM `resources`
        WHERE
            `resource_ID` = ?
    ";
    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // prepare, bind data and execute
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "i",
        $resource_id
    );

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    if (count($result) > 0) {
        $result = $result[0];
    }

    $stmt->close();
    return $result;
}