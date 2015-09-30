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

    function formValidateInit()
    {
        var isFormValid = true;

        $('#orderItems').find('tr').each(function(index, e){
            var elm = $(e);
            var qty = elm.find('td:eq(2)').text();
            var deliver = elm.find('td:eq(3)');

            deliver.removeClass('has-error');
            if (deliver.find('input').val() == '') {
                deliver.addClass('has-error');
                isFormValid = false;
            }

            var deliveryQtq = parseInt(deliver.find('input').val());
            if (!deliveryQtq || deliveryQtq > parseInt(qty)) {
                deliver.addClass('has-error');
                isFormValid = false;
            }

        });

        return isFormValid;
    }

    function orderItemRemainingHandle()
    {
        $('#orderItems').find('.deliver-qty').blur(function(){
            var elm = $(this).parents('tr');

            var qty = parseInt(elm.find('td:eq(2)').text());
            var deliveryQtq = parseInt(elm.find('td:eq(3) input').val());
            var remain = elm.find('td:eq(4)');

            if (deliveryQtq !== 0 && (!deliveryQtq || (qty - deliveryQtq) < 0)) { // Invalid value
                remain.text(qty);
                $(this).val(0);
                toastr.error('Invalid value OR delivered quantity is greater then Order quantity');
            } else {
                remain.text(qty - deliveryQtq);
            }

        }).blur();
    }

    function orderProcessHandle()
    {

    }

    function saveDelivery()
    {
        $('#deliveryView').on('shown.bs.modal', function (){
            orderItemRemainingHandle();
        });

        $('body').on('click', '#save-delivery', function(){
            console.log(formValidateInit());
        });
    }

    function init()
    {
        orderProcessHandle();
        saveDelivery();
    }

    return {
        init: init,
        filterInit: filterInit,
        formValidateInit: formValidateInit
    }
}();