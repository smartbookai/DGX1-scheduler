#!/usr/bin/php
<?php


$year = date("Y");
$month = date("m");
//detect how script was ran
//from shell or webserver
if(php_sapi_name() != "cli") {
    echo("can be runned only in CLI mode");
    die();
} else {
    $shortopts  = "y:m:";
    $longopts  = array(
        "year:",
        "month:",
    );

    $options = getopt($shortopts, $longopts);

    if (array_key_exists('y', $options)) {
        $year = $options['y'];
    }
    if (array_key_exists('year', $options)) {
        $year = $options['year'];
    }

    if (array_key_exists('m', $options)) {
        $month = $options['m'];
    }

    if (array_key_exists('month', $options)) {
        $month = $options['month'];
    }
}





define("DB_HOST", "localhost");
define("DB_NAME", "dgx1_db");
define("DB_USER","dgx1_db");
define("DB_PASSWORD", "53cr3tP@ssw0rd");
// Connect to database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

//Check connection
if(!$conn){
    echo "Connection error: " . mysqli_connect_error();
    exit(1);
}

//collect and print report data
print_CSV(collect_load_statistic($year, $month));










function collect_load_statistic($year, $month) {
    GLOBAL $conn;

    $start_date = date_create("$year-$month-01 00:00:00");
    $end_date = date_create("$year-$month-" . cal_days_in_month(CAL_GREGORIAN, $month, $year) . " 23:59:59");
    $str_start_date = date_format($start_date, "Y-m-d H:i:s");
    $str_end_date = date_format($end_date, "Y-m-d H:i:s");

    $sql = "
        SELECT
            concat('Task_', `t`.`task_ID`) AS `name`,
            `s`.`name` AS `status`,
            `u1`.`name` AS `task_user`,
            `u2`.`name` AS `approved_by`,
            `u3`.`name` AS `canceled_by`,
            `t`.`approved_from` AS `start_time`,
            `t`.`approved_duration` AS `duration`,
            `t`.`canceled_at` AS `canceled_at`,
            IF(
                (NOT `t`.`canceled_at` IS NULL
                AND NOT `t`.`approved_from` IS NULL
                AND `t`.`canceled_at` > `t`.`approved_from`),
                TIMESTAMPDIFF(HOUR, `t`.`approved_from`,`t`.`canceled_at`),
                `t`.`approved_duration`
            ) AS `processing_time`
        FROM `tasks` AS `t`
        LEFT JOIN `status` AS `s` ON (`s`.`status_ID` = `t`.`status_ID`)
        LEFT JOIN `users` AS `u1` ON (`u1`.`user_ID` = `t`.`user_ID`)
        LEFT JOIN `users` AS `u2` ON (`u2`.`user_ID` = `t`.`approved_by`)
        LEFT JOIN `users` AS `u3` ON (`u3`.`user_ID` = `t`.`canceled_by`)
        WHERE
            `t`.`status_ID` IN (4,5,6)
            AND NOT `t`.`approved_at` IS NULL
            AND (   `t`.`approved_from` BETWEEN ? AND ?
                OR  DATE_ADD(`t`.`approved_from`, INTERVAL `t`.`approved_duration` HOUR)
                    BETWEEN ? AND ?
                )
            AND IF(NOT `t`.`canceled_at` IS NULL, IF(`t`.`canceled_at` > `t`.`approved_from`, TRUE, FALSE), TRUE)
        ORDER BY `t`.`task_ID`
        ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssss",
        $str_start_date, $str_end_date,
        $str_start_date, $str_end_date
    );

    $stmt->execute();
    $processing_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();



    $result = process_db_data($processing_data, $start_date, $end_date);
    return $result;
}

function process_db_data($data, $start_date, $end_date) {
    $result = array();

    foreach ($data as $entry) {
        $tmp = $entry;

        $task_start = date_create($entry['start_time']);
        $task_end = date_add(date_create($entry['start_time']), new DateInterval('PT' . $entry['duration'] .  'H'));

        if ($entry['canceled_at']) {
            $task_end = date_create($entry['canceled_at']);
        }

        if ($task_end < $start_date) {
            continue;
        }

        $tmp['end_time'] = date_format($task_end, "Y-m-d H:i:s");

        if ($task_start < $start_date) {
            $diff = date_diff($start_date, $task_start);
            $tmp['processing_time'] = $tmp['processing_time'] - ($diff->format('%d') * 24 + $diff->format('%h'));
        }

        if ($task_end > $end_date) {
            $diff = date_diff($task_end, $end_date);
            $tmp['processing_time'] = $tmp['processing_time'] - ($diff->format('%d') * 24 + $diff->format('%h'));
        }

        $result[] = $tmp;
    }
    return $result;
}

function print_CSV($data) {
    echo ("name,status,user,approver,canceler,start_time,duration,cancelation_time,processing_time\n");
    foreach($data as $entry) {
        echo(
                "\"" . $entry['name'] . "\"," .
                "\"" . $entry['status'] . "\"," .
                "\"" . $entry['task_user'] . "\"," .
                "\"" . $entry['approved_by'] . "\"," .
                "\"" . $entry['canceled_by'] . "\"," .
                $entry['start_time'] . "," .
                $entry['duration'] . "," .
                $entry['canceled_at'] . "," .
                $entry['processing_time'] . "\n"
        );
    }
}

//$start_date = date_create("2020-05-01 00:00:00");
//$end_date = date_create("2020-05-10 23:59:59");
//$data = array(
//  array(
//      "name" => 1,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-04-30 22:00:00",
//      "duration" => 36,
//      "canceled_at" => null,
//      "processing_time" => 36
//  ),
//  array(
//      "name" => 2,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-05-02 00:00:00",
//      "duration" => 24,
//      "canceled_at" => null,
//      "processing_time" => 24
//  ),
//  array(
//      "name" => 3,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-05-09 00:00:00",
//      "duration" => 72,
//      "canceled_at" => null,
//      "processing_time" => 72
//  ),
//  array(
//      "name" => 4,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-04-30 22:00:00",
//      "duration" => 36,
//      "canceled_at" => "2020-04-30 23:00:00",
//      "processing_time" => 1
//  ),
//  array(
//      "name" => 5,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-05-01 22:00:00",
//      "duration" => 36,
//      "canceled_at" => "2020-05-01 23:10:00",
//      "processing_time" => 1
//  ),
//  array(
//      "name" => 6,
//      "status" => "",
//      "task_user" => "",
//      "approved_by" => "",
//      "canceled_by" => "",
//      "start_time" => "2020-05-09 00:00:00",
//      "duration" => 72,
//      "canceled_at" => "2020-05-11 10:00:00",
//      "processing_time" => 58
//  ),
//);
//var_dump(process_db_data($data, $start_date, $end_date));
//print_CSV(process_db_data($data, $start_date, $end_date));

