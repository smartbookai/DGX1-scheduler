<section class="container">
    <!-- Filter UI -->
    <form id="table_filter_ui" action="" method="" style="margin: auto">
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status_under_review" value="under review" type="radio" checked />
            <span>Under Review</span>
        </label>
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status__approved" value="approved" type="radio" />
            <span>Approved</span>
        </label>
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status_rejected" value="rejected" type="radio" />
            <span>Rejected</span>
        </label>
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status_cancelled" value="canceled" type="radio" />
            <span>Cancelled</span>
        </label>
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status_inprogress" value="in progress" type="radio" />
            <span>In Progress</span>
        </label>
        <label style="padding-right:50px;">
            <input class="with-gap" name="status" id="status_completed" value="completed" type="radio" />
            <span>Completed</span>
        </label>
    </form>

    <!-- Table -->
    <table id="footable" class="table" data-use-parent-width="true" data-paging="true" data-sorting="true" data-editing="true" data-editing-always-show="true" data-editing-allow-add="false" data-editing-view-text='<i class="material-icons" style="font-size:12px;">check</i>' data-editing-edit-text='<i class="material-icons" style="font-size:12px">edit</i>' data-editing-delete-text='<i class="material-icons" style="font-size:12px">close</i>'>
</section>

<script type="text/javascript" src="/js/footable.js"></script>