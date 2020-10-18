$(document).ready(function () {
	initModals();
	initFootable();
	initFilterUI();
});

// Initialize the Modals
function initModals() {
	var elems = document.querySelectorAll('.modal');
	var instances = M.Modal.init(elems, {
		'opacity': 0
	});
}

//Cancel inside modal-reject pressed
function confirmCancel() {
	showSpinner();
	var values = currentRow.val();
	//get task_id from values

	$.ajax({
		method: "POST",
		url: "/cancelRequest",
		data: {
			"task_id": values["Task_ID"]
		}
	}).done(function (data) {
		reloadFootable();
		console.log("Task canceled successfully.");
		hideSpinner();
	}).fail(function (data) {
		console.error("Task cancelation failed.");
		hideSpinner();
	});
}

// Initialize the FooTable
function initFootable() {
	$.get("/getTableData", function (data) {
		//console.log("Type of data: " + typeof data)
		//console.log("Data: " + data);
		//console.log("Rows: " + data.rows);
		//console.log("Columns: " + data.columns);
		//console.log(JSON.parse(data.rows));
		//console.log(JSON.parse(data.columns));

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
					ft.$el.hide().after(ft.$loader)
				},
				"after.ft.filtering": function (e, ft) {
					initSortUI();
				},
				"after.ft.sorting": function (e, ft) {
					ft.$loader.remove();
					ft.$el.show();
					//enable everything called status
					filter_buttons = document.getElementsByName("status");
					for (i = 0; i < filter_buttons.length; i++) {
						filter_buttons[i].removeAttribute("disabled");
					}
				}
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
				"allowEdit": false,
				"allowView": false,
				"allowDelete": true,
				"deleteRow": function (row) {
					//console.log("cancel");
					currentRow = row;
					var modal_instance = M.Modal.getInstance(modal_cancel);
					modal_instance.open();
				}
			}
		});
	}, "json");
}

function initSortUI() {
	filter = $("input[name*='status']:checked").val();
	if (ft !== null) {
		if (filter === "under review") {
			ft.use(FooTable.Sorting).sort(0, "DESC");
		} else {
			ft.use(FooTable.Sorting).sort(0, "ASC");
		}
	}
	delete_buttons = document.getElementsByClassName("footable-delete");

	switch (filter) {
		case "none":
			//console.log("Filter none");
			break;

		case "under review":
			//console.log("Filter under review");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].removeAttribute("disabled");
			}
			break;

		case "approved":
			//console.log("Filter approved");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].removeAttribute("disabled");
			}
			break;

		case "rejected":
			//console.log("Filter rejected");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].setAttribute("disabled", "");
			}
			break;

		case "canceled":
			//console.log("Filter canceled");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].setAttribute("disabled", "");
			}
			break;

		case "in progress":
			//console.log("Filter canceled");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].setAttribute("disabled", "");
			}
			break;
			
		case "completed":
			//console.log("Filter canceled");
			for (i = 0; i < delete_buttons.length; i++) {
				delete_buttons[i].setAttribute("disabled", "");
			}
			break;

	}
}

// Reload the FooTable
function reloadFootable() {
	$.get("/getTableData", function (data) {
		// //console.log("Type of data: " + typeof data)
		// //console.log("Data: " + data);
		// //console.log(JSON.parse(data.rows));
		ft.loadRows(JSON.parse(data.rows));
	}, "json");
}
