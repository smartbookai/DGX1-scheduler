var currentRow = null;
var ft = null;
var mySchedule = null;
$(document).ready(function () {
    schedRange = 31 * 24;
    getSchedule(schedRange);
    initModals();
    initSkedTape();
    initFootableAdmin();
    initFilterUI();
    populateServerAccountSelect();
});

// Initialize the Admin FooTable
function initFootableAdmin() {
    $.get("/getTableData", {"unfiltered": 1}, function (data) {
        console.log("Type of data: " + typeof data);
        console.log("Data: " + data);
        console.log("Rows: " + data.rows);
        console.log("Columns: " + data.columns);
        console.log(JSON.parse(data.rows));
        console.log(JSON.parse(data.columns));

        // Table Initialization
        ft = FooTable.init('.table', {
            "columns": JSON.parse(data.columns),
            "rows": JSON.parse(data.rows),
            "on": {
                "before.ft.filtering": function (e, ft) {
                    //disabled everything called status
                    filter_buttons = document.getElementsByName("status");
                    for (i = 0; i < filter_buttons.length; i++) {
                        filter_buttons[i].setAttribute("disabled", "");
                    }
                    ft.$el.hide().after(ft.$loader);
                },
                "after.ft.filtering": function (e, ft) {
                    initSortUIAdmin();
                },
                "after.ft.sorting": function (e, ft) {
                    ft.$loader.remove();
                    ft.$el.show();
                    //enable everything called status
                    filter_buttons = document.getElementsByName("status");
                    for (i = 0; i < filter_buttons.length; i++) {
                        filter_buttons[i].removeAttribute("disabled");
                    }
                },
                "after.ft.paging": function (e, ft) {
                    initSortUIAdmin();
                }
            },
            "paging": {
                "size": 50
            },
            "filtering": {
                "enabled": true,
                "filters": [{
                        "name": "Status",
                        "query": "under review",
                        "columns": ["Status"]
                    }]
            },
            "breakpoints": {
                "xs": 480,
                "sm": 768,
                "md": 992,
                "lg": 1290,
                "xlg": 1400
            },
            "editing": {
                "enabled": true,
                "allowView": true,
                //edit request dynamic form generation
                "editRow": function (row) {
                    currentRow = row;
                    var values = row.val();
                    //console.log("edit");
                    if (values["Account"] === null) {
                        //call server account modal
                        serverAccountModal();
                    } else {

                        if (values["Status"] === "in progress (running)") {
                            var form = document.getElementById("modal_edit_in_progress_form");
                            var modal = document.getElementById("modal_in_progress_edit");
                            //Clear form in modal
                            while (form.hasChildNodes()) {
                                form.removeChild(form.lastChild);
                            }

                            //Generate Form in Modal
                            var row = document.createElement("div");
                            row.setAttribute('class', 'row');

                            // <div class="col s6">
                            //     <div class="input-field">
                            //         <input required type="number" id="numHours" name="numHours" onchange="limitNumHours(); enableCheck()" min=1 max=72>
                            //         <label for="numHours">Number of hours</label>
                            //     </div>
                            // </div>
                            var col = document.createElement("div");
                            col.setAttribute('class', 'col s4');

                            var div = document.createElement("div");
                            div.setAttribute('class', 'input-field');

                            var input = document.createElement("input");
                            input.setAttribute('type', 'number');
                            input.setAttribute('id', 'numHours');
                            input.setAttribute('name', 'numHours');
//							resourceIDs, start_date, task_duration, new_task_duration
                            taskEdit = 'validateInProgressEdit('.concat('[', values['ResourceIDs'], ']', ',"', values['Start'], '",', values['Duration'], ');');
//							validateInProgressEdit(values['ResourceIDs'],values['Start'],values['Duration']);
//							input.setAttribute('onchange', 'validateInProgressEdit();');
                            input.setAttribute('onchange', taskEdit);

                            input.setAttribute('min', '1');
                            input.setAttribute('max', '168');
                            input.setAttribute('value', values["Duration"]);


                            var label = document.createElement("label");
                            label.setAttribute('for', 'numHours');
                            label.innerHTML = "Extend duration for ";

                            div.appendChild(input);
                            div.appendChild(label);
                            col.appendChild(div);
                            row.appendChild(col);

                            form.appendChild(row);
                            M.updateTextFields();

                            var modal_instance = M.Modal.getInstance(modal_in_progress_edit);
                            modal_instance.open();
                            clearScheduleOfTaskInProgress(values['Task_ID']);
                            changeInProgressTasksAdmin(values['ResourceIDs'], values['Start'], values['Duration']);
                            editSkedTape();

                        } else {
                            var form = document.getElementById("modal_edit_form");
                            var modal = document.getElementById("modal_edit");

                            //Clear form in modal
                            while (form.hasChildNodes()) {
                                form.removeChild(form.lastChild);
                            }

                            //Generate Form in Modal
                            var row = document.createElement("div");
                            row.setAttribute('class', 'row');

                            for (var key in values) {
                                if (values.hasOwnProperty(key)) {
                                    if (key === "Start") {
                                        var date = new Date(values[key]);
                                        const dtf = new Intl.DateTimeFormat('en', {
                                            year: 'numeric',
                                            month: 'short',
                                            day: '2-digit'
                                        });
                                        const [{
                                                value: mo
                                            }, , {
                                                value: da
                                            }, , {
                                                value: ye
                                            }] = dtf.formatToParts(date);

                                        dateString = `${mo} ${da}, ${ye}`;

                                        //Create datepicker
                                        var col = document.createElement("div");
                                        col.setAttribute('class', 'col s4');

                                        var div = document.createElement("div");
                                        div.setAttribute('class', 'input-field');

                                        var input = document.createElement("input");
                                        input.setAttribute('type', 'text');
                                        input.setAttribute('id', 'date');
                                        input.setAttribute('name', 'date');
                                        input.setAttribute('class', 'datepicker');
                                        input.setAttribute('value', dateString);
                                        input.setAttribute('onchange', 'enableCheckAdmin()');

                                        var label = document.createElement("label");
                                        label.setAttribute('for', 'date');
                                        label.innerHTML = "Select a date";

                                        div.appendChild(input);
                                        div.appendChild(label);
                                        col.appendChild(div);
                                        row.appendChild(col);

                                        //Create timepicker
                                        var col = document.createElement("div");
                                        col.setAttribute('class', 'col s4');

                                        var div = document.createElement("div");
                                        div.setAttribute('class', 'input-field');

                                        var input = document.createElement("input");
                                        input.setAttribute('type', 'text');
                                        input.setAttribute('readonly', '');
                                        input.setAttribute('id', 'time');
                                        input.setAttribute('name', 'time');
                                        input.setAttribute('class', 'timepicker');
                                        input.setAttribute('onchange', 'enableCheckAdmin();');
                                        input.setAttribute('value', date.getHours() + ":00");
                                        input.setAttribute('onmousedown', 'M.Timepicker.getInstance(document.getElementById("time")).open()');


                                        var label = document.createElement("label");
                                        label.setAttribute('for', 'time');
                                        label.innerHTML = "Select a time";

                                        div.appendChild(input);
                                        div.appendChild(label);
                                        col.appendChild(div);
                                        row.appendChild(col);

                                    } else if (key === "Resources") {
                                        //Create numGPUs slider
                                        var col = document.createElement("div");
                                        col.setAttribute('class', 'col s4');

                                        var input1 = document.createElement("input");
                                        input1.setAttribute('type', 'range');
                                        input1.setAttribute('id', 'numGPUs');
                                        input1.setAttribute('name', 'numGPUs');
                                        input1.setAttribute('min', '1');
                                        input1.setAttribute('max', '8');
                                        input1.setAttribute('value', values[key]); //CHANGE VALUE HERE <-------------------
                                        input1.setAttribute('onchange', 'showNum(this.value); enableCheckAdmin();');

                                        var p = document.createElement("p");
                                        p.setAttribute('class', 'range-field');
                                        p.setAttribute('style', 'margin: 0px;');

                                        var div1 = document.createElement("div");
                                        div1.setAttribute('style', 'position: relative; margin-top: -26px;');

                                        var label = document.createElement("label");
                                        label.setAttribute('for', 'gpus');
                                        label.innerHTML = "Resources";

                                        var input2 = document.createElement("input");
                                        input2.setAttribute('disabled', '');
                                        input2.setAttribute('type', 'text');
                                        input2.setAttribute('name', 'gpus');
                                        input2.setAttribute('id', 'gpus');
                                        input2.setAttribute('value', values[key]); //CHANGE VALUE HERE <-------------------

                                        var div2 = document.createElement("div");
                                        div2.setAttribute('class', 'input-field');

                                        p.appendChild(input1);
                                        div1.appendChild(p);
                                        div2.appendChild(input2);
                                        div2.appendChild(label);
                                        div2.appendChild(div1);
                                        col.appendChild(div2);
                                        row.appendChild(col);

                                    } else if (key === "ResourceIDs") {
                                        continue;
                                    } else if (key === "Duration") {
                                        // <div class="col s6">
                                        //     <div class="input-field">
                                        //         <input required type="number" id="numHours" name="numHours" onchange="limitNumHours(); enableCheck()" min=1 max=72>
                                        //         <label for="numHours">Number of hours</label>
                                        //     </div>
                                        // </div>
                                        var col = document.createElement("div");
                                        col.setAttribute('class', 'col s4');

                                        var div = document.createElement("div");
                                        div.setAttribute('class', 'input-field');

                                        var input = document.createElement("input");
                                        input.setAttribute('type', 'number');
                                        input.setAttribute('id', 'numHours');
                                        input.setAttribute('name', 'numHours');
                                        input.setAttribute('onchange', 'limitNumHours(); enableCheckAdmin()');
                                        input.setAttribute('min', '1');
                                        input.setAttribute('max', '168');
                                        input.setAttribute('value', values[key]); //CHANGE VALUE HERE <-------------------


                                        var label = document.createElement("label");
                                        label.setAttribute('for', 'numHours');
                                        label.innerHTML = "Duration";

                                        div.appendChild(input);
                                        div.appendChild(label);
                                        col.appendChild(div);
                                        row.appendChild(col);

                                    } else if (key !== "editing" &&
                                            key !== "Affiliation" &&
                                            key !== "Status" &&
                                            key !== "Container" &&
                                            key !== "Approved_From" &&
                                            key !== "Approved_Duration" &&
                                            key !== "Num_Resources_Approved" &&
                                            key !== "Approver" &&
                                            !key.includes('col')) {

                                        var div = document.createElement("div");
                                        div.setAttribute('class', 'input-field');

                                        var input = document.createElement("input");
                                        var col = document.createElement("div");

                                        if (key === "Name" || key === "Task_ID") {
                                            input.setAttribute('disabled', '');
                                            col.setAttribute('class', 'col s6');
                                        } else {
                                            col.setAttribute('class', 'col s4');
                                        }

                                        input.type = "text";
                                        if (values[key]) {
                                            input.value = values[key].replace(/^\s+/g, ''); //remove white space on left of value
                                            input.id = key;
                                            input.name = key;

                                            var label = document.createElement("label");
                                            label.setAttribute('for', key);
                                            label.textContent = key;

                                            div.appendChild(input);
                                            div.appendChild(label);
                                            col.appendChild(div);
                                            row.appendChild(col);
                                        }
                                    }
                                }
                            }
                            form.appendChild(row);
                            M.updateTextFields();
                            //console.log("Clearing Schdeule of task ", values["Task_ID"]);
                            clearScheduleOfTask(values["Task_ID"]);
                            initTimePickerAdmin(date);
                            initDatePickerAdmin(date);
                            initModals();
                            initRanges();

                            var modal_instance = M.Modal.getInstance(modal_edit);
                            modal_instance.open();

                        }
                    }
                },
                "deleteRow": function (row) {
                    //console.log("reject");
                    currentRow = row;
                    var modal_instance = M.Modal.getInstance(modal_reject);
                    modal_instance.open();
                },
                "viewRow": function (row) {
                    //console.log("approve");
                    currentRow = row;
                    var values = currentRow.val();
                    if (values["Account"] === null) {
                        //call server account modal
                        serverAccountModal();
                    } else {
                        var modal_instance = M.Modal.getInstance(modal_approve);

                        //Request name
                        name = values["Name"];

                        //Request Affiliation
                        affiliation = values["Affiliation"];

                        //Date
                        date = values["Start"];

                        //Number of GPUs
                        numGPUs = values["Resources"];

                        //Duration
                        duration = values["Duration"];

                        var paragraph = document.getElementById("modal_approve_content_p");
                        paragraph.innerHTML = "Are you sure you want to <font color='green'>APPROVE</font> <b>" + name + "</b>'s (" + affiliation + ") request for <b>" + numGPUs + "</b> GPUs starting <b>" + date + "</b> for <b>" + duration + "</b> hours? This task will be put into the queue immediately."
                        modal_instance.open();
                    }
                }
            }
        });
        //console.log("Logging here look: ")
        //console.log(ft.use(FooTable.Editing).allowAdd);
    }, "json");
}

function initSortUIAdmin() {
    filter = $("input[name*='status']:checked").val();
    if (ft !== null) {
        if (filter === "under review") {
            ft.use(FooTable.Sorting).sort(0, "DESC");
        } else {
            ft.use(FooTable.Sorting).sort(0, "DESC");
        }
    }
    edit_buttons = document.getElementsByClassName("footable-edit");
    view_buttons = document.getElementsByClassName("footable-view");
    delete_buttons = document.getElementsByClassName("footable-delete");

    switch (filter) {
        case "none":
            //console.log("Filter none");
            break;

        case "under review":
            //console.log("Filter under review");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].removeAttribute("disabled");
                view_buttons[i].removeAttribute("disabled");
                delete_buttons[i].removeAttribute("disabled");
            }
            break;

        case "approved":
            //console.log("Filter approved");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].removeAttribute("disabled");
                view_buttons[i].setAttribute("disabled", "");
                delete_buttons[i].removeAttribute("disabled");
            }
            break;

        case "rejected":
            //console.log("Filter rejected");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].setAttribute("disabled", "");
                view_buttons[i].setAttribute("disabled", "");
                delete_buttons[i].setAttribute("disabled", "");
            }
            break;

        case "canceled":
            //console.log("Filter canceled");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].setAttribute("disabled", "");
                view_buttons[i].setAttribute("disabled", "");
                delete_buttons[i].setAttribute("disabled", "");
            }
            break;

        case "in progress":
            //console.log("Filter in progress");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].removeAttribute("disabled");
                view_buttons[i].setAttribute("disabled", "");
                delete_buttons[i].removeAttribute("disabled");
            }
            break;

        case "completed":
            //console.log("Filter completed");
            for (i = 0; i < view_buttons.length; i++) {
                edit_buttons[i].setAttribute("disabled", "");
                view_buttons[i].setAttribute("disabled", "");
                delete_buttons[i].setAttribute("disabled", "");
            }
            break;
    }
}

function serverAccountModal() {
    var options = ["1", "2", "3", "4", "5"]; // <--PUT ARRAY OF OPTIONS HERE 
    populateServerAccountSelect(options);
    var modal_instance = M.Modal.getInstance(modal_server_account);
    // M.updateTextFields();
    modal_instance.open();
}

// Reload the FooTable
function reloadFootableAdmin() {
    $.get("/getTableData", {"unfiltered": 1}, function (data) {
        ft.loadRows(JSON.parse(data.rows));
        setTimeout(initSortUIAdmin, 1000);
    }, "json");
}

//Save Changes inside modal-edit pressed
function assignAccount() {
    console.log("assignAccount");
    var values = currentRow.val();
    //get task_id from values

    //TaskID
    task_ID = values["Task_ID"];

    //Server Account
    server_account_ID = document.getElementById("serverAccountSelect").value;

    //Execute query through AJAX
    $.ajax({
        method: "POST",
        url: "/assignServerAccount",
        data: {
            "task_id": task_ID,
            "server_account_id": server_account_ID
        }
    }).done(function (response) {
        console.log("Sever Account assigned successfully.");
    }).fail(function (data) {
        console.error("Sever Account assign failed.");
    }).always(function () {
        reloadFootableAdmin();
    });
}
//Approve inside modal-approve pressed
function confirmApprove() {
    console.log("confirmApprove");
    showSpinner();
    var values = currentRow.val();
    //get task_id from values

    //TaskID
    task_ID = values["Task_ID"];

    //Date
    date = values["Start"];

    //Duration
    duration = values["Duration"];

    //Number of GPUs
    numGPUs = values["Resources"];

    //Execute query through AJAX
    $.ajax({
        method: "POST",
        url: "/approveRequest",
        data: {
            "user_id": user_ID,
            "approved_date": date,
            "approved_duration": duration,
            "task_id": task_ID,
            "approved_num_resources": numGPUs
        }
    }).done(function (data) {
        console.log("Request approved successfully.");
        reloadFootableAdmin();
        editSkedTape();
        hideSpinner();
    }).fail(function () {
        console.error("Request approval failed.");
        hideSpinner();
    });
}


//Save Changes inside modal_in_progress_edit pressed

function confirmEditTaskInProgress() {
    showSpinner();
    var values = currentRow.val();
    var startDate = new Date(Date.parse(values['Start']));
    console.log("confirmEditTaskInProgress");
    var form_data = {};
    form_data["task_id"] = values['Task_ID'];
    form_data["date"] = startDate.toLocaleDateString("en-US", {month: 'short', day: 'numeric', year: 'numeric'});
    form_data["time"] = startDate.getHours() + ":" + startDate.getMinutes();
    form_data["numgpus"] = values['Resources'];
    form_data["numhours"] = document.getElementById("numHours").value;
    // FIXME: resourceID calculation is incorrect
    // should be proper mapping to ids in DB
    form_data["resource_ids"] = values['ResourceIDs'].split(",").map(function (item) {
        return Number(item) + 1/* because DB ids starts from 1*/;
    });

    $.ajax({
        method: "POST",
        url: "/editRequest",
        data: form_data
    }).done(function (data) {
        console.log("Task edited successfully.");
        reloadFootableAdmin();
        reloadSkedTape();
        hideSpinner();
    }).fail(function (data) {
        console.error("Task edition failed");
        hideSpinner();

    });
}

//Save Changes inside modal-edit pressed
function confirmEdit() {
    showSpinner();
    console.log("confirmEdit");
    var values = currentRow.val();
    var inputs = $('#modal_edit_form').find('input');
    var task_id = $('#modal_edit_form').find('input#Task_ID');
    var form_data = {};
    for (var i in [...Array(inputs.length).keys()]) {
        if (inputs[i].id !== "Task_ID" && inputs[i].id !== "Name") {
            form_data[inputs[i].id.toLowerCase()] = inputs[i].value;
        }
    }

    gpusToUse = [];
    for (var i = 0; i < parseInt(document.getElementById("numGPUs").value); i++) {
        // FIXME: resourceID calculation is incorrect
        gpusToUse.push(parseInt(dates2gpu[dateIndex][i]) + 1);
    }

    form_data["resource_ids"] = gpusToUse;
    form_data["task_id"] = task_id[0].value;

    $.ajax({
        method: "POST",
        url: "/editRequest",
        data: form_data
    }).done(function (data) {
        console.log("Task edited successfully.");
        reloadFootableAdmin();
        reloadSkedTape();
        hideSpinner();
    }).fail(function (data) {
        console.error("Task edition failed");
        hideSpinner();

    });
}

//Reject inside modal-reject pressed
function confirmReject() {
    var values = currentRow.val();
    showSpinner();

    //TaskID
    task_ID = values["Task_ID"];

    $.ajax({
        method: "POST",
        url: "/rejectRequest",
        data: {
            "user_id": user_ID,
            "task_id": task_ID
        }
    }).done(function (data) {
        reloadFootableAdmin();
        reloadSkedTape();
        hideSpinner();
        console.log("Task rejected successfully.");
    }).fail(function (data) {
        hideSpinner();
        console.log("Task rejection failed.");
    });
}

function populateServerAccountSelect() {
    var select = document.getElementById("serverAccountSelect");

    while (select.hasChildNodes()) {
        select.removeChild(select.lastChild);
    }

    $.ajax({
        method: "GET",
        url: "getServerAccounts",
        data: {},
        type: "text/json"
    }).done(function (data) {
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                var opt = data[i];
                var el = document.createElement("option");
                el.textContent = opt.name;
                el.value = opt.id;
                select.appendChild(el);
            }

            var elems = document.querySelectorAll('select');
            var instances = M.FormSelect.init(elems, {
                dropdownOptions: {
                    container: document.body
                }
            });
            console.info("Server accounts retrieved successfully.");
        } else {
            console.error("No server accounts data");
        }
    });
}
