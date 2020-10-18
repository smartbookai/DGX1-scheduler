<?php 
//include(__BASE_PATH__ . '/server/server.php');
if (session_status() == PHP_SESSION_NONE)
    session_start();
    
// Make sure user is logged in and Admin
if (!isset($_SESSION['isAdmin'])) {
    $_SESSION['msg'] = "You are not an admin";
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

<!-- Connect Account Moal Structure -->
<div id="modal_server_account" class="modal">
    <div class="modal-content center" style="text-align: center">
        <section class="section container" style="position:relative; width: 50%; height:50%; text-align: center;"></section>
        <h4>Link a Server Account</h4>
        <p>Please assign this user to a server account from the dropdown menu.
        </p>
        <div class="input-field" style="width: 30%; margin: auto;">
            <select id="serverAccountSelect">
                <option value="" disabled selected>Choose your option</option>
                <option value="1">Option 1</option>
                <option value="2">Option 2</option>
                <option value="3">Option 3</option>
            </select>
        </div>
        <div class="center">
            <button class="modal-close btn-small waves-effect waves-light grey" style="margin:1%" name="action">Cancel</button>
            <!-- javascript:location.reload(); -->
            <a href="#!" class="modal-close btn-small waves-effect waves-light" style="margin:1%" name="action" onClick="assignAccount()">Assign Server Account</a>
        </div>
        </section>
    </div>
</div>

<!-- Edit Modal Structure -->
<div id="modal_edit" class="modal bottom-sheet" style="height: 100%;">
    <section class="container">
        <div class="modal-content">
            <h4>Edit Task</h4>
            <form id="modal_edit_form" action="" method="">
            </form>
        </div>
        <div class="modal-footer">
            <!-- javascript:location.reload(); -->
            <a href="#!" class="modal-close btn-small waves-effect waves-light right" style="margin:1%" id="save" name="action" onClick="mClearedReset(); confirmEdit()">Save changes</a>
            <button class="modal-close btn-small waves-effect waves-light right grey" style="margin:1%" name="action" onClick="mClearedReset()">Cancel</button>
        </div>
    </section>
</div>

<!-- Edit In Progress Modal Structure -->
<div id="modal_in_progress_edit" class="modal bottom-sheet" style="height: 100%;">
    <section class="container">
        <div class="modal-content">
            <h4>Edit Task</h4>
            <form id="modal_edit_in_progress_form" action="" method="">
            </form>
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-close btn-small waves-effect waves-light right" style="margin:1%" id="save" name="action" onClick="mClearedReset(); confirmEditTaskInProgress()">Save changes</a>
            <button class="modal-close btn-small waves-effect waves-light right grey" style="margin:1%" name="action" onClick="mClearedReset()">Cancel</button>
        </div>
    </section>
</div>

<!-- Confirm Approval Modal Structure -->
<div id="modal_approve" class="modal">
    <section class="container center">
        <div class="modal-content">
            <h4>Are you sure?</h4>
            <p id="modal_approve_content_p"></p>
            <div class="center">
                <button class="modal-close btn-small waves-effect waves-light grey" style="margin:1%" name="action">Cancel</button>
                <a href="#!" class="modal-close btn-small waves-effect waves-light green" style="margin:1%" name="action" onClick="confirmApprove()">Approve</a>
            </div>
        </div>
    </section>
</div>

<!-- Confirm Reject Modal Structure -->
<div id="modal_reject" class="modal">
    <section class="container center">
        <div class="modal-content">
            <h4>Are you sure?</h4>
            <p>Are you sure you want to <font color="red">REJECT</font> this request? You will not be able to reapprove this task.</p>
            <div class="center">
                <button class="modal-close btn-small waves-effect waves-light grey" style="margin:1%" name="action">Cancel</button>
                <!-- javascript:location.reload(); -->
                <a href="#!" class="modal-close btn-small waves-effect waves-light red" style="margin:1%" name="action" onClick="confirmReject()">Reject</a>
            </div>
        </div>
    </section>
</div>

<!-- Table Container -->
<?php include(__BASE_PATH__ . '/templates/footable.php'); ?>

<!-- Scripts -->
<script type="text/javascript" src="/js/availability.js"></script>
<script type="text/javascript" src="/js/initMaterialize.js"></script>
<script type="text/javascript" src="/js/footable.js"></script>

<script type="text/javascript">
	//UserID
	user_ID = <?php echo $_SESSION['user_ID'] ?>;
</script>
<script type="text/javascript" src="/js/admin.js"></script>

<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>
