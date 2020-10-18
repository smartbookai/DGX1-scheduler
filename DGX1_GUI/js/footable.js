//UI Filter
var ft = FooTable.get('.footable');
var filter;
var ft;

function initFilterUI() {
	$("input[name*='status']").on('change', function () {
		//console.log("Im being filtered");
		var filtering = ft.use(FooTable.Filtering), // get the filtering component for the table
				filter = $(this).val(); // get the value to filter by
		if (filter === 'none') { // if the value is "none" remove the filter
			filtering.removeFilter('Status');
		} else { // otherwise add/update the filter.
			filtering.addFilter('Status', filter, ['Status']);
		}
		filtering.filter();
	});
}
