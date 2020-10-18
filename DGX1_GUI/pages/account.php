<?php 
//include('../server/server.php');
if (session_status() == PHP_SESSION_NONE)
    session_start();
    
if (!isset($_SESSION['user_ID'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: /login');
}

include(__BASE_PATH__ . '/pages/header.php'); ?>

<!-- Confirm Cancel Modal Structure -->
<div id="modal_cancel" class="modal">
    <section class="container center">
        <div class="modal-content">
            <h4>Are you sure?</h4>
            <p>Are you sure you want to <font color="red">CANCEL</font> this request? You cannot change this later.</p>
            <div class="center">
                <button class="modal-close btn-small waves-effect waves-light grey" style="margin:1%" name="action">Back</button>
                <a href="#!" class="modal-close btn-small waves-effect waves-light red" style="margin:1%" name="action" onClick="confirmCancel()">Yes, Cancel it</a>
            </div>
        </div>
    </section>
</div>

<!-- Table Container -->
<?php include(__BASE_PATH__ . '/templates/footable.php'); ?>

<script type="text/javascript">
	user_ID = <?php echo $_SESSION['user_ID'] ?>;
</script>
<script type="text/javascript" src="/js/account.js"></script>

<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>
