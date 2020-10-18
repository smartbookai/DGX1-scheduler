<?php 
//include(__BASE_PATH__ . '/server/server.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_ID'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: /login');
}
?>

<?php include(__BASE_PATH__ . '/pages/header.php'); ?>

<section class="container">
    <div id="skedtape">
    </div>
</section>

<script type="text/javascript" src="/js/SkedTape.js"></script>

<div class="divider"></div>
<!-- Form -->
<div>
    <section class="section container" style="width: 100%;">
        <div class="row" style='margin: auto;'>
            <div class="col s12 l5">
                <h2 class="cyan-text text-darken-4 center" style="margin-left: auto; margin-right: auto; max-width: 350px;">Request Resources</h2>
            </div>
            <div class="col s12 l5">
                <form id="request_form" action="" method="">
                    <div class="row">
                        <div class="row" style="margin-top:20px;">
                            <!-- GPUs Input Field -->
                            <div class="col s6">
                                <div class="input-field">
                                    <input disabled type="text" name="gpus" id="gpus" value="1" />
                                    <label for="gpus">Number of GPUs required</label>
                                    <div style="position: relative; margin-top: -26px;">
                                        <p class="range-field" style="margin: 0px;">
                                            <input type="range" id="numGPUs" name="numGPUs" min="1" max="8" value="1" onchange="showNum(this.value); enableCheck();" />
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Number of hours required -->
                            <div class="col s6">
                                <div class="input-field">
                                    <input required type="number" id="numHours" name="numHours" onchange="limitNumHours(); enableCheck()" min=1 max=168>
                                    <label for="numHours">Number of hours</label>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-top:20px;">
                            <!-- Container dropdown -->
                            <div class="input-field col s6">
                                <select id="container-selector" name="container-selector" onchange="enableCheck(); changeDesc(this.value);">
                                    <option value="" disabled selected>Select a container</option>
                                    <?php include(__BASE_PATH__ . '/helpers/getContainerNames.php'); ?>
                                </select>
                                <label>Container</label>
                            </div>

                            <!-- Date Selection -->
                            <div class="col s3">
                                <div class="input-field">
                                    <input disabled type="text" id="date" name="date" class="datepicker" onchange="; enableCheck();">
                                    <label for="date">Select a date</label>
                                </div>
                            </div>
                            <div class="col s3">
                                <div class="input-field">
                                    <input readonly type="text" id="time" name="time" class="timepicker" onchange="enableCheck();" onmousedown="M.Timepicker.getInstance(document.getElementById('time')).open()">
                                    <label for="time">Select a time</label>
                                </div>
                            </div>

                            <div class="col s12">
                                <p id="description"></p>
                            </div>
                        </div>

                        <div class="row" style="margin-top:20px;">

                            <!-- Additional Notes -->
                            <div class="input-field col s12">
                                <textarea id="comments" name="comments" class="materialize-textarea"></textarea>
                                <label for="comments">Additional Notes</label>
                            </div>
                        </div>

                        <!-- Submit button -->
                        <div class="col s12">
                            <button disabled class="btn waves-effect waves-light right" type="submit" id="submit" name="submit">Submit Request
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<!-- Request submitted Modal Structure -->
<div id="modal_submitted" class="modal">
    <div class="modal-content center">
        <section class="section container" style="width: 50%; height:50%;"></section>
        <h4>Request Submitted</h4>
        <p>Thanks for using our portal! We will review your request and contact you soon.</p>
        <div class="center">
            <button class="modal-close btn-small waves-effect waves-light" onclick="location.reload()">Okay</button>
        </div>
        </section>
    </div>
</div>

<!-- Request submitted Modal Structure -->
<div id="modal_refresh" class="modal">
    <div class="modal-content center">
        <section class="section container" style="width: 50%; height:50%;"></section>
        <h4>Database not in sync</h4>
        <p>This slot may have just been assigned, please refresh the page and try again.</p>
        <div class="center">
            <button class="modal-close btn-small waves-effect waves-light red" onclick="location.reload()">Okay</button>
        </div>
        </section>
    </div>
</div>

<!-- Request submitted Modal Structure -->
<div id="modal_failed" class="modal">
    <div class="modal-content center">
        <section class="section container" style="width: 50%; height:50%;"></section>
        <h4>Request allocation failed</h4>
        <p>Something wrong happened on server. Try again later. If error is persistent contact administrator.</p>
        <div class="center">
            <button class="modal-close btn-small waves-effect waves-light red" onclick="location.reload()">Okay</button>
        </div>
        </section>
    </div>
</div>

<!-- Compiled and minified JavaScript -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="/js/availability.js"></script>
<script type="text/javascript" src="/js/initMaterialize.js"></script>

<script type="text/javascript" src="/js/request.js"></script>

<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>
