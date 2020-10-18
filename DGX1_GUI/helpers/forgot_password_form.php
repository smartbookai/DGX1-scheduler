<section class="section container" style="width: 100%;">
    <!-- Email Submission Form -->
    <div class="col s12 m12 offset-m1 center">
        <div style="margin:15%">
            <h2 class="cyan-text text-darken-4 center" style="margin-left: auto; margin-right: auto; max-width: 500px;">Email Submission</h2>
            <form id="email_submission_form" action="/reset_password" method="POST">
                <?php
                if (isset($_POST['send_reset_email'])) {
                    include(__BASE_PATH__ . '/helpers/errors.php');
                    if (count($errors) == 0) {
                        $i = 0;
                        ?>
                        <div class="success">
                            <p>The password reset email was sent successfully.</p>
                        </div>
                        <?php
                    }
                }
                ?>

                <div class="input-field">
                    <input id="reset_email" name="reset_email" type="email" value="<?php if (isset($reset_email)) {echo($reset_email);} ?>">
                    <label for="reset_email">Your Email</label>
                </div>

                <div style="position: relative; top: -10px;">

                    <button class="btn waves-effect waves-light right" type="submit" name="send_reset_email">Submit
                        <i class="material-icons right">send</i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>