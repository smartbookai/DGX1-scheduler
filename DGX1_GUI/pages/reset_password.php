<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include(__BASE_PATH__ . '/database_connection.php');
include(__BASE_PATH__ . '/helpers/email.php');

$errors = array();

include(__BASE_PATH__ . '/pages/header.php');


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // two cases possible
    // 1. logged in user try to change password
    // 2. user forgot password and try to reset it
    //  2.1 request token
    //  2.2 change password

    if (isset($_SESSION["user_ID"])) {
        //  CASE #1
        //show change password form
        include(__BASE_PATH__ . '/helpers/reset_password_form.php');

    } else {
        if (!isset($_GET['token'])) {
            // CASE #2.1
            //no token present
            // just show send link form
            include(__BASE_PATH__ . '/helpers/forgot_password_form.php');
        } else {
            // CASE #2.2
            //check if token valid
            //and show change password form
            $reset_token = $_GET['token'];
            $reset_token_sql = "
                SELECT
                    `u`.*
                FROM `users` AS `u`
                WHERE `u`.`reset_token` = ?
                LIMIT 1
                ";

            $stmt = $conn->prepare($reset_token_sql);
            $stmt->bind_param("s", $reset_token);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows != 0) {
                $row = $result->fetch_assoc();
                $user_ID = $row['user_ID'];
            }
            $stmt->close();
            include(__BASE_PATH__ . '/helpers/reset_password_form.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (isset($_POST['reset_password'])) {
        $errors = array();

        $reset_token = isset($_POST['token'])?$_POST['token']:null;
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];
        if (isset($_SESSION['user_ID'])) {
            $user_ID = $_SESSION['user_ID'];
        } else {
            $user_ID = $_POST['user_id'];
        }

        if (!isset($_SESSION['user_ID']) && empty($reset_token)) {
            array_push($errors, "Reset token is required");
        }

        if (empty($user_ID)) {
            array_push($errors, "Cannot detect proper user to reset password.");
        }

        if (empty($new_password)) {
            array_push($errors, "Password is required");
        }

        if (empty($confirm_new_password)) {
            array_push($errors, "Confirm password field is required");
        }

        if ($new_password !== $confirm_new_password) {
            array_push($errors, "Passwords do not match");
        }

        if (count($errors) == 0) {
            $new_password_encrypted = md5($new_password);
            if (isset($_SESSION['user_ID'])) {
                $password_update_query = "
                    UPDATE `users`
                    SET
                        `password` = ?,
                        `reset_token` = NULL
                    WHERE `user_ID` = ?
                    ";
                $stmt = $conn->prepare($password_update_query);
                $stmt->bind_param("si", $new_password_encrypted, $user_ID);
                if (!$stmt->execute()) {
                    array_push($errors, "DB error");
                }
                $stmt->close();
            } else {
                $password_update_query = "
                    UPDATE `users`
                    SET
                        `password` = ?,
                        `reset_token` = NULL
                    WHERE `user_ID` = ?
                        AND `reset_token` = ?
                    ";
                $stmt = $conn->prepare($password_update_query);
                $stmt->bind_param("sis", $new_password_encrypted, $user_ID, $reset_token);
                if (!$stmt->execute()) {
                    array_push($errors, "DB error");
                }
                $stmt->close();

                if (count($errors) == 0) {
//                    header('location: /login');
//                    exit();
                }
            }
        }
        include(__BASE_PATH__ . '/helpers/reset_password_form.php');
    }




    if (isset($_POST['send_reset_email'])) {
        $errors = array();
        // receive all input values from the form

        $sql = "
            SELECT
                u.*
            FROM `users` AS u
            WHERE u.`email` = ?
            LIMIT 1
        ";


        $reset_email = $_POST['reset_email'];

        // form validation: ensure that the form is correctly filled ...
        // by adding (array_push()) corresponding error unto $errors array
        if (empty($reset_email)) {
            array_push($errors, "Email is required");
        }

        if (count($errors) == 0) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $reset_email);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows != 0) {
                    $user_id = $result->fetch_assoc()["user_ID"];
                }
            } else {
                array_push($errors, "DB error");
            }
            $stmt->close();

            if (isset($user_id)) {
                $token = bin2hex(random_bytes(32));
                $reset_url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/reset_password?token=" . $token;

                $conn->autocommit(FALSE);
                $token_query = "
                    UPDATE `users`
                    SET `reset_token` = ?
                    WHERE `user_ID` = ?
                    ";

                $stm = $conn->prepare($token_query);
                $stm->bind_param("si", $token, $user_id);
                if (!$stm->execute()) {
                    $conn->rollback();
                    array_push($errors, "DB error");
                }


                //send email
                $message_type = 7;
                $to = $reset_email;
                $subject = "DGX-1 Portal Password Reset Request";
                $message = file_get_contents(__BASE_PATH__ . '/email_templates/reset_password.html');
                $message = str_replace('%reset_url%', $reset_url, $message);


                $status = sendEmail($to, $subject, $message);

                //DO NOT STORE EMAIL INTO DB
//                $status = ($status == 1) ? "SENT" : "FAILED";
//                $task_id = 0;
//                //save status of send into db
//                $sql4 = "INSERT INTO `emails`(
//                            `task_ID`,
//                            `type`,
//                            `content`,
//                            `status`,
//                            `time_sent`
//                        )
//                        VALUES(
//                            ?,
//                            ?,
//                            ?,
//                            ?,
//                            CURRENT_TIMESTAMP()
//                        )";
//
//                $stmt4 = $conn->prepare($sql4);
//                $stmt4->bind_param(
//                        "iiss",
//                        $task_id,
//                        $message_type,
//                        $message,
//                        $status
//                );
//
//                if (!$stmt4->execute()) {
//                    $conn->rollback();
//                    array_push($errors, "DB error");
//                }
//                $stmt4->close();

                if (count($errors) == 0 ) {
                    $conn->commit();
//                    header('location: /login');
//                    exit();
                } else {
                    $conn->rollback();
                }
            } else {
                array_push($errors, "User with this email doesn't exist");
            }

        }
        include(__BASE_PATH__ . '/helpers/forgot_password_form.php');
    }
}

include(__BASE_PATH__ . '/pages/footer.php'); ?>