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

        $('.orderItems').find('tr').each(function(index, e){
            var elm = $(e);
            var qty = elm.find('.item-qty').text();
            var deliver = elm.find('.deliver');
            var check = elm.find('[type=checkbox]');

            deliver.removeClass('has-error');

            if (check.length == 1 && check.is(':checked')) {
                return;
            }

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
        $('.orderItems').find('.deliver-qty').blur(function(){
            var elm = $(this).parents('tr');

            var qty = parseInt(elm.find('.item-qty').text());
            var deliveryQtq = parseInt($(this).val());
            var remain = elm.find('.remain');
            var check = elm.find('[type=checkbox]');

            if (check.length && !check.is(':checked')) {
                return;
            }

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
        var container = $('#process-actions');
        /*var inBtn = container.find('td:eq(0) button');
        var startBtn = container.find('td:eq(1) button');
        var finishBtn = container.find('td:eq(2) button');
        var outBtn = container.find('td:eq(3) button');*/
        var loadingDiv = $('.modal-body').find('.portlet');

        container.find('button').on('click', function(){
            var that = $(this);
            bootbox.confirm('Are you sure?', function(result) {
                if (result) {

                    Metronic.blockUI({
                        target: loadingDiv,
                        animate: true,
                        overlayColor: 'black'
                    });

                    $.get(that.attr('data-route'), function(){

                        that.closest('td').text(moment().format('h:mm a'));
                        var nextBtn = container.find('td button:eq(0)');
                        if (nextBtn) {
                            nextBtn.attr('disabled', false);
                        } else {
                            $('#save-delivery').attr('disabled', false);
                        }

                        Metronic.unblockUI(loadingDiv);

                    }, 'json').fail(function() {
                        Metronic.unblockUI(loadingDiv);
                        toastr.error('Server error');
                    });
                }
            });
        });
    }

    function saveDelivery()
    {
        $('#deliveryView').on('shown.bs.modal', function (){
            orderItemRemainingHandle();
            orderProcessHandle();
        });

        $('body').on('click', '#save-delivery', function(){
            if (formValidateInit()) {
                $.post('/delivery/2/save', $('#delivery-item-form').serialize())
                    .done(function(){

                    })
                    .fail(function(){

                    });
            }
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