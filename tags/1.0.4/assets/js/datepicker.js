jQuery(document).ready(function($) {
    let today = new Date();
    let firstAvailable = new Date();
    firstAvailable.setDate(today.getDate() + 2);
    let secondAvailable = new Date();
    secondAvailable.setDate(today.getDate() + 3);

    let availableDates = [
        $.datepicker.formatDate("yy-mm-dd", firstAvailable),
        $.datepicker.formatDate("yy-mm-dd", secondAvailable)
    ];

    $("#delivery_date").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        yearRange: "c-1:c+1",
        beforeShowDay: function(date) {
            let formattedDate = $.datepicker.formatDate("yy-mm-dd", date);
            if (availableDates.includes(formattedDate)) {
                return [true, "available-date", "Available"];
            } else {
                return [false, "inactive-date", "Unavailable"];
            }
        }
    });
});
