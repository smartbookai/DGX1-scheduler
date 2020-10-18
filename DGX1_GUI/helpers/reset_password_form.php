<section class="section container" style="width: 100%;">
    <div class="col s12 m12 center">
        <div style="margin:10%">
            <h2 class="cyan-text text-darken-4 center" style="margin-left: auto; margin-right: auto; max-width: 500px;">Password Reset</h2>
            <form id="password_reset_form" action="reset_password" method="POST">
                    <?php
                        include(__BASE_PATH__ . '/helpers/errors.php');
                        if (isset($_POST['reset_password']) && count($errors) == 0) {
                    ?>
                        <div class="success">
                            <p>The password reset went successfully.</p>
                        </div>
                    <?php
                        }
                    ?>

                <?php if (!isset($_SESSION['user_ID'])) { ?>
                    <input type="hidden" id="token" name="token" value="<?php echo $reset_token; ?>">
                    <input type="hidden" id="user_ID" name="user_id" value="<?php echo $user_ID; ?>">
                <?php } ?>
                <div class="input-field">
                    <input type="password" id="new_password" name="new_password" class="validate" value="">
                    <label for="new_password">Enter Password</label>
                </div>
                <div class="input-field">
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="validate" value="">
                    <label for="confirm_new_password">Confirm Password</label>
                </div>

                <div style="position: relative; top: -10px;">
                    <button class="btn waves-effect waves-light right" type="submit" name="reset_password">Submit
                        <i class="material-icons right">send</i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
