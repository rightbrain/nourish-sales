var Order = function()
{
    var customer = $("#order_customer").val();
    if (customer) {
        $.ajax({
            type: "post",
            url: Routing.generate('find_customer_ajax'),
            data: "customer=" + customer,
            dataType: 'json',
            success: function (response) {
                var creditLimit = response.creditLimit;
                $("div.credit_limit").html(creditLimit);
            },
            error: function(){
                Metronic.unblockUI();
            }
        });
    }

    function bindItemChangeEvent(collectionHolder) {
        collectionHolder.find('tr').each(function(index, elm){
            $(elm).find('select').change(function(){
                findStockItem($(this).val(), index);

                $("#order_orderItems_" + index + "_remove").click(function () {
                    var parent = $(this).closest('tr');
                    parent.remove();
                    totalAmountCalculate();
                });

            }).trigger('change');
        });
    }

    function addItemForm($collectionHolder) {
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
            if ($collectionHolder.find('tr').length == 1) {
                bootbox.alert("Minimum One Item Require.");
                return false;
            }
            var parent = $(this).closest('tr');
            parent.remove();
            totalAmountCalculate();
        });
    }

    function findStockItem(item, index) {
        var collectionHolder = $('.order-item-list');
        if (item == "") {
            setOrderItemValue(collectionHolder, index, 0, 0, false, 0, 0, '0');
            return false;
        }

        Metronic.blockUI({
            target: collectionHolder,
            animate: true,
            overlayColor: 'black'
        });

        $.ajax({
            type: "post",
            url: Routing.generate('find_stock_item_ajax'),
            data: "item=" + item,
            dataType: 'json',
            success: function (response) {
                var onHand = response.onHand;
                var onHold = response.onHold;
                var available = response.available;
                var price = response.price;
                var itemUnit = response.itemUnit;
                setOrderItemValue(collectionHolder, index, onHand, onHold, available, price, itemUnit, false);
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
            totalAmount += subTotal;
        });

        $("#order_totalAmount").val(totalAmount);
    }

    function totalPriceCalculation() {
        var price = parseFloat($(this).closest('td').parent('tr').find('.price').val());
        var quantity = parseFloat($(this).closest('td').parent('tr').find('.quantity').val());
        if (!price) { price = 0; }
        if (!quantity) { quantity = 0; }
        $(this).closest('td').parent('tr').find('.total_price').val(price * quantity);
        totalAmountCalculate();
    }

    function setOrderItemValue(collectionHolder, index, onHand, onHold, availableOnDemand, price, itemUnit, itemQty){
        var row = collectionHolder.find('tbody tr:eq('+index+')');
        var stockAvailableInfo = 'Available On Demand';
        if (!availableOnDemand) {
            stockAvailableInfo = parseInt(onHand) - parseInt(onHold);
        }
        row.find('.item-price input').val(price);
        row.find('.stock-available').text(stockAvailableInfo);
        if (itemQty) {
            row.find('.quantity').val(itemQty);
        }
        row.find('.item-unit').text(itemUnit);
    }

    function newOrder()
    {
        var $collectionHolder;
        var $addTagLink = $('#add_order_item');
        $collectionHolder = $('tbody.tags');
        $collectionHolder.data('index', $collectionHolder.find(':input').length);
        bindItemChangeEvent($collectionHolder);
        $addTagLink.on('click', function(e) {
            e.preventDefault();
            addItemForm($collectionHolder);
        });

        $("#order_customer").change(function () {
            $collectionHolder.find('tr').remove();
            var customer = $(this).val();
            if (customer == false) {
                $('.hide_button').hide();
                $("div.credit_limit").html('');
            } else {
                Metronic.blockUI({
                    target: null,
                    animate: true,
                    overlayColor: 'black'
                });
                $.ajax({
                    type: "post",
                    url: Routing.generate('find_customer_ajax'),
                    data: "customer=" + customer,
                    dataType: 'json',
                    success: function (response) {
                        var creditLimit = response.creditLimit;
                        $("div.credit_limit").html(creditLimit);
                        $addTagLink.trigger('click');
                        Metronic.unblockUI();
                    },
                    error: function(){
                        Metronic.unblockUI();
                    }
                });
                $('.hide_button').show();
            }
        });

        $('.quantity').live("click keyup", (totalPriceCalculation));
    }

    function init()
    {
        newOrder();
    }

    return {
        init: init
    }
}();