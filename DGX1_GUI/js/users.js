$(document).ready(function () {
	initModals();
	initFootable();

	$("#add_server_account_btn").click(function () {
		$("#server_account").val("");
		M.Modal.getInstance(document.getElementById("modal_add_server_account")).open();
	});

	$("#server_account").bind("propertychange change click keyup input paste", function(event) {
		//validata value
		if (!/^[a-z_][a-z0-9_-]*$/.test(this.value)) {
			$("#server_account").addClass("invalid");
		} else {
			$("#server_account").removeClass("invalid");
		}
	});
});

// Initialize the Modals
function initModals() {
	var elems = document.querySelectorAll('.modal');
	var instances = M.Modal.init(elems, {
		'opacity': 0
	});
}

function confirmDelUser() {
	var values = currentRow.val();

	$.ajax({
		method: "POST",
		url: "/deleteUser",
		data: {
			"user_id": values['ID']
		}
	}).done(function (data) {
		reloadFootable();
		console.log("User deleted successfully.");
	}).fail(function (data) {
		console.error("User deletion failed.");
	});
}

function confirmAddServerAccount() {
	if (!/^[a-z_][a-z0-9_-]*$/.test($("#server_account").val())) {
		$("#server_account").addClass("invalid");
		return;
	}

	$.ajax({
		method: "POST",
		url: "/addServerAccount",
		data: {
			"server_account": $("#server_account").val()
		}
	}).done(function (data) {
		reloadFootable();
		M.Modal.getInstance(document.getElementById("modal_add_server_account")).close();
		console.log("Server account added successfully.");
	}).fail(function (data) {
		console.error("Server account adding failed.");
	});
}

// Initialize the FooTable
function initFootable() {
	$.get("/getUsersData", function (data) {

		// Table Initialization
		ft = FooTable.init('.table', {
			"columns": JSON.parse(data.columns),
			"rows": JSON.parse(data.rows),
			"on": {
				"before.ft.filtering": function (e, ft) {
				},
				"after.ft.filtering": function (e, ft) {
				},
				"after.ft.sorting": function (e, ft) {
				}
			},
			"filtering": {
				"enabled": true,
				"filters": [{
						"name": "Name",
						"columns": ["Name"]
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
				"allowEdit": true,
				"allowView": false,
				"allowDelete": true,
				"editRow": function (row) {
					currentRow = row;
					var values = row.val();


					var form = document.getElementById("modal_edit_form");

					//Clear form in modal
					while (form.hasChildNodes()) {
						form.removeChild(form.lastChild);
					}

					//Generate Form in Modal
					var row = document.createElement("div");
					row.setAttribute('class', 'row');

					for (var key in values) {
						//skip irrelevant fields
						var col = document.createElement("div");
						col.setAttribute('class', 'col s4');
						if (key === "editing") {
							continue;
						}

						console.log("modal_edit_form: " + key);
						if (key === "Affiliation") {

							$.ajax({
								method: "GET",
								url: "/getAffiliations",
								data: {},
								type: "text/json",
								async: false
							}).done(function (data) {
								if (data.length > 0) {
									var select_el = document.createElement("select");
									select_el.id = key;
									select_el.name = key;
									for (var val in data) {
										var opt = document.createElement("option");
										opt.value = data[val]["id"];
										opt.textContent = data[val]["name"];
										select_el.appendChild(opt);

										if (values[key] === data[val]["name"]) {
											select_el.value = data[val]["id"];
										}
									}

									var div = document.createElement("div");
									div.setAttribute('class', 'input-field');


									var label = document.createElement("label");
									label.setAttribute('for', key);
									label.textContent = key;
									div.appendChild(select_el);
									div.appendChild(label);
									col.appendChild(div);
									row.appendChild(col);

								} else {
									console.error("No affiliation data");
								}
							});
						} else if (key === "Account") {
							$.ajax({
								method: "GET",
								url: "/getServerAccounts",
								data: {},
								type: "text/json",
								async: false
							}).done(function (data) {
								if (data.length > 0) {
									var select_el = document.createElement("select");
									select_el.id = key;
									select_el.name = key;
									var opt = document.createElement("option")
									opt.textContent = "Select server account";
									opt.disabled = true;
									opt.selected = true;
									select_el.appendChild(opt);

									for (var val in data) {
										var opt = document.createElement("option")
										opt.value = data[val]["id"];
										opt.textContent = data[val]["name"];
										select_el.appendChild(opt);

										if (values[key] === data[val]["name"]) {
											select_el.value = data[val]["id"];
										}
									}


									var div = document.createElement("div");
									div.setAttribute('class', 'input-field');

									var label = document.createElement("label");
									label.setAttribute('for', key);
									label.textContent = key;
									div.appendChild(select_el);
									div.appendChild(label);
									col.appendChild(div)
									row.appendChild(col);
								} else {
									console.error("No affiliation data");
								}
							});
						} else if (key === "isAdmin") {
							var input = document.createElement("input");

							input.id = key;
							input.name = key;
							input.type = "checkbox";
							if (values[key] === 1) {
								input.checked = true;
							}
							var span = document.createElement("span");
							span.textContent = key;


							var label = document.createElement("label");
							label.setAttribute('for', key);
							label.appendChild(input);
							label.appendChild(span);
							col.appendChild(label);
							col.setAttribute('class', 'col s4 valign');
							row.appendChild(col);

						} else {

							//TODO: fix issue with null in values

							if (values[key]) {

								var input = document.createElement("input");
								input.id = key;
								input.name = key;
								input.disabled = true;

								var div = document.createElement("div");
								div.setAttribute('class', 'input-field');
								var label = document.createElement("label");
								label.setAttribute('for', key);


								input.type = "text";
								input.value = values[key].replace(/^\s+/g, ''); //remove white space on left of value
								label.textContent = key;
								div.appendChild(input);
								div.appendChild(label);

								col.appendChild(div);
								row.appendChild(col);
							}
						}
					}
					form.appendChild(row);

					var instances = M.FormSelect.init(
							form.querySelectorAll('select'),
							{ dropdownOptions: { container: '#modal_edit'}}
					);
//					$("#Affiliation").formSelect();
//					$("#Account").formSelect({ dropdownOptions: { container: '#modal_edit'}});

					M.updateTextFields();
					M.Modal.getInstance(document.getElementById("modal_edit")).open();
				},
				"deleteRow": function (row) {
					currentRow = row;
					M.Modal.getInstance(document.getElementById("modal_delete")).open();
				}
			}
		});
	}, "json");
}

// Reload the FooTable
function reloadFootable() {
	$.get("/getUsersData", function (data) {
		ft.loadRows(JSON.parse(data.rows));
	}, "json");
}

function confirmEdit() {
	console.log("confirmEdit");
	var values = currentRow.val();
	var form_values = $('#modal_edit_form').serializeArray();
	var form_data = {};
	form_values.forEach(function (val) {
		form_data[val.name] = val.value;
	});

	form_data["user_id"] = values["ID"];

	$.ajax({
		method: "POST",
		url: "/editUser",
		data: form_data
	}).done(function (data) {
		console.log("Task edited successfully.");
		reloadFootable();
	}).fail(function (data) {
		console.error("Task edition failed");
	});
}
