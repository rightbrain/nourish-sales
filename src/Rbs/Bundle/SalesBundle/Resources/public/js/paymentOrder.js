var Payment = function()
{
    function filterInit(){
        if (!$('#external_filter_container').length) {
            $('<div id="external_filter_container">' +
                '<div id="date-filter"><div class="input-group input-daterange">' +
                '<input type="text" class="form-control input-small" value="">' +
                '<span class="input-group-addon">to</span>' +
                '<input type="text" class="form-control input-small" value="" style="margin-left: 0">' +
                '</div></div>' +
                '<div id="customer-filter"></div>' +
                '<button class="btn green pull-right payment-filter-btn">Filter</button>' +
                '</div>').appendTo('#payment_datatable_filter');
        }

        var table = $('#payment_datatable').DataTable();

        $('.input-daterange').datepicker({
            autoclose: true,
            todayBtn: "linked",
            format: 'dd/mm/yyyy'
        });/*.on('changeDate', function(e){
            var fromDate = moment(e.date).format('DD-MM-YYYY');
            var toDate = moment(e.date).format('DD-MM-YYYY');
            //table.columns(0).search(fromDate+'--'+toDate).draw();
        });*/

        $("#customer-filter").select2({
            placeholder: "Customers",
            allowClear: true,
            minimumInputLength: 3,
            ajax: {
                url: Routing.generate('customer_search'),
                dataType: 'json',
                quietMillis: 250,
                data: function (term, page) {
                    return {
                        q: term
                    };
                },
                results: function (data) {
                    return {results: data};
                },
                cache: true
            }
        }).on('change', function(){
            //table.columns(1).search($(this).val()).draw();
        }).prev().addClass('form-control input-medium').css('vertical-align', 'initial');

        // Filter Button Action - Filter Payment
        $('.payment-filter-btn').on('click', function(){
            var fromDate = moment($('#date-filter input:eq(0)').datepicker("getDate")).format('DD-MM-YYYY');
            var toDate = moment($('#date-filter input:eq(1)').datepicker("getDate")).format('DD-MM-YYYY');

            table
                .columns(0).search(fromDate+'--'+toDate)
                .columns(1).search($("#customer-filter").val())
                .draw();
        });

        var orderFilterContainer = $('#payment_datatable_filter');
        // Add class to select to match with theme
        orderFilterContainer.find('select').addClass("form-control");

        // Remove global search box
        orderFilterContainer.addClass('pull-right').find('label').remove();
        // Remove Individual Filter Inputs
        $('.dataTables_scrollHead').find('table thead tr').eq(1).remove();
    }

    function handleCustomerChange()
    {
        if (!$("#payment_customer").length) {
            return;
        }
        $("#payment_customer").change(function () {
            var ordersElm = $('#payment_orders');
            var customer = $(this).val();
            if (customer == '') {
                ordersElm.find('option').remove();
                return fales;
            }
            $.ajax({
                type: "post",
                url: Routing.generate('partial_payment_orders', {id: customer}),
                dataType: 'json',
                success: function(data) {
                    ordersElm.find('option').remove();
                    for (var i=0; i < data.length; i++) {
                        var arr = data[i];
                        ordersElm.append('<option value="'+arr['id']+'">'+arr['text']+'</option>');
                    }
                },
                error: function(){
                    toastr.error('Server Error');
                }
            });
        });
    }

    function init()
    {
        handleCustomerChange();
    }

    return {
        init: init,
        filterInit: filterInit
    }
}();