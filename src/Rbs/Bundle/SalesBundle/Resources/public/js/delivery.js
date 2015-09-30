var Delivery = function()
{
    function filterInit(){
        if (!$('#external_filter_container').length) {
            $('<div id="external_filter_container">' +
                '<div><input class="form-control input-small" placeholder="Order ID"></div>' +
                '<div><input class="form-control date-picker" placeholder="Order Date"></div>' +
                '<div id="customer-filter"></div>' +
                '<button class="btn green pull-right payment-filter-btn">Filter</button>' +
                '</div>').appendTo('#delivery_datatable_filter');
        }

        var table = $('#delivery_datatable').DataTable();
        var filterContainer = $('#external_filter_container');
        $('.date-picker').datepicker({
            autoclose: true,
            todayBtn: "linked",
            format: 'dd-mm-yyyy'
        });

        // Filter Button Action - Filter Payment
        $('.payment-filter-btn').on('click', function(e){
            var date = filterContainer.find('input:eq(1)').datepicker("getDate");
            date = date ? moment(date).format('DD-MM-YYYY') : '';
            table
                .columns(0).search(filterContainer.find('input:eq(0)').val())
                .columns(1).search(date)
                .draw();
        });

        // Enter key trigger
        filterContainer.find('input').on('keyup', function(e){
            if (e.keyCode == 13) {
                $('.payment-filter-btn').trigger('click');
            }
        });

        // Set datatable state value
        var datatableSaveState = table.state();
        filterContainer.find('input:eq(0)').val(datatableSaveState.columns[0].search.search);
        filterContainer.find('input:eq(1)').val(datatableSaveState.columns[1].search.search);

        var orderFilterContainer = $('#delivery_datatable_filter');
        // Add class to select to match with theme
        orderFilterContainer.find('select').addClass("form-control");

        // Remove global search box
        orderFilterContainer.addClass('pull-right').find('label').remove();
        // Remove Individual Filter Inputs
        $('.dataTables_scrollHead').find('table thead tr').eq(1).remove();
    }

    function init()
    {
        newOrder();
        formValidateInit();
    }

    function formValidateInit()
    {
        var isFormValid = true;
        var form = $('form[name=order]');
        if (!form.length) return;

        form.submit(function(){

            var orderItem = $('#orderItems');
            orderItem.find('tr').each(function(index, e){
                var elm = $(e);
                var item = elm.find('td:eq(0)');
                var qty = elm.find('td:eq(1)');
                var stock = elm.find('td:eq(3)').text();

                item.removeClass('has-error');
                qty.removeClass('has-error');
                if (item.find('select').val() == '') {
                    item.addClass('has-error');
                    isFormValid = false;
                }

                if (
                    (qty.find('input').val() == '') ||
                    (stock != 'Available On Demand' && (parseInt(qty.find('input').val()) > parseInt(stock)))
                ) {
                    qty.addClass('has-error');
                    isFormValid = false;
                }

            });

            if (!isFormValid) {
                return false;
            }
        });
    }

    function OrderStateFormat(data, type, row, meta){
        return data;
    }

    function OrderPaymentFormat(data, type, row, meta){
        return data;
    }

    return {
        init: init,
        filterInit: filterInit,
        formValidateInit: formValidateInit,
        OrderStateFormat: OrderStateFormat,
        OrderPaymentFormat: OrderPaymentFormat
    }
}();