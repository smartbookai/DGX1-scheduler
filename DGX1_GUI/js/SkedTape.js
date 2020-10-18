var defaultScroll;

var GPUs = [{
		id: '0',
		name: 'GPU 0'
	},
	{
		id: '1',
		name: 'GPU 1'
	},
	{
		id: '2',
		name: 'GPU 2'
	},
	{
		id: '3',
		name: 'GPU 3'
	},
	{
		id: '4',
		name: 'GPU 4'
	},
	{
		id: '5',
		name: 'GPU 5'
	},
	{
		id: '6',
		name: 'GPU 6'
	},
	{
		id: '7',
		name: 'GPU 7'
	}
];
var candidate_task = Array();
var tasks;

function initSkedTape() {
	var beginWindow = new Date();
	beginWindow.setDate(beginWindow.getDate() - 1);

	var endWindow = new Date();
	endWindow.setDate(endWindow.getDate() + 30);
	$.get("/getSkedTapeData", function (data) {
		//console.log("SkedTape Data:", data);
		tasks = data;
		if (Array.isArray(candidate_task) && candidate_task.length) {
			tasks = tasks.concat(candidate_task);
		}
		$('#skedtape').skedTape({
			caption: 'GPUs',
			tzOffset: -240,
			start: beginWindow,
			end: endWindow,
			// showEventTime: true,
			// showEventDuration: true,
			scrollWithYWheel: false,
			locations: GPUs,
			events: tasks,
			zoom: 2,
			maxZoom: 2,
			snapToMins: 24 * 60,
			editMode: false,
			timeIndicatorSerifs: false,
			showIntermission: true,
		});
	}, "json");
}

function editSkedTape() {
	if (Array.isArray(candidate_task) && candidate_task.length) {
		displayed_tasks = tasks.concat(candidate_task);
	} else {
		displayed_tasks = tasks;
	}

	defaultScroll = $("div.sked-tape__time-frame").scrollLeft();
	$('#skedtape').skedTape('removeAllEvents');
	$('#skedtape').skedTape('addEvents', displayed_tasks.slice());
	$("div.sked-tape__time-frame").scrollLeft(defaultScroll);
}

function reloadSkedTape() {
	$.get("/getSkedTapeData", function(data) {
		//console.log("SkedTape Data:", data);
		tasks = data;
		if (Array.isArray(candidate_task) && candidate_task.length) {
			tasks = tasks.concat(candidate_task);
		}
	}, "json").done(function(){
		editSkedTape();
	});
}
