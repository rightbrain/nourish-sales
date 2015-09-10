var Order = function()
{
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
            $('#order-item-'+index).remove();
            totalAmountCalculate();
        });
    }

    function findStockItem(item, index) {
        var collectionHolder = $('.order-item-list');
        if (item == "") {
            setOrderItemValue(index, 0, 0, false, 0, '');
            totalAmountCalculate();
            return false;
        }

        // Check Duplicate Item Select
        if (isItemExits(collectionHolder, item, index)) {
            var selectedItem = $('#order-item-'+index).find('select');
            toastr.error(selectedItem.find(":selected").text() + " Item Already Selected.");
            selectedItem.val("").trigger('change');
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
                setOrderItemValue(index, onHand, onHold, available, price, itemUnit);
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

    function setOrderItemValue(index, onHand, onHold, availableOnDemand, price, itemUnit){
        var row = $('#order-item-'+index);
        var stockAvailableInfo = 'Available On Demand';

        if (!availableOnDemand) {
            stockAvailableInfo = parseInt(onHand) - parseInt(onHold);
        }
        row.find('.item-price input').val(price);
        row.find('.stock-available').text(stockAvailableInfo);
        row.find('.quantity').val(0);
        row.find('.item-unit').text(itemUnit);
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
        $collectionHolder = $('tbody.tags');
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

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

        $('.order-item-list tbody').on("click keyup", ".quantity", (totalPriceCalculation));
    }

    function init()
    {
        newOrder();
    }

    return {
        init: init
    }
}();