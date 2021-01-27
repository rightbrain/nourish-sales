var VehicleChick = function()
{

    function filterInit(){
        if (!$('#external_filter_container').length) {
            $('<div style="float: right" id="external_filter_container">' +
                '<label style="float: left" class="col-md-2">Filter: </label>' +
                '<div class="col-md-6"><input class="form-control date-picker order-date" placeholder="Order Date"></div>' +
                '</div>').appendTo('#chick_truck_info_datatable_filter');
        }

        $('.date-picker').datepicker({
            autoclose: true,
            todayBtn: "linked",
            format: 'dd-mm-yyyy'
        });
        var table = $('#chick_truck_info_datatable').DataTable();
        var orderFilterContainer = $('#chick_truck_info_datatable_filter');
        // Add class to select to match with theme

        setTimeout(function(){
            orderFilterContainer.find('input.order-date').on('change', function(e){
                var date = $(this).datepicker("getDate");
                date = date ? moment(date).format('DD-MM-YYYY') : '';

                table.columns(0).search(date)
                    .draw();
            });
        },500);


    }

    return {
        filterInit: filterInit,
    }
}();
// VehicleChick.filterInit();