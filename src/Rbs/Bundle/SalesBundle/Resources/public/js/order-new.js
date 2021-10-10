var Order = function()
{
    var agent = $("#order_agent").val();

    function bindItemChangeEvent(collectionHolder) {
        collectionHolder.find('tr').each(function(index, elm){
            $(elm).find('select').change(function(){
                findStockItem($(this).val(), index);

                $("#order_orderItems_" + index + "_remove").click(function () {
                    deleteOrderItemHandler(collectionHolder, index);
                });

            }).trigger('change');
        });
    }

    function bindPaymentChangeEvent(collectionHolder) {
        collectionHolder.find('tr').each(function(index, elm){
            $(elm).find('select').change(function(){
                // findStockItem($(this).val(), index);
                $("#order_payments_"+index+"_remove").click(function () {
                    deleteOrderPaymentHandler(collectionHolder, index);
                });

                $("#order_payments_"+index+"_depositDate").datepicker( {
                    format: "yyyy-mm-dd",
                    viewMode: "default",
                    minViewMode: "default",
                    autoclose: true
                });

            }).trigger('change');


        });
    }

    function deleteOrderItemHandler(collectionHolder, index)
    {
        if (collectionHolder.find('tr').length == 1) {
            bootbox.alert("Minimum One Item Require.");
            return false;
        }
        $('#order-item-'+index).remove();
        totalAmountCalculate();
        totalQuantityCalculate();
    }

    function deleteOrderPaymentHandler(collectionHolder, index)
    {
        if (collectionHolder.find('tr').length == 1) {
            bootbox.alert("Minimum One Item Require.");
            return false;
        }
        $('#payment-'+index).remove();
    }

    function addItemForm($collectionHolder) {

        if ($('#order_agent').val() == '') {
            toastr.error("Please select an agent.");
            return false;
        }

        if ($('#order_depo').val() == '') {
            toastr.error("Please select a depo.");
            return false;
        }

        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        var $newForm = prototype.replace(/__name__/g, index);

        $collectionHolder.data('index', index + 1);
        //var $newFormLi = $('<div></div>').append(newForm);

        $collectionHolder.append($newForm);

        $("#order_orderItems_" + index + "_item").change(function () {
            var item = $(this).val();
            findStockItem(item, index);
        });

        $("#order_orderItems_" + index + "_remove").click(function () {
            deleteOrderItemHandler($collectionHolder, index);
        });

        App.integerMask($collectionHolder.find('tr:eq('+index+')').find('.quantity'));
        $("#order_orderItems_" + index + "_item").select2({
            'allowClear': true
        });
    }

    function addPaymentForm($collectionHolder) {

        if ($('#order_agent').val() == '') {
            toastr.error("Please select an agent.");
            return false;
        }

        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        var $newForm = prototype.replace(/__name__/g, index);

        $collectionHolder.data('index', index + 1);
        //var $newFormLi = $('<div></div>').append(newForm);
        $collectionHolder.append($newForm);

        $("#order_payments_" + index + "_bank").change(function () {
            var bankId = $(this).val();
            var branchId= jQuery("#order_payments_" + index + "_branch").val();
            if(bankId==''){
                var dataOption='<option value="">Select Branch</option>';
                jQuery("#order_payments_" + index + "_branch").html(dataOption);
                return false;
            }
            console.log(bankId);
            $.ajax({
                type: "get",
                url: Routing.generate('branch_by_bank', {
                    id: bankId,
                }),
                dataType: 'json',
                success: function (response) {
                    var dataOption='<option value="">Select Branch</option>';
                    jQuery.each(response, function(i, item) {
                        if(branchId==item.id){
                            var selected= 'selected="selected"'
                        }else{
                            var selected='';
                        }
                        dataOption += '<option value="'+item.id+'" '+selected+'>'+item.name+'</option>';
                    });

                    jQuery("#order_payments_" + index + "_branch").html(dataOption);
                },
                error: function(){
                    Metronic.unblockUI($collectionHolder);
                }
            });
        }).change();

        $("#order_payments_"+index+"_remove").click(function () {
            deleteOrderPaymentHandler($collectionHolder, index);
        });

        $("#order_payments_"+index+"_depositDate").datepicker( {
            format: "yyyy-mm-dd",
            viewMode: "default",
            minViewMode: "default",
            autoclose: true
        });

    }

    function autoFocusQuantityField() {
       var collectionHolder = $('tbody.tags');
        collectionHolder.data('index', collectionHolder.find(':input').length);
        var index = collectionHolder.data('index');
        $('.order-item-list').on("keydown, keyup", ".orderItem", function(event) {
            if (event.which === 13 || event.keyCode==13) {
                event.stopPropagation();
                event.preventDefault();
                $(this).closest('tr').find('td').find('.quantity').focus();
            }
        });

    }

    function findStockItem(item, index) {
        var collectionHolder = $('.order-item-list');
        if (item == "") {
            setOrderItemValue(index, 0, 0, false, 0, '');
            totalAmountCalculate();
            totalQuantityCalculate();
            return false;
        }

        // Check Duplicate Item Select
        if (isItemExits(collectionHolder, item, index)) {
            var selectedItem = $('#order-item-'+index).find('select');
            toastr.error(selectedItem.find(":selected").text() + " Item Already Selected.");
            selectedItem.val("").trigger('change');
            var option = new Option('Select Item', '', true, true);
            selectedItem.append(option).trigger('change');
            selectedItem.select2();
            return false;
        }

        Metronic.blockUI({
            target: collectionHolder,
            animate: true,
            overlayColor: 'black'
        });

        $.ajax({
            type: "post",
            url: Routing.generate('find_stock_item_depo_ajax', {
                item: item,
                agent: $('#order_agent').val(),
                depo: $('#order_depo').val(),
                order: $('#order_id').val()
            }),
            //data: "item=" + item + "&agent=" + $('#order_agent').val() + "&depoId=" + $('#order_depo').val() + "&orderId=" + $('#order_id').val() ? $('#order_id').val() : '',
            dataType: 'json',
            success: function (response) {
                var onHand = response.onHand;
                var onHold = response.onHold;
                var available = response.available;
                var price = response.price;
                var itemUnit = response.itemUnit;

                setOrderItemValue(index, onHand, onHold, available, price, itemUnit, false);
                Metronic.unblockUI(collectionHolder);
            },
            error: function(){
                Metronic.unblockUI(collectionHolder);
            }
        });
    }

    function totalAmountCalculate() {
        var subTotal = 0;
        var totalAmount = 0;
        $('.total_price').each(function () {
            subTotal = parseFloat($(this).val());
            if (subTotal) {
                totalAmount += subTotal;
            }

        });

        $("#order_totalAmount").val(totalAmount.toFixed(2));
    }

    function totalQuantityCalculate() {
        var subTotalQuantity = 0;
        var totalQuantity = 0;
        $('.quantity').each(function () {
            subTotalQuantity = parseFloat($(this).val());
            if (subTotalQuantity) {
                totalQuantity += subTotalQuantity;
            }

        });

        $("#order_totalQuantity").html(totalQuantity.toFixed(0));
    }

    function recalculateItemPriceOnEdit() {
        setTimeout(function(){
            //$('.order-item-list tbody .quantity').each(totalPriceCalculation);
        }, 100);
    }

    function totalPriceCalculation() {
        var row = $(this).closest('td').parent('tr');
        var price = parseFloat(row.find('.price').val());
        var quantity = parseFloat(row.find('.quantity').val());
        if (!price) { price = 0; }
        if (!quantity) { quantity = 0; }
        row.find('.total_price').val((price * quantity).toFixed(2));
        totalAmountCalculate();
        totalQuantityCalculate();
    }

    function setOrderItemValue(index, onHand, onHold, availableOnDemand, price, itemUnit, itemQty){
        var row = $('#order-item-'+index);

        var stockAvailableInfo = 'Available On Demand';

        if (!availableOnDemand) {
            stockAvailableInfo = parseInt(onHand) - parseInt(onHold);
        }
        row.find('.item-price input').val(price);
        row.find('.stock-available').text(stockAvailableInfo);
        if (itemQty) {
            row.find('.quantity').val(itemQty);
        }
        var qty = row.find('.quantity').val();
        row.find('.total_price').val((price * qty).toFixed(2));
        row.find('.item-unit').text(itemUnit);

        totalAmountCalculate();
        totalQuantityCalculate();
    }

    function isItemExits(collectionHolder, item, index)
    {
        var found = false;
        collectionHolder.find('tbody tr').not('#order-item-'+index).each(function(i, el){
            if (item == $(el).find('select').val()) {
                found = true;
                return false;
            }
        });

        return found;
    }

    function newOrder()
    {
        var $collectionHolder;
        var $addTagLink = $('#add_order_item');
        var agentElm = $('#order_agent');
        var depoElm = $('#order_depo');
        $collectionHolder = $('tbody.tags');
        $collectionHolder.data('index', $collectionHolder.find(':input').length);
        bindItemChangeEvent($collectionHolder);
        $addTagLink.on('click', function(e) {
            e.preventDefault();
            addItemForm($collectionHolder);
        });

        depoElm.change(function () {
            $collectionHolder.find('tr').remove();
            if (depoElm.val() != '' && agentElm.val() != '') {
                $addTagLink.trigger('click');
            }
        });

        agentElm.change(function () {

            $collectionHolder.find('tr').remove();
            var agent = $(this).val();
            if (agent == false) {
                $('.hide_button').hide();
            } else {
                Metronic.blockUI({
                    target: null,
                    animate: true,
                    overlayColor: 'black'
                });
                $.ajax({
                    type: "post",
                    url: Routing.generate('find_agent_ajax'),
                    data: "agent=" + agent,
                    dataType: 'json',
                    success: function (response) {
                        var item_type_prototype = response.item_type_prototype;
                        $collectionHolder.data('prototype', item_type_prototype);
                        Metronic.unblockUI();
                        if (depoElm.val() != '' && agentElm.val() != '') {
                            $addTagLink.trigger('click');
                        }
                    },
                    error: function(){
                        Metronic.unblockUI();
                    }
                });
                $('.hide_button').show();
            }
        }).change();

        $('.order-item-list tbody').on("click keyup", ".quantity, .price", (totalPriceCalculation));
        recalculateItemPriceOnEdit();
    }

    function newOrderPayment()
    {
        var $collectionHolder;
        var $addPaymentLink = $('#add_payment_item');
        var agentElm = $('#order_agent');
        $collectionHolder = $('tbody.payments');
        $collectionHolder.data('index', $collectionHolder.find(':input').length);
        bindPaymentChangeEvent($collectionHolder);
        $addPaymentLink.on('click', function(e) {
            e.preventDefault();
            addPaymentForm($collectionHolder);
        });
        agentElm.change(function () {

            $collectionHolder.find('tr').remove();
            var agent = $(this).val();
            if (agent == false) {
                $('.hide_button').hide();
            } else {
                Metronic.blockUI({
                    target: null,
                    animate: true,
                    overlayColor: 'black'
                });
                $.ajax({
                    type: "post",
                    url: Routing.generate('find_payment_form_ajax'),
                    data: "agent=" + agent,
                    dataType: 'json',
                    success: function (response) {
                        var item_type_prototype = response.item_type_prototype;
                        $collectionHolder.data('prototype', item_type_prototype);
                        Metronic.unblockUI();
                        if (agentElm.val() != '') {
                            $addPaymentLink.trigger('click');
                        }
                    },
                    error: function(){
                        Metronic.unblockUI();
                    }
                });
                $('.hide_button').show();
            }
        }).change();

    }

    var return_depot = function () {
        var depot = [];
        $.ajax({
            type: "get",
            async: false,
            global: false,
            url: Routing.generate('depot_for_feed'),
            dataType: 'json',
            success: function (response) {
                depot = response;
            }
        });
        return depot;
    }();

    function filterInit(){
        if (!$('#external_filter_container').length) {
            $('<div id="external_filter_container">' +
                'Filter: <div id="order-depot"></div>' +
                '<div id="order-status"></div>' +
                '<div id="order-payment-status"></div>' +
                '<div id="order-delivery-status"></div>' +
                '<div id="order-agent"></div>' +
                '<div id="order-id"></div>' +
                '</div>').appendTo('#order_datatable_filter');
        }
        $("#order_datatable").dataTable().yadcf([

                {
                    column_number: 1,
                    filter_type: 'text',
                    filter_container_id: "order-agent",
                    filter_reset_button_text: false,
                    filter_default_label: "Agent Id"
                },
                {
                    column_number: 0,
                    filter_type: 'text',
                    filter_container_id: "order-id",
                    filter_reset_button_text: false,
                    filter_default_label: "Order Id"
                },
                {
                    column_number: 4,
                    data: return_depot,
                    filter_container_id: "order-depot",
                    filter_reset_button_text: false,
                    filter_default_label: "Select Depot"
                },
                {
                    column_number: 6,
                    data: ["PENDING", "PROCESSING", "COMPLETE", "CANCEL", "HOLD"],
                    filter_container_id: "order-status",
                    filter_reset_button_text: false,
                    filter_default_label: "Order State"
                },
                {
                    column_number: 7,
                    data: ["PENDING", "PARTIALLY_PAID", "PAID"],
                    filter_container_id: "order-payment-status",
                    filter_reset_button_text: false,
                    filter_default_label: "Payment State"
                },
                {
                    column_number: 8,
                    data: ["PENDING", "PARTIALLY_SHIPPED", "SHIPPED", "HOLD"],
                    filter_container_id: "order-delivery-status",
                    filter_reset_button_text: false,
                    filter_default_label: "Delivery State"
                }
            ]
        );

        var orderFilterContainer = $('#order_datatable_filter');
        // Add class to select to match with theme
        orderFilterContainer.find('select').addClass("form-control");
        orderFilterContainer.find('input[type=text]').addClass("form-control");

        // Remove global search box
        orderFilterContainer.addClass('pull-right').find('label').remove();
        // Remove Individual Filter Inputs
        //$('.dataTables_scrollHead').find('table thead tr').eq(1).remove();

        // Humanize Filter Option's Text
        setTimeout(function(){
            orderFilterContainer.find('select option').each(function(){
                $(this).text($(this).text().replace('_', ' '));
            });
        },500);
    }

    function init()
    {
        newOrder();
        newOrderPayment();
        formValidateInit();
    }

    function formValidateInit()
    {
        var form = $('form[name=order]');
        if (!form.length) return;

        form.submit(function(){
            var isFormValid = true,
                orderItem = $('#orderItems'),
                payments = $('#payments'),
                qtyError = false,
                priceError = false,
                agentBankError = false,
                depositDateError = false,
                amountError = false,
                paymentForError = false,
                paymentMethodError = false,
                nourishBankError = false;
                bankError = false;
                branchError = false;


            if ($('#order_agent').val() == '') {
                toastr.error("Please select an agent");
                isFormValid = false;
                $('#order_agent').closest('div').addClass('has-error');
            }else {
                $('#order_agent').closest('div').removeClass('has-error');
            }

            if (isFormValid && $('#order_refSMS').val() == '') {
                toastr.error("Please select a reference SMS");
                isFormValid = false;
            }

            if (isFormValid && $('#order_depo').val() == '') {
                toastr.error("Please select a depot");
                isFormValid = false;
                $('#order_depo').closest('div').addClass('has-error');
            }else {
                $('#order_depo').closest('div').removeClass('has-error');
            }

            if (isFormValid && $('#order_paymentMode').val() == '') {
                toastr.error("Please select a payment mode");
                isFormValid = false;
                $('#order_paymentMode').closest('div').addClass('has-error');
            }else {
                $('#order_paymentMode').closest('div').removeClass('has-error');
            }

            if (isFormValid && orderItem.find('tr').length == 0) {
                toastr.error("Please add minimum an item");
                isFormValid = false;
            }

            orderItem.find('tr').each(function(index, e){
                var elm = $(e);
                var item = elm.find('td:eq(0)');
                var qty = elm.find('td:eq(1)');
                var stock = elm.find('td:eq(3)').text();
                var price = elm.find('td:eq(4)');

                item.removeClass('has-error');
                qty.removeClass('has-error');
                price.removeClass('has-error');
                if (item.find('select').val() == '') {
                    item.addClass('has-error');
                    isFormValid = false;
                }

                if (!parseFloat(price.find('input').val())) {
                    price.addClass('has-error');
                    isFormValid = false;
                    priceError = true;
                }

                if (
                    qty.find('input').val() == '' ||
                    parseInt(qty.find('input').val()) == 0
                    // (stock != 'Available On Demand' && (parseInt(qty.find('input').val()) > parseInt(stock)))
                ) {
                    qty.addClass('has-error');
                    isFormValid = false;
                    qtyError = true;
                }

            });

            if (qtyError) {
                toastr.error("Invalid Item Quantity");
            }

            if (priceError) {
                toastr.error("Invalid Price");
            }

            payments.find('tr.payment_info').each(function(index, e){
                var elm = $(e);
                var agentBank = elm.find('.payment_section').find('td:eq(0)');
                var amount = elm.find('.payment_section').find('td:eq(1)');
                var depositDate = elm.find('.payment_section').find('td:eq(2)');
                var paymentFor = elm.find('.payment_section').find('td:eq(3)');
                var paymentMethod = elm.find('.payment_section').find('td:eq(4)');
                var nourishBank = elm.find('.payment_section').find('td:eq(5)');
                var remarks = elm.find('.payment_section').find('td:eq(6)');

                var bank = elm.find('.bank_branch_section').find('.bank_section').find('td:eq(1)');
                var branch = elm.find('.bank_branch_section').find('td.branch_section');

                console.log(branch.find('select').val());
                // return false;

                agentBank.removeClass('has-error');
                amount.removeClass('has-error');
                nourishBank.removeClass('has-error');
                depositDate.removeClass('has-error');
                paymentFor.removeClass('has-error');
                paymentMethod.removeClass('has-error');
                bank.removeClass('has-error');
                branch.removeClass('has-error');

                if ((amount.find('input').val() == '' && agentBank.find('select').val() != '')||
                    (amount.find('input').val() == '' && nourishBank.find('select').val() != '')||
                    (amount.find('input').val() == '' && depositDate.find('input').val() != '')||
                    (amount.find('input').val() == '' && paymentFor.find('select').val() != '')||
                    (amount.find('input').val() == '' && paymentMethod.find('select').val() != '')||
                    (amount.find('input').val() == '' && remarks.find('textarea').val() != '')||
                    (amount.find('input').val() == '' && bank.find('select').val() != '')||
                    (amount.find('input').val() == '' && branch.find('select').val() != '')) {
                    amount.addClass('has-error');
                    isFormValid = false;
                    amountError = true;
                }
                if (amount.find('input').val() != '' && agentBank.find('select').val() == '') {
                    agentBank.addClass('has-error');
                    isFormValid = false;
                    agentBankError = true;
                }
                if (amount.find('input').val() != '' && depositDate.find('input').val() == '') {
                    depositDate.addClass('has-error');
                    isFormValid = false;
                    depositDateError = true;
                }
                if (amount.find('input').val() != '' && nourishBank.find('select').val() == '') {
                    nourishBank.addClass('has-error');
                    isFormValid = false;
                    nourishBankError = true;
                }
                if (amount.find('input').val() != '' && paymentFor.find('select').val() == '') {
                    paymentFor.addClass('has-error');
                    isFormValid = false;
                    paymentForError = true;
                }
                if (amount.find('input').val() != '' && paymentMethod.find('select').val() == '') {
                    paymentMethod.addClass('has-error');
                    isFormValid = false;
                    paymentMethodError = true;
                }

                if (amount.find('input').val() != '' && bank.find('select').val() == '') {
                    bank.addClass('has-error');
                    isFormValid = false;
                    bankError = true;
                }

                if (amount.find('input').val() != '' && branch.find('select').val() == '') {
                    branch.addClass('has-error');
                    isFormValid = false;
                    branchError = true;
                }

            });

            if (amountError) {
                toastr.error("Please enter deposit amount.");
            }
            if (agentBankError) {
                toastr.error("Please select agent bank.");
            }
            if (depositDateError) {
                toastr.error("Please enter deposit date.");
            }
            if (nourishBankError) {
                toastr.error("Please select bank account.");
            }
            if (paymentForError) {
                toastr.error("Please select payment for.");
            }
            if (paymentMethodError) {
                toastr.error("Please select payment Method.");
            }
            if (bankError) {
                toastr.error("Please select bank.");
            }
            if (branchError) {
                toastr.error("Please select branch.");
            }

            return isFormValid;
        });
    }

    function OrderStateFormat(data, type, row, meta){
        return data;
    }

    function OrderPaymentFormat(data, type, row, meta){
        return data;
    }

    function paymentConfirmationOnModal() {
        $("#ajaxSummeryView").on('click', '.payment-action-buttons button',function (event) {
            event.preventDefault();
            // var setDepositedAmount = document.getElementById("amount").innerHTML;
            var setDepositedAmount = $(this).closest('tr').find('.deposit_amount').val();
            var element = $(this);
            var current_tr = $(element).closest('tr');
            var paymentId = $(this).val();
            var isVerified = $(this).data('id') == 'payment-amount-verified';
            if(isVerified) {

                var setActualAmount = prompt("Actual Amount:", setDepositedAmount);

                if (setActualAmount != null || setActualAmount != "") {
                    $.ajax({
                        type: "get",
                        url: Routing.generate('payment_amount_verified', {
                            id: paymentId,
                            verified: isVerified,
                            actualAmount: setActualAmount,
                            depositedAmount: setDepositedAmount
                        }),
                        dataType: 'json',
                        success: function (response) {
                            $(element).closest('tr').find('.payment-action-buttons').html(response.message);
                            $(current_tr).find('.actual-amount-new').html(response.actualAmount);
                            if (!isVerified) {
                                setTimeout(function () {
                                    $(element).closest('tr').remove();
                                }, 5000);
                            }
                        }
                    });
                }
            }else{
                $.ajax({
                    type: "get",
                    url: Routing.generate('payment_amount_verified', {
                        id: paymentId,
                        verified: isVerified,
                        actualAmount: setActualAmount,
                        depositedAmount: setDepositedAmount
                    }),
                    dataType: 'json',
                    success: function (response) {
                        $(element).closest('tr').find('.payment-action-buttons').html(response.message);
                        if (!isVerified) {
                            setTimeout(function () {
                                $(element).closest('tr').remove();
                            }, 5000);
                        }
                    }
                });
            }

        });
    }

    return {
        init: init,
        filterInit: filterInit,
        formValidateInit: formValidateInit,
        OrderStateFormat: OrderStateFormat,
        OrderPaymentFormat: OrderPaymentFormat,
        PaymentConfirmationOnModal: paymentConfirmationOnModal,
        autoFocusQuantityField: autoFocusQuantityField
    }
}();