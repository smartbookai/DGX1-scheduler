<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include(__BASE_PATH__ . '/pages/header.php');
include(__BASE_PATH__ . '/database_connection.php');
include(__BASE_PATH__ . '/helpers/email.php');

//Check connection
if (!$conn) {
    echo "Database connection error";
}

$errors = array();


if (isset($_SESSION['user_ID'])) {
//    header('location: /reset_password');
    header('location: /request');
    exit();
}

// REGISTER USER
if (isset($_POST['signup'])) {
    // receive all input values from the form
    $affiliation_id = $_POST['affiliation_id'];
    $signup_name = $_POST['signup_name'];
    $signup_email = $_POST['signup_email'];
    $signup_password = $_POST['signup_password'];
    $signup_confirm_password = $_POST['signup_confirm_password'];

    // form validation: ensure that the form is correctly filled ...
    // by adding (array_push()) corresponding error unto $errors array
    if (empty($affiliation_id)) {
        //TODO: check validity of affiliation ID
        array_push($errors, "Affiliation is required");
    }
    if (empty($signup_name)) {
        array_push($errors, "Name is required");
    }
    if (empty($signup_email)) {
        array_push($errors, "Email is required");
    }
    if (empty($signup_password)) {
        array_push($errors, "Password is required");
    }
    if ($signup_password != $signup_confirm_password) {
        array_push($errors, "The two passwords do not match");
    }

    // first check the database to make sure
    // a user does not already exist with the same email

    $user_check_query = "
        SELECT *
        FROM users
        WHERE email = ?
        LIMIT 1";
    $stmt1 = $conn->prepare($user_check_query);
    $stmt1->bind_param("s", $signup_email);
    $stmt1->execute();

    if ($stmt1->get_result()->num_rows > 0) {
        array_push($errors, "Email already exists");
    }
    $stmt1->close();

    // Finally, register user if there are no errors in the form
    if (count($errors) == 0) {

        $signup_password_encrypted = md5($signup_password); //encrypt the password before saving in the database

//        $conn->autocommit(FALSE);
        
        $query = "
            INSERT INTO `users` (
                `name`,
                `email`,
                `password`,
                `is_admin`,
                `affiliation_ID`
            ) VALUES (
                ?,
                ?,
                ?,
                0,
                ?
            )";
        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param(
                "sssi",
                $signup_name,
                $signup_email,
                $signup_password_encrypted,
                $affiliation_id
        );

        if (!$stmt2->execute()) {
            error_log("Can't insert new user: " . $conn->error);
//            $conn->rollback();
            array_push($errors, "Server error happened. Try later.");
        }

        $user_id = $stmt2->insert_id;
        $stmt2->close();

        if (count($errors) == 0) {
            //send email

            $message_type = 1;
            $to = $signup_email;
            $subject = "DGX-1 Portal Credentials";
            $message = file_get_contents(__BASE_PATH__ . '/email_templates/registration.html');
            $message = str_replace('%username%', $signup_email, $message);
            $message = str_replace('%password%', $signup_password, $message);

            try {
                $status = sendEmail($to, $subject, $message);
            } catch (Exception $e) {
                error_log("Email sent failed: " . $e->getMessage());
                $status = 0;
            }

            //DO NOT SAVE EMAIL INTO DB
//            $status = ($status == 1) ? "SENT" : "FAILED";
//            $task_id = 0;
//            //save status of senc into db
//            $sql3 = "INSERT INTO `emails`(
//                        `task_ID`,
//                        `type`,
//                        `content`,
//                        `status`,
//                        `time_sent`
//                    )
//                    VALUES(
//                        ?,
//                        ?,
//                        ?,
//                        ?,
//                        CURRENT_TIMESTAMP()
//                    )";
//
//            $stmt3 = $conn->prepare($sql3);
//            $stmt3->bind_param(
//                    "iiss",
//                    $task_id,
//                    $message_type,
//                    $message,
//                    $status
//            );
//
//            if (!$stmt3->execute()) {
//                $conn->rollback();
//                array_push($errors, "Server error happened. Try later.");
//            }
//            $stmt3->close();
        }

        if (count($errors) == 0) {
//            $_SESSION['user_ID'] = $user_id;
//            $_SESSION['isAdmin'] = FALSE;
//            $_SESSION['user_email'] = $signup_email;
//            $_SESSION['success'] = "You are now logged in";

//            $conn->commit();
            header('location: /login');
        }
    }
} else if (isset($_POST['login'])) {

    $login_email = $_POST['login_email'];
    $login_password = $_POST['login_password'];

    if (empty($login_email)) {
        array_push($errors, "Email is required");
    }
    if (empty($login_password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $login_password = md5($login_password);

        $query = "
            SELECT *
            FROM users
            WHERE email = ? AND password = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $login_email, $login_password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $_SESSION['user_email'] = $login_email;
            $_SESSION['success'] = "You are now logged in";
            $row = $result->fetch_assoc();
            if ($row["is_admin"] == 1) {
                $_SESSION['isAdmin'] = TRUE;
            }
            $_SESSION['user_ID'] = $row["user_ID"];

            header('location: /request');
            exit();
        } else {
            array_push($errors, "Wrong username/password combination");
        }
    }
}
?>


<section class="section container" style="width: 100%;">
    <div class="row">
        <!-- Login Form -->
        <div class="col s12 m5 offset-m1 center">
            <div style="margin:15%">
                <h2 class="cyan-text text-darken-4 center" style="margin-left: auto; margin-right: auto; max-width: 350px;">Login</h2>
                <form id="login_form" action="/login" method="POST">
                    <?php
                    if (isset($_POST['login'])) {
                        include(__BASE_PATH__ . '/helpers/errors.php');
                    }
                    ?>
                    <div class="input-field">
                        <input id="login_email" name="login_email" type="email" value="<?php if (isset($login_email)) {echo $login_email;} ?>">
                        <label for="login_email">Your Email</label>
                    </div>

                    <div class="input-field">
                        <input id="login_password" name="login_password" type="password" class="validate">
                        <label for="login_password">Your Password</label>
                    </div>

                    <div style="position: relative; top: -10px;">

                        <button class="btn waves-effect waves-light right" type="submit" name="login">Login
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                    <div style="position: relative; left: -10px;">
                        <a href="/reset_password">Forgot password?</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sign up Form -->
        <div class="col s12 m5 center" style="border-left: 1px solid gray;">
            <div style="margin:10%">
                <h2 class="cyan-text text-darken-4 center" style="margin-left: auto; margin-right: auto; max-width: 350px;">Sign up</h2>
                <form id="signup_form" action="/login" method="POST">
                    <?php
                    if (isset($_POST['signup'])) {
                        include(__BASE_PATH__ . '/helpers/errors.php');
                    }
                    ?>

                    <div class="input-field" style="margin-top:30px; margin-bottom:15px;">
                        <select id="affiliation_selector" name="affiliation_id">
                            <option value="" disabled selected>Select your affiliation</option>
                            <?php include(__BASE_PATH__ . '/helpers/getAffiliationNames.php'); ?>
                        </select>
                        <label>Affiliation</label>
                    </div>

                    <div class="input-field">
                        <input type="text" id="signup_name" name="signup_name" value="<?php if (isset($signup_name)) {echo($signup_name);} ?>">
                        <label for="signup_name">Your Name</label>
                    </div>

                    <div class="input-field">
                        <input type="email" id="signup_email" name="signup_email" value="<?php if (isset($signup_email)) {echo($signup_email);} ?>">
                        <label for="signup_email">Your Email</label>
                    </div>

                    <div class="input-field">
                        <input type="password" id="signup_password" name="signup_password" class="validate" value="<?php if (isset($signup_password)) {echo($signup_password);} ?>">
                        <label for="signup_password">Your Password</label>
                    </div>

                    <div class="input-field">
                        <input type="password" id="signup_confirm-password" name="signup_confirm_password" class="validate" value="<?php if (isset($signup_confirm_password)) {echo($signup_confirm_password);} ?>">
                        <label for="signup_password">Confirm Password</label>
                    </div>


                    <div style="position: relative; top: -10px;">
                        <button class="btn waves-effect waves-light right" type="submit" name="signup">Sign up
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $("#affiliation_selector").formSelect();
    });
</script>
<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>
