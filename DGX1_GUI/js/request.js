$(document).ready(function () {
	schedRange = 31 * 24;
	getSchedule(schedRange);
	initSkedTape();
	initModals();
	initDatePicker();
	initTimePicker();
	$("#container-selector").formSelect();
});

document.getElementById("numHours").addEventListener("keypress", function (evt) {
	if (isNaN(parseInt(evt.key))) {
		evt.preventDefault();
	}
});

$("#request_form").submit(function (event) {
	showSpinner();
	document.getElementById("submit").disabled = true;
	event.preventDefault(); //prevent default action 
	var form_data = $(this).serializeArray().reduce(function(obj, item){obj[item.name] = item.value;return obj;}, {});
	oldM = window.m.clone();
	getSchedule(schedRange);
	setTimeout(() => {

		if (nj.equal(oldM, window.m)) {
			
			gpusToUse = [];
			for (var i = 0; i < parseInt(document.getElementById("numGPUs").value); i++) {
				gpusToUse.push(parseInt(dates2gpu[dateIndex][i]) + 1);
			}
			
			form_data["resource_ids"] = gpusToUse;
			$.ajax({
				method: "POST",
				url: "/createRequest",
				data: form_data
			}).done(function (data) {
				console.log("Task created successfully.");
				modal_instance = M.Modal.getInstance(modal_submitted);
				hideSpinner();
				document.getElementById("submit").disabled = false;
				modal_instance.open();
			}).fail(function(data) {
				console.log("Task creation failed. [ " + data["error"] +"]");
				modal_instance = M.Modal.getInstance(modal_failed);
				hideSpinner();
				document.getElementById("submit").disabled = false;
				modal_instance.open();
			});
		} else {
			console.log("Database not up-to-date, please refresh page");
			modal_instance = M.Modal.getInstance(modal_refresh);
			hideSpinner();
			document.getElementById("submit").disabled = false;
			modal_instance.open();
		}

	}, 300);

});

function changeDesc(value) {
	document.getElementById("description").innerHTML = JSON.parse(value)["description"];
}
