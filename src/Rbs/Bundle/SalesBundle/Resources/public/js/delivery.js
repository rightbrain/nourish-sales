var Delivery = function()
{
    function filterInit(){
        if (!$('#external_filter_container').length) {
            $('<div id="external_filter_container">' +
                '<div><input class="form-control input-small" placeholder="Order ID"></div>' +
                '<div><input class="form-control date-picker" placeholder="Order Date"></div>' +
                '<div id="agent-filter"></div>' +
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
        var oneValidValue = false;
        $('.orderItems').find('tr').each(function(index, e){
            var elm = $(e);
            var deliver = elm.find('.deliver');

            deliver.removeClass('has-error');

            var deliveryQtq = deliver.find('input').val();

            if (parseInt(deliveryQtq) > 0) {
                oneValidValue = true;
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', false);
            }

        });

        return oneValidValue;
    }

    function orderItemRemainingHandleInit() {

        var item_qty_sum = 0;
        $('body .item-qty').each(function() {
            var value = $(this).text();
            if(!isNaN(value) && value.length != 0) {
                item_qty_sum += parseFloat(value);
            }
        });

        $('body').find('.totalItemQty').text(item_qty_sum);


        var delivery_qty_sum = 0;
        $('body .deliver-qty').each(function() {
            var value = $(this).val();
            var unitPrice = $(this).closest('tr').find('.orderItemPrice').text();

            var amount = parseFloat(unitPrice)*value;
            $(this).closest('tr').find('.deliveryItemAmount').text(parseFloat(amount).toFixed(2));

            if(!isNaN(value) && value.length != 0) {
                delivery_qty_sum += parseFloat(value);
            }
        });

        $('.totalDeliveryQty').text(delivery_qty_sum);


        var sum = 0;
        $('body .remain').each(function() {
            var value = $(this).text();
            if(!isNaN(value) && value.length != 0) {
                sum += parseFloat(value);
            }
        });

        $('body').find('.totalRemainingQty').text(sum);
    }

    function orderItemRemainingHandle()
    {
        $('body').on('keypress keyup blur','.deliver-qty', function () {

            var orderId = $(this).closest('tr').find('.order_id').text();
            var totalApprovedAmount = $('.totalApprovedAmount_'+orderId).text();

        // $('.orderItems').find('.deliver-qty').blur(function(){
            if (formValidateInit()) {
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', false);
            }else {
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', true);
            }
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
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', true);
            } else {
                remain.text(qty - deliveryQtq);
            }

            var item_qty_sum = 0;
            $('body .item-qty').each(function() {
                var value = $(this).text();
                if(!isNaN(value) && value.length != 0) {
                    item_qty_sum += parseFloat(value);
                }
            });

            $('.totalItemQty').text(item_qty_sum);


            var delivery_qty_sum = 0;
            $('body .deliver-qty').each(function() {
                var value = $(this).val();
                var unitPrice = $(this).closest('tr').find('.orderItemPrice').text();
                console.log(unitPrice);
                var amount = parseFloat(unitPrice)*value;
                $(this).closest('tr').find('.deliveryItemAmount').text(parseFloat(amount).toFixed(2));
                if(!isNaN(value) && value.length != 0) {
                    delivery_qty_sum += parseFloat(value);
                }
            });

            $('.totalDeliveryQty').text(delivery_qty_sum);

            var delivery_amount_sum = 0;
            $('body .deliveryItemAmount_'+orderId).each(function() {
                var value = $(this).text();
                // var unitPrice = $(this).closest('tr').find('.orderItemPrice').text();
                // console.log(unitPrice);
                // var amount = parseFloat(unitPrice)*value;
                // $(this).closest('tr').find('.deliveryItemAmount').text(amount);
                if(!isNaN(value) && value.length != 0) {
                    delivery_amount_sum += parseFloat(value);
                }
            });

            $('.totalDeliveryAmount_'+orderId).text(parseFloat(delivery_amount_sum).toFixed(2));

            if (delivery_amount_sum > totalApprovedAmount) { // Invalid value
                remain.text(qty);
                $(this).val(0);
                toastr.error('Delivery quantity amount is greater then clearance amount');
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', true);
            }

            var sum = 0;
            $('body .remain').each(function() {
                var value = $(this).text();
                if(!isNaN(value) && value.length != 0) {
                    sum += parseFloat(value);
                }
            });

            $('.totalRemainingQty').text(sum);

        });
    }

    function orderProgressHandle()
    {
        var container = $('#process-actions');
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
                        }

                        if (that.attr('data-action') == 'finish-loading') {
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
        /*$('#deliveryView').on('shown.bs.modal', function (){
            setTimeout(function(){
                orderItemRemainingHandleInit();
                orderProgressHandle();
                $('#process-actions span').tooltip();
                App.integerMask($('#delivery-item-form').find('.orderItems .deliver-qty'));
            }, 500);
        });*/

        $(window).load( function (){
            setTimeout(function(){
                orderItemRemainingHandle();
                $('body').find('#delivery-item-form').find('#save-delivery').prop('disabled', true);
                orderItemRemainingHandleInit();
                orderProgressHandle();
                $('#process-actions span').tooltip();
                App.integerMask($('#delivery-item-form').find('.orderItems .deliver-qty'));
                App.integerMask($('#delivery_order_amendment').find('.itemQty'));
                // App.integerMask($('#delivery_order_amendment').find('.amendmentItemQty'));
            }, 500);
        });

        $('body').on('click', '#save-delivery', function(){
            var loadingDiv = $('.modal-body').find('.portlet');
            if (formValidateInit()) {
                bootbox.confirm('Are you sure?', function(result) {
                    if (result) {
                        Metronic.blockUI({
                            target: loadingDiv,
                            animate: true,
                            overlayColor: 'black'
                        });
                        $.post(Routing.generate('delivery_save', {id:$('#delivery-id').val()}), $('#delivery-item-form').serialize())
                            .done(function(){
                                toastr.success('Order Delivery Saved Successfully. Please wait while page reload.');
                                // location.reload();
                                window.location.href= Routing.generate('vehicle_info_load_list')
                            })
                            .fail(function(){
                                toastr.error('Server error. Contact with System Admin');
                                Metronic.unblockUI(loadingDiv);
                            });
                    }
                });
            } else {
                toastr.error('Please enter at least one quantity');
            }

            return false;
        });

        $('body').on('keyup','.itemQty', function () {
            var element = $(this);

            var maxQty = element.attr('max');
            var amendment_item_id = element.closest('tr').find('.amendment_item_id').val();
            var amendment_item_unit_price = element.closest('tr').find('.amendment_item_unit_price').val();

            element.closest('tr').find('.totalAmount').text('');
            var orderId = element.closest('tr').find('.order_id').val();

            var itemId = element.closest('tr').find('.item_id').val();
            var unit_price = element.closest('tr').find('.unit_price').text();
            var amendmentItemQty = element.closest('tr').find('.amendmentItemQty').val();

            // var exOrderItemsQty = $('tbody.orderItems').find('.order_'+orderId).find('.itemId_'+itemId).text();
            if (amendment_item_id!=''){
                if(parseInt(element.val())>parseInt(maxQty)){
                    element.val(maxQty);

                    var amendmentCalculateQty = parseFloat((parseInt(element.val())*parseFloat(unit_price)) / amendment_item_unit_price).toFixed(2);
                    // console.log(amendmentCalculateQty)
                    if (amendmentCalculateQty != Math.floor(amendmentCalculateQty)) {

                        amendmentCalculateQty = parseInt(amendmentCalculateQty) + 1;

                        // console.log(amendmentCalculateQty);
                    }
                    element.closest('tr').find('.amendmentItemQty').val(parseFloat(amendmentCalculateQty));

                    // element.closest('tr').find('.amendmentItemQty').val('');
                    toastr.error('Max quantity cross.');
                    return false
                }
            }


            if(amendment_item_unit_price){
                exOrderItemsAmount = parseInt(amendmentItemQty)*parseFloat(amendment_item_unit_price);
            }else {
                exOrderItemsAmount=0;
            }
            if ($(this).val()=='' || $(this).val()<=0) {
                element.closest('tr').find('.itemAdd').prop('disabled', true);
                element.closest('tr').find('.amendmentItemQty').val('');
            }
            // console.log(exOrderItemsAmount);
            if($.isNumeric($(this).val()) && itemId!=='' && unit_price!==''){
                var totalAmount = parseInt(element.val())*parseFloat(unit_price);

                element.closest('tr').find('.totalAmount').text(parseFloat(totalAmount).toFixed(2));
                if (amendment_item_id!='') {
                    var amendmentCalculateQty = parseFloat(totalAmount / amendment_item_unit_price).toFixed(2);
                    // console.log(amendmentCalculateQty)
                    if (amendmentCalculateQty != Math.floor(amendmentCalculateQty)) {

                        amendmentCalculateQty = parseInt(amendmentCalculateQty) + 1;

                        // console.log(amendmentCalculateQty);
                    }
                    element.closest('tr').find('.amendmentItemQty').val(amendmentCalculateQty);
                }

                // var totalApprovedAmount = $('.totalApprovedAmount_'+orderId).text();
                // var orderTotalAmount = $('.totalAmount_'+orderId).text();

                element.closest('tr').find('.itemAdd').prop('disabled', false);

                /*if (exOrderItemsAmount < totalAmount ) {
                    element.closest('tr').find('.itemAdd').prop('disabled', true);
                    element.val(0);
                    element.closest('tr').find('.totalAmount').text('');
                    alert('Clearance amount limit cross');
                    return false;
                }*/

            }


        });


        $('body').on('change','#order_id', function () {
            var element = $(this),
            orderId = element.val();
            if(orderId==''){
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('order_item_remaining', {order:orderId}),
                dataType: 'json',
                success: function (response) {
                    var htmlOption='<option value="">Select Item</option>';
                    $.each( response, function( key, value ) {
                        htmlOption += '<option value="'+key+'">'+value+'</option>'
                    });

                    $('#amendment_item_id').html(htmlOption);
                }
            });
        }).change();

        /*$('body').on('change', '#amendment_item_id', function () {
            var element = $(this);

            amendmentItem(element);


        }).change();*/

        $('body').on('change','#item_id, #amendment_item_id', function () {
            var parentElement = $('#deliveryView');
            var element = $(this);

            amendmentItem(element);

            amendedItem(parentElement, element);
        });

        $('body').on('click','#itemAdd', function () {
            var element = $(this);
            var parentElement = $('#deliveryView');
            var orderId = element.closest('tr').find('.order_id').val();
            var totalApprovedAmount = parentElement.find('.totalApprovedAmount_'+orderId).text();
            var amendmentItemId = element.closest('tr').find('.amendment_item_id').val();
            var itemId = element.closest('tr').find('.item_id').val();
            var itemQty = element.closest('tr').find('.itemQty').val();
            var amendmentItemQty = element.closest('tr').find('.amendmentItemQty').val();
            var amendment_item_unit_price = element.closest('tr').find('.amendment_item_unit_price').val();
            var unit_price = element.closest('tr').find('.unit_price').text();
            var deliveryId = $('.delivery-id').val();

            if(orderId==''||itemId==''||itemQty==''||unit_price==''){
                alert('Please enter value.');
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('order_item_add_ajax'),
                data: {
                    'orderId':orderId,
                    'amendmentItemId':amendmentItemId,
                    'amendmentItemQty':amendmentItemQty,
                    'amendmentItemUnitPrice':amendment_item_unit_price,
                    'itemId':itemId,
                    'itemQty':itemQty,
                    'unitPrice':unit_price,
                    'totalApprovedAmount':parseInt(totalApprovedAmount),
                    'deliveryId':deliveryId
                },
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if(response.status==='success'){
                        if(response.type==='new'){
                            $(".delivery_order tbody").find(".order_"+response.orderId).eq(-1).after($('<tr class="order_'+response.orderId+'"><td>'+response.orderItemCount+'</td><td>'+response.orderId+'</td><td>'+response.itemName+'</td><td class="item-qty orderItemQty_'+response.orderItemId+'">'+response.itemQty+'</td><td class="deliver"><input name="qty['+response.orderId+']['+response.orderItemId+']" class="form-control input-xsmall deliver-qty" value="0" style="text-align: right;"></td><td class="remain">'+response.itemQty+'</td><td></td></tr>'));
                        }
                        if(response.type==='old'){
                            $(".delivery_order tbody").find(".order_"+response.orderId).find('.orderItemQty_'+response.orderItemId).text(response.itemQty);
                            $(".delivery_order tbody").find(".order_"+response.orderId).find('.orderItemQty_'+response.orderItemId).closest('tr').find('.remain').text(response.itemQty);
                            $(".delivery_order tbody").find(".order_"+response.orderId).find('.orderItemQty_'+response.orderItemId).closest('tr').find('.deliver-qty').val(0);
                        }
                        $(".delivery_order tbody").find(".totalAmount_"+response.orderId).text(response.totalAmount);
                        toastr.success(response.message);
                        element.closest('tr').find('.itemQty').val('');
                        element.closest('tr').find('.stock-available').text('');
                        element.closest('tr').find('.unit_price').text('');
                        element.closest('tr').find('.totalAmount').text('');


                        // $("#delivery_order_amendment").load(window.location + " #delivery_order_amendment");
                        // $("#delivery_order").load(window.location + " #delivery_order");
                        window.location.href= Routing.generate('delivery_view', {id:$('#delivery-id').val()});

                    }
                    if(response.status==='error'){
                        toastr.error(response.message);
                    }
                    setTimeout(function(){
                        orderItemRemainingHandleInit();
                        App.integerMask($('body').find('#delivery_order_amendment').find('.itemQty'));
                        // App.integerMask($('body').find('#delivery_order_amendment').find('.amendmentItemQty'));
                        App.integerMask($('body').find('#delivery-item-form').find('.orderItems .deliver-qty'));
                    }, 500);

                }
            });
        });

        function amendmentItem(element) {
            var amendment_item_id = element.closest('tr').find('#amendment_item_id').val();
            var orderId = element.closest('tr').find('.order_id').val();
            // var amendment_item_id = element.val();
            var itemId = element.closest('tr').find('#item_id').val();
            if (amendment_item_id=='' || orderId==''){
                return false;
            }
            if (itemId===amendment_item_id){
                element.closest('tr').find('.stock-available').text('');
                element.closest('tr').find('.unit_price').text('');
                element.closest('tr').find('.itemAdd').prop('disabled', true);
                toastr.error('Please select different item.');
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('find_stock_item_depo_for_amenment_ajax', {order:orderId,item:amendment_item_id}),
                dataType: 'json',
                success: function (response) {
                    element.closest('tr').find('.itemId').val('');
                    element.closest('tr').find('.amendment_item_unit_price').val('');
                    element.closest('tr').find('.amendment_item_unit_price_show').find('strong').text('');
                    element.closest('tr').find('.itemAdd').prop('disabled', true);
                    if(response.status==='success'){
                        var stockAvailableInfo = parseInt(response.onHand) - parseInt(response.onHold);
                        element.closest('tr').find('.amendment_item_unit_price').val(response.price);
                        element.closest('tr').find('.amendment_item_unit_price_show').find('strong').text(response.price);
                        if(stockAvailableInfo>0){
                            element.closest('tr').find('.itemAdd').prop('disabled', false);
                        }

                    }
                    if(response.status==='error'){
                        toastr.error(response.message);
                    }
                }
            });
        }

        function amendedItem(parentElement, element) {

            var orderId = element.closest('tr').find('.order_id').val();
            var itemId = element.closest('tr').find('#item_id').val();
            var amendment_item_id = element.closest('tr').find('.amendment_item_id').val();
            var amendment_item_unit_price = element.closest('tr').find('.amendment_item_unit_price').val();



            var amendmentItemQty = parentElement.find('.itemId_'+amendment_item_id).text();
// console.log(amendmentItemQty)
            //
            var totalApprovedAmount = parseInt(amendmentItemQty*amendment_item_unit_price);

            if(amendment_item_id==''){
                var totalClearanceAmount = parentElement.find('.totalApprovedAmount_'+orderId).text();
                var orderTotalAmount = parentElement.find('.totalAmount_'+orderId).text();
                totalApprovedAmount = parseFloat(totalClearanceAmount)-parseFloat(orderTotalAmount);
            }

            // var itemId = element.val();
            if(orderId==''||itemId==''){
                element.closest('tr').find('.stock-available').text('');
                element.closest('tr').find('.unit_price').text('');
                return false;
            }

            if (itemId===amendment_item_id){
                element.closest('tr').find('.stock-available').text('');
                element.closest('tr').find('.unit_price').text('');
                element.closest('tr').find('.itemAdd').prop('disabled', true);
                toastr.error('Please select different item.');
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('find_stock_item_depo_for_amenment_ajax', {order:orderId,item:itemId}),
                dataType: 'json',
                success: function (response) {
                    element.closest('tr').find('.stock-available').text('');
                    element.closest('tr').find('.unit_price').text('');
                    element.closest('tr').find('.itemQty').val('');
                    element.closest('tr').find('.itemAdd').prop('disabled', true);
                    element.closest('tr').find('.itemQty').prop('disabled', true);
                    if(response.status==='success'){
                        var itemQty = parseInt(totalApprovedAmount/response.price);
                        var stockAvailableInfo = parseInt(response.onHand) - parseInt(response.onHold);
                        element.closest('tr').find('.stock-available').text(stockAvailableInfo);
                        element.closest('tr').find('.itemQty').val(itemQty);
                        element.closest('tr').find('.itemQty').attr('data-max-qty',itemQty);
                        element.closest('tr').find('.itemQty').attr('max',itemQty);
                        element.closest('tr').find('.unit_price').text(response.price);
                        if(amendment_item_id=!'') {
                            element.closest('tr').find('.amendmentItemQty').val(amendmentItemQty);
                        }
                        if(stockAvailableInfo>0){
                            element.closest('tr').find('.itemAdd').prop('disabled', false);
                            element.closest('tr').find('.itemQty').prop('disabled', false);
                        }

                    }
                    if(response.status==='error'){
                        toastr.error(response.message);
                    }
                }
            });
        }
    }

    function init()
    {
        saveDelivery();
    }

    return {
        init: init,
        filterInit: filterInit,
        formValidateInit: formValidateInit
    }
}();