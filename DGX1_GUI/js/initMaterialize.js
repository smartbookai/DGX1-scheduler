function initTimePicker() {
    today = new Date();
    // console.log(availableTimes);
    $('.timepicker').timepicker({
        defaultTime: String(today.getHours()) + ":00",
        twelveHour: false,
        showClearBtn: true,
        enabledHours: availableTimes
    });
}

function initDatePicker() {
    min = new Date()
    max = new Date()
    max.setDate(max.getDate() + 30)
    $('.datepicker').datepicker({
        minDate: min,
        maxDate: max,
        yearRange: 0,
        showClearBtn: true,
        disableDayFn: function (day) {
            today = new Date();
            relativeCurrDate = Math.ceil((day - today) / 8.64e+7);
            console.log(relativeCurrDate);
            relativeHours = Array(24).fill().map((_, i) => i + relativeCurrDate * 24 - today.getHours());
            console.log(relativeHours);
            return !findCommonElement(availableHours, relativeHours);
        }
    });
}
function initTimePickerAdmin(defDate) {
    $('.timepicker').timepicker({
        defaultTime: String(defDate.getHours()) + ":00",
        twelveHour: false,
        showClearBtn: true,
        enabledHours: availableTimes
    });
}

function initDatePickerAdmin(defDate) {
    min = new Date()
    max = new Date()
    max.setDate(max.getDate() + 30)
    $('.datepicker').datepicker({
        defaultDate: defDate,
        setDefaultDate: true,
        minDate: min,
        maxDate: max,
        yearRange: 0,
        showClearBtn: true,
        disableDayFn: function (day) {
            today = new Date();
            relativeCurrDate = Math.ceil((day - today) / 8.64e+7);
            console.log(relativeCurrDate);
            relativeHours = Array(24).fill().map((_, i) => i + relativeCurrDate * 24 - today.getHours());
            console.log(relativeHours);
            return !findCommonElement(availableHours, relativeHours);
        }
    });
}

function initModals() {
    var elems = document.querySelectorAll('.modal');
    var instances = M.Modal.init(elems, {
        'dismissible': false,
        'opacity': 0
    });
}

function initRanges() {
    var array_of_dom_elements = document.querySelectorAll("input[type=range]");
    M.Range.init(array_of_dom_elements);
}

function showNum(numGPUs) {
    document.getElementById("gpus").value = numGPUs;
    M.updateTextFields();
}