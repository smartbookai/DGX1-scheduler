var m;
var mCleared;
var availableHours;
var availableTimes;
var dates2gpu;
var dateIndex;
var timeIndex;

function getSchedule(range) {
	//Range is in hours
	$.ajax({
		url: "/getScheduleData",
		type: "GET"
	}).done(function (response) {
		//response = JSON.parse(response);
		//create matrix
		m = nj.zeros([8, range]);
		rangeStart = new Date();
		rangeStart.setMinutes(0, 0, 0);

		rangeEnd = new Date();
		rangeEnd.setTime(rangeStart.getTime());
		rangeEnd.setHours(rangeEnd.getHours() + range);
		for (x in response) {
			row = response[x];

			var rowIndex = parseInt(row["gpu"]);
			var rowStart = new Date();
			rowStart.setTime(Date.parse(row["start"]));
			var rowEnd = new Date();
			rowEnd.setTime(Date.parse(row["end"]));

			if (rowEnd >= rangeStart) {
				if (rowStart < rangeStart) {
					//from rangeStart to rowEnd
					startIndex = 0;
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);
				} else {
					//from rowStart to rowEnd
					startIndex = ((rowStart - rangeStart) / 3.6e+6);
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);

				}

				if (endIndex > range) {
					m.pick(rowIndex).slice(startIndex).assign(1, false);
				} else {
					m.pick(rowIndex).slice([startIndex, endIndex]).assign(1, false);
				}
			}
		}

	});
}

function getBestDates(m, duration, numGPUs) {
	dates2gpu = getAvailableDates(m, duration, numGPUs);

	// Loop over all days
	for (var date in dates2gpu) {
		gpuScore = [];
		for (var gpu in dates2gpu[date]) {
			// //console.log(m.pick(dates2gpu[date][gpu]).toString());
			// //console.log("DATE: ", Object.keys(dates2gpu)[date]);
			// //console.log(dates2gpu[date].toString());
			// //console.log(m.pick(dates2gpu[date][gpu]).slice(Object.keys(dates2gpu)[date]).toString());
			index = m.pick(dates2gpu[date][gpu]).tolist().indexOf(1);
			if (index !== -1) {
				index = index - date + 1;
				gpuAvailability = m.pick(parseInt(dates2gpu[date][gpu])).slice([parseInt(Object.keys(dates2gpu)[date]), index]);
			} else {
				index = null;
				gpuAvailability = m.pick(parseInt(dates2gpu[date][gpu]));
			}
			// //console.log(gpu);
			// //console.log(date);
			gpuScore.push(gpuAvailability.shape[0]);
			// //console.log("GPU Availability: ", gpuAvailability.toString());

		}
		// //console.log("Score: ", gpuScore);
		rankings = sortWithIndices(gpuScore);
		// //console.log("Rankings: ", rankings);
		indecies = rankings.slice(0, numGPUs);
		bestGPUs = [];
		for (index in indecies) {
			bestGPUs.push(dates2gpu[date][indecies[index]]);
		}

		// //console.log(dates2gpu);
		// //console.log(Object.keys(dates2gpu  ));
		console.log("Best Hour: ", date);
		// //console.log("Best GPUS: ", bestGPUs);
		return date
	}
}

function sortWithIndices(toSort) {
	for (var i = 0; i < toSort.length; i++) {
		toSort[i] = [toSort[i], i];
	}
	toSort.sort(function (left, right) {
		return left[0] < right[0] ? -1 : 1;
	});
	toSort.sortIndices = [];
	for (var j = 0; j < toSort.length; j++) {
		toSort.sortIndices.push(toSort[j][1]);
		toSort[j] = toSort[j][0];
	}
	return toSort.sortIndices;
}

function getAvailableTimes(hours, date) {
	hour0 = date;
	hour23 = date + 24;
	result = []
	for (i = 0; i < 24; i++) {
		if (hours.indexOf(hour0 + i) !== -1) {
			result.push(i);
		}
	}
	// //console.log(result);
	return result
}

function getAvailableDates(m, duration, numGPUs) {
	availableWindows = getAvailableWindows(m, duration, numGPUs);
	// //console.log("Windows");
	// //console.log(availableWindows.toString());

	availableWindows_T = availableWindows.T;
	// //console.log("Days");
	// //console.log(availableWindows_T.toString());

	// Check how many gpus available every hour
	res = Array();
	for (var i = 0; i < availableWindows_T.shape[0]; i++) {
		if (availableWindows_T.pick(i).sum() >= numGPUs) {
			res.push(1);
		} else {
			res.push(0);
		}
	}

	dates2gpu = {};
	// Loop over all dates
	for (var i = 0; i < res.length; i++) {
		// If date is available
		if (res[i] === 1) {
			gpus = [];
			// Loop over all gpus in that date
			row = availableWindows_T.pick(i);
			for (var k = 0; k < row.shape; k++) {
				if (row.get(k) === 1) {
					gpus.push(k);
				}
			}
			dates2gpu[i] = gpus
		}
	}
	return dates2gpu;
}

function getAvailableWindows(m, duration, numGPUs) {
	// //console.log("Matrix: ");
	// //console.log(m);
	// //console.log("Duration: ", duration);
	// //console.log("Numer of GPUs: ", numGPUs);

	// //console.log("Shape: ", m.shape);
	availableWindows = Array();
	seq = nj.zeros(duration);
	// Loop over resources
	for (var i = 0; i < m.shape[0]; i++) {
		row = m.pick(i);
		result = search_sequence_numjs(row, seq);
		if (result.shape > 0) {
			if (availableWindows.length > 0) {
				availableWindows.push(result);
			} else {
				availableWindows = Array(result);
			}
		}
	}

	// res = search_sequence_numjs(m, nj.zeros(duration));
	availableWindows = nj.stack(availableWindows);
	return availableWindows;
}

function search_sequence_numjs(arr, seq) {
	// Store sizes of input array and sequnce
	Na = arr.shape[0];
	Nseq = seq.shape[0];
	var res = Array();
	// //console.log("arr: ", arr.toString());
	// //console.log("seq: ", seq.toString());

	for (var j = 0; j < Na - Nseq + 1; j++) {
		win = arr.slice([j, j + Nseq]);
		seqC = seq.clone();
		// //console.log("win: ", win.toString());
		// //console.log("seqC: ", seqC.toString());
		if (seqC.equal(win)) {
			res.push(1);
		} else {
			res.push(0);
		}
	}
	return nj.array(res);
}

function findCommonElement(array1, array2) {

	// Loop for array1
	for (let i = 0; i < array1.length; i++) {

		// Loop for array2
		for (let j = 0; j < array2.length; j++) {

			// Compare the element of each and
			// every element from both of the
			// arrays
			if (array1[i] === array2[j]) {

				// Return if common element found
				return true;
			}
		}
	}

	// Return if no common element exist
	return false;
}

function enableCheck() {
	// //console.log(m);

	//if numGPUs and numHours are both selected
	if (document.getElementById("numGPUs").value !== "" && document.getElementById("numHours").value !== "") {
		//enable date
		document.getElementById("date").disabled = false;
		numHours = parseInt(document.getElementById("numHours").value);
		numGPUs = parseInt(document.getElementById("numGPUs").value)
		dates2gpu = getAvailableDates(m, numHours, numGPUs);
		availableHours = Object.keys(dates2gpu).map(Number);

		//If both date and time are not selected, suggest best date
		if (document.getElementById("date").value === "" && document.getElementById("time").value === "") {
			bestHour = getBestDates(m, numHours, numGPUs);
			console.log(bestHour);
			recommendation = new Date();
			console.log(recommendation)
			console.log("+ " + (bestHour + 1) * 60 * 60 * 1000)
			recommendation.setMinutes(0);
			recommendation.setTime(recommendation.getTime() + ((parseInt(bestHour) + 1) * 60 * 60 * 1000))
			console.log(recommendation)
			bestTime = recommendation.getHours() + ":00";
			s = recommendation.toString().split(" ");
			bestDate = s[1] + " " + s[2] + ", " + s[3];

			// console.log("Best Date: " +  bestDate);
			// console.log("Best Time: " + bestTime);

			document.getElementById("date").value = bestDate;
			document.getElementById("time").value = bestTime;



		}

		// //console.log("Reloaded available Hours using " + numHours + " hours and " + numGPUs + " GPUs.");
		//if date is selected
		if (document.getElementById("date").value !== "") {
			document.getElementById("time").disabled = false;
			dateSelected = new Date();
			today = new Date();
			today.setMinutes(0, 0, 0);

			dateSelected.setTime(Date.parse(document.getElementById("date").value));
			dateSelected.setHours(0, 0, 0, 0);
			dateIndex = (dateSelected - today) / 3.6e+6;
			//console.log("dateIndex: ", dateIndex);
			//console.log("parseInt(dateIndex/24): ", parseInt(dateIndex / 24));
			timeIndex = 0;
			availableTimes = getAvailableTimes(availableHours, dateIndex);
			initTimePicker();
			//if date and time are selected
			if (document.getElementById("time").value !== "") {
				// document.getElementById("date").disabled = true;
				today = new Date();
				today.setMinutes(0, 0, 0);

				dateSelected.setTime(Date.parse(document.getElementById("date").value));
				dateSelected.setHours(parseInt(document.getElementById("time").value), 0, 0, 0);
				dateIndex = (dateSelected - today) / 3.6e+6;

				// timeIndex = parseInt(document.getElementById("time").value);
				// timeIndex = (timeSelected - today.getHours());
				timeIndex = 0;

				//show candidate request in skedtape
				addCandidateTasks();
				editSkedTape();

				//if container is selected
				if (document.getElementById("container-selector").value !== "") {
					//enable submit button
					document.getElementById("submit").disabled = false;
				}
			} else { //Date or time is null
				//disable submit button
				document.getElementById("submit").disabled = true;
				candidate_task = Array();
				editSkedTape();
			}

			gpus = dates2gpu[dateIndex];
			if (gpus) {
				if (document.getElementById("numGPUs").value > gpus.length) {
					document.getElementById("numGPUs").value = gpus.length;
				}
				document.getElementById("numGPUs").max = gpus.length;
			}
		} else {
			document.getElementById("time").disabled = true;
			document.getElementById("time").value = "";
			document.getElementById("numGPUs").max = 8;
			M.updateTextFields();
		}


	} else { //numGPU or numHours is null
		//disable date and time
		document.getElementById("date").disabled = true;
		document.getElementById("time").disabled = true;

		document.getElementById("date").value = ""
		document.getElementById("time").value = ""

		//disable submit button
		document.getElementById("submit").disabled = true;
		candidate_task = Array();
		editSkedTape();
	}
	M.updateTextFields();
}

function enableCheckAdmin() {
	// //console.log(m);
	//console.log("Enabling..");
	//if numGPUs and numHours are both selected
	if (document.getElementById("numGPUs").value !== "" && document.getElementById("numHours").value !== "") {
		//enable date
		document.getElementById("date").disabled = false;
		numHours = parseInt(document.getElementById("numHours").value);
		numGPUs = parseInt(document.getElementById("numGPUs").value);
		dates2gpu = getAvailableDates(mCleared, numHours, numGPUs);
		availableHours = Object.keys(dates2gpu).map(Number);
		//console.log("Reloaded available Hours using " + numHours + " hours and " + numGPUs + " GPUs.");
		//if date is selected
		if (document.getElementById("date").value !== "") {
			document.getElementById("time").disabled = false;
			dateSelected = new Date();
			today = new Date();
			today.setMinutes(0, 0, 0);

			dateSelected.setTime(Date.parse(document.getElementById("date").value));
			dateSelected.setHours(0, 0, 0, 0);
			dateIndex = (dateSelected - today) / 3.6e+6;
			//console.log("dateIndex: ", dateIndex);
			//console.log("parseInt(dateIndex/24): ", parseInt(dateIndex / 24));
			timeIndex = 0;
			availableTimes = getAvailableTimes(availableHours, dateIndex);
			initTimePicker();
			//if date and time are selected
			if (document.getElementById("time").value !== "") {
				// document.getElementById("date").disabled = true;
				today = new Date();
				today.setMinutes(0, 0, 0);

				dateSelected.setTime(Date.parse(document.getElementById("date").value));
				dateSelected.setHours(parseInt(document.getElementById("time").value), 0, 0, 0);
				dateIndex = (dateSelected - today) / 3.6e+6;

				// timeIndex = parseInt(document.getElementById("time").value);
				// timeIndex = (timeSelected - today.getHours());
				timeIndex = 0;

				//show candidate request in skedtape
				addCandidateTasksAdmin();
				editSkedTape();

			} else { //Date or time is null
				//disable save button
				document.getElementById("save").disabled = true;
				candidate_task = Array();
				editSkedTape();
			}

			gpus = dates2gpu[dateIndex];
			if (gpus) {
				if (document.getElementById("numGPUs").value > gpus.length) {
					document.getElementById("numGPUs").value = gpus.length;
				}
				document.getElementById("numGPUs").max = gpus.length;
			}
		} else {
			document.getElementById("time").disabled = true;
			document.getElementById("time").value = "";
			document.getElementById("numGPUs").max = 8;
			M.updateTextFields();
		}


	} else { //numGPU or numHours is null
		//disable date and time
		document.getElementById("date").disabled = true;
		document.getElementById("time").disabled = true;

		document.getElementById("date").value = "";
		document.getElementById("time").value = "";

		//disable save button
		document.getElementById("save").disabled = true;
		candidate_task = Array();
		editSkedTape();
	}
	M.updateTextFields();
}

function validateInProgressEdit(resourceIDs, start_date, task_duration) {
	var today = new Date();
	today.setMinutes(0, 0, 0);
	var time_in_progress = Math.round((today - Date.parse(start_date)) / 3.6e+6);
	var difference = document.getElementById("numHours").value - task_duration;
	var col = task_duration - time_in_progress;
	var f = true;
	var max_duration = 0;
	while (f && (max_duration <= 168)){
		resourceIDs.forEach(function (gpuID) {
			if (m.get(gpuID, col + max_duration) !== 0) {
				f = false;
			}
		});
		if (f) {
			max_duration += 1; 
		}
	}
	f = true;
	resourceIDs.forEach(function (gpuID) {
		for (var i = 0; i < difference; i++) {
			if (m.get(gpuID, col + i) !== 0) {
				f = false;
				break;
			}
		}
	});
	if (f) {
		changeInProgressTasksAdmin(resourceIDs, start_date, task_duration + difference);
		editSkedTape();
		return true;
	} else {
                changeInProgressTasksAdmin(resourceIDs, start_date, task_duration + max_duration);
		document.getElementById("numHours").value = task_duration + max_duration;
                editSkedTape();
		return false;
                
	}
}

function changeInProgressTasksAdmin(resourceIDs, startDate, taskDuration) {
	candidate_task = Array();
	var options = {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: 'numeric',
		minute: 'numeric'
	};

	var startDate_ = new Date(Date.parse(startDate));

	var endDate = new Date(startDate_.getTime());
	endDate.setTime(Date.parse(startDate) + (parseInt(taskDuration) * 60 * 60 * 1000));
	resourceIDs.forEach(function (gpuID) {

		var tmp = {
			"name": "Moved Task ".concat(gpuID + 1),
			"location": String(gpuID),
			"start": startDate_.toLocaleDateString("en-US", options),
			"end": endDate.toLocaleDateString("en-US", options),
			"id": "candidate"
		};
		candidate_task.push(tmp);

	});
}

function clearScheduleOfTaskInProgress(task_id) {
	mCleared = m.clone();
	//Range is in hours
	$.ajax({
		url: "/getTaskScheduleData",
		type: "GET",
		async: false,
		data: {
			"task_id": task_id
		}
	}).done(function (response) {
		//console.log(response);
		//response = JSON.parse(response);
		for (x in response) {
			row = response[x];

			var rowIndex = parseInt(row["gpu"]);
			var rowStart = new Date();
			rowStart.setTime(Date.parse(row["start"]));
			var rowEnd = new Date();
			rowEnd.setTime(Date.parse(row["end"]));
			range = m.shape[1];
			if (rowEnd >= rangeStart) {
				if (rowStart < rangeStart) {
					//from rangeStart to rowEnd
					startIndex = 0;
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);
				} else {
					//from rowStart to rowEnd
					startIndex = ((rowStart - rangeStart) / 3.6e+6);
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);

				}

				if (endIndex > range) {
					mCleared.pick(rowIndex).slice(startIndex).assign(0, false);
				} else {
					mCleared.pick(rowIndex).slice([startIndex, endIndex]).assign(0, false);
				}
			}
		}
		//console.log("I'm done clearing");
	});
}


function clearScheduleOfTask(task_id) {
	mCleared = m.clone();
	//Range is in hours
	$.ajax({
		url: "/getTaskScheduleData",
		type: "GET",
		async: false,
		data: {
			"task_id": task_id
		}
	}).done(function (response) {
		//console.log(response);
		//response = JSON.parse(response);
		for (x in response) {
			row = response[x];

			var rowIndex = parseInt(row["gpu"]);
			var rowStart = new Date();
			rowStart.setTime(Date.parse(row["start"]));
			var rowEnd = new Date();
			rowEnd.setTime(Date.parse(row["end"]));
			range = m.shape[1];
			if (rowEnd >= rangeStart) {
				if (rowStart < rangeStart) {
					//from rangeStart to rowEnd
					startIndex = 0;
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);
				} else {
					//from rowStart to rowEnd
					startIndex = ((rowStart - rangeStart) / 3.6e+6);
					endIndex = ((rowEnd - rangeStart) / 3.6e+6);

				}

				if (endIndex > range) {
					mCleared.pick(rowIndex).slice(startIndex).assign(0, false);
				} else {
					mCleared.pick(rowIndex).slice([startIndex, endIndex]).assign(0, false);
				}
			}
		}
		//console.log("I'm done clearing");
		enableCheckAdmin();
	});
}

function addCandidateTasks() {
	candidate_task = Array();
	var options = {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: 'numeric',
		minute: 'numeric'
	};
	for (i = 0; i < document.getElementById("numGPUs").value; i++) {
		if (dateIndex in dates2gpu) {
			document.getElementById("time").classList.remove("invalid");
			var startDate = new Date(document.getElementById("date").value + " " + document.getElementById("time").value);
			var endDate = new Date();
			var today = new Date();
			$("div.sked-tape__time-frame").scrollLeft(4800 * ((startDate - today) / 8.64e+7) / 30);
			endDate.setTime(startDate.getTime() + (parseInt(document.getElementById("numHours").value) * 60 * 60 * 1000));
			endDate.setMinutes(endDate.getMinutes() - 1);
			var tmp = {
				"name": "Requested Task ".concat(i + 1),
				"location": String(dates2gpu[dateIndex][i]),
				"start": startDate.toLocaleDateString("en-US", options),
				"end": endDate.toLocaleDateString("en-US", options)
			};
			candidate_task.push(tmp);
		} else {
			//console.log("Error:");
			//console.log("dateIndex: ", dateIndex);
			//console.log("dates2gpu: ", dates2gpu);
			//console.log("i: ", i);
			document.getElementById("time").classList.add("invalid");
			document.getElementById("time").value = "";
			// document.getElementById("date").value = "";
			enableCheck();
		}
	}
}

function addCandidateTasksAdmin() {
	candidate_task = Array();
	var options = {
		month: 'short',
		day: 'numeric',
		year: 'numeric',
		hour: 'numeric',
		minute: 'numeric'
	};
	for (i = 0; i < document.getElementById("numGPUs").value; i++) {
		if (dateIndex in dates2gpu) {
			document.getElementById("time").classList.remove("invalid");
			var startDate = new Date(document.getElementById("date").value + " " + document.getElementById("time").value);
			var endDate = new Date();
			var today = new Date();
			$("div.sked-tape__time-frame").scrollLeft(4800 * ((startDate - today) / 8.64e+7) / 30);
			endDate.setTime(startDate.getTime() + (parseInt(document.getElementById("numHours").value) * 60 * 60 * 1000));
			endDate.setMinutes(endDate.getMinutes() - 1);
			var tmp = {
				"name": "Moved Task ".concat(i + 1),
				"location": String(dates2gpu[dateIndex][i]),
				"start": startDate.toLocaleDateString("en-US", options),
				"end": endDate.toLocaleDateString("en-US", options),
				"id": "candidate"
			};
			candidate_task.push(tmp);
		} else {
			//console.log("Error:");
			//console.log("dateIndex: ", dateIndex);
			//console.log("dates2gpu: ", dates2gpu);
			//console.log("i: ", i);
			document.getElementById("time").classList.add("invalid");
			document.getElementById("time").value = "";
			// document.getElementById("date").value = "";
			enableCheckAdmin();
		}
	}
}

function limitNumHours() {
	numHoursField = document.getElementById("numHours");
	if (parseInt(numHoursField.value) > parseInt(numHoursField.max)) {
		numHoursField.value = numHoursField.max;
	}
}

function mClearedReset() {
	//console.log("Resetting clear..")
	mCleared = m.clone();
	candidate_task = [];
	reloadSkedTape();
}