<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['isAdmin'])) {
    $_SESSION['msg'] = "You must be an admin";
    header('location: /login');
}

include(__BASE_PATH__ . '/pages/header.php');
?>

<!-- Confirm Delete Modal Structure -->
<div id="modal_delete" class="modal">
    <section class="container center">
        <div class="modal-content">
            <h4>Are you sure?</h4>
            <p>Are you sure you want to <font color="red">DELETE</font> this user? You cannot change this later.</p>
            <div class="center">
                <button class="modal-close btn-small waves-effect waves-light grey" style="margin:1%" name="action">Back</button>
                <a href="#!" class="modal-close btn-small waves-effect waves-light red" style="margin:1%" name="action" onClick="confirmDelUser()">Yes, delete this user.</a>
            </div>
        </div>
    </section>
</div>

<!-- Edit Modal Structure -->
<div id="modal_edit" class="modal bottom-sheet" style="height: 100%;">
    <section class="container">
        <div class="modal-content">
            <h4>Edit User</h4>
            <form id="modal_edit_form" action="" method="">
            </form>
        </div>
        <div class="modal-footer">
            <!-- javascript:location.reload(); -->
            <a href="#!" class="modal-close btn-small waves-effect waves-light right" style="margin:1%" id="save" name="action" onClick="confirmEdit()">Save changes</a>
            <button class="modal-close btn-small waves-effect waves-light right grey" style="margin:1%" name="action">Cancel</button>
        </div>
    </section>
</div>

<!-- Add Server Account Structure -->
<div id="modal_add_server_account" class="modal bottom-sheet" style="height: 100%;">
    <section class="container">
        <div class="modal-content">
            <h4>Add Server Account</h4>
            <form id="modal_add_server_account_form" action="" method="">
                <div class="input-field col s12">
                    <input type="text" name="server_account" id="server_account" class="validate">
                    <label for="server_account">Server Account</label>
                    <span class="helper-text" data-error="Should start from latin letter or '_'. Can contains only small latin letters, digits, '_' and '-' symbols"></span>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#!" class="btn-small waves-effect waves-light right" style="margin:1%" id="save" name="action" onClick="confirmAddServerAccount()">Save changes</a>
            <button class="modal-close btn-small waves-effect waves-light right grey" style="margin:1%" name="action">Cancel</button>
        </div>
    </section>
</div>

<section class="container">
    <div class="col s12 m12 offset-m10 center"></div>
    <div class="col s12 m12 right">
        <a id="add_server_account_btn" class = "waves-effect waves-light btn">Add Server Account</a>
    </div>
</section>


<!-- Scripts -->
<script type="text/javascript" src="/js/availability.js"></script>
<script type="text/javascript" src="/js/initMaterialize.js"></script>
<script type="text/javascript" src="/js/footable.js"></script>


<!-- Table Container -->
<?php include(__BASE_PATH__ . '/templates/userFootable.php'); ?>

<script type="text/javascript">
    user_ID = <?php echo $_SESSION['user_ID'] ?>;
</script>
<script type="text/javascript" src="/js/users.js"></script>

<?php include(__BASE_PATH__ . '/pages/footer.php'); ?>
