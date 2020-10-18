#!/usr/bin/php
<?php
//detect how script was ran
//from shell or webserver
if (php_sapi_name() != "cli") {
    echo("can be runned only in CLI mode");
    die();
}

define("DB_HOST", "localhost");
define("DB_NAME", "dgx1_db");
define("DB_USER", "dgx1_db");
define("DB_PASSWORD", "53cr3tP@ssw0rd");


$date = date("Y/m/d H:i:s");
// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//Check connection
if (!$conn) {
    echo "[$date] Connection error: " . mysqli_connect_error();
}

check_server_accounts();

function check_server_accounts() {
    GLOBAL $conn, $date;


    $command = "mount -a";
    exec($command, $output, $retval);
    if ($retval != 0) {
        $output = implode("\n", $output);
        echo("[$date] check_server_accounts(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
    }

    $command = "ls -1 /home/";
    exec($command, $output, $retval);

    if ($retval != 0) {
        $output = implode("\n", $output);
        echo("[$date] check_server_accounts(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
        return;
    }

    $existing_users = array();

    foreach ($output as $line) {
        $existing_users[] = basename($line);
    }


    $select_query = "
        SELECT
            `name`
        FROM `server_accounts`
    ";

    $stmt1 = $conn->prepare($select_query);
    $stmt1->execute();
    $db_users = $stmt1->get_result()->fetch_all();
    $db_users = array_map(function($item) {
        return $item[0];
    }, $db_users);
    $stmt1->close();


    $users_to_create = array_diff($db_users, $existing_users);

    foreach ($users_to_create as $user) {
        if (preg_match("/^[a-z_][a-z0-9_-]*$/", $user) == 0) {
            echo("[$date] check_server_accounts(): detect invalid name '$user'. Skip.\n");
            continue;
        }
        echo("[$date] check_server_accounts(): creating user '$user'\n");
        $command = "/opt/dgx/create_user_loginserver.sh $user &>/opt/dgx/logs/user_monitor.log";
        exec($command, $output, $retval);

        if ($retval != 0) {
            $output = implode("\n", $output);
            echo("[$date] check_server_accounts(): command '$command'\n execution failed with return code: $retval and output\n $output\n");
            return;
        }
    }
}
