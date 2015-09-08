var Order = function()
{
    function addItemForm($collectionHolder, $newLinkLi) {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        var newForm = prototype.replace(/__name__/g, index);

        $collectionHolder.data('index', index + 1);
        var $newFormLi = $('<div></div>').append(newForm);
        $newLinkLi.before($newFormLi);

        $("#order_orderItems_" + index + "_item").change(function () {
            var item = $(this).val();
            findItem(item, index);
            findStockItem(item, index);
        });

        $("#order_orderItems_" + index + "_remove").click(function () {
            var parent = $(this).closest('tr');
            parent.remove();
        });
    }

    function findItem(item, index) {
        $.ajax({
            type: "post",
            url: Routing.generate('find_item_ajax'),
            data: "item=" + item,
            dataType: 'json',
            success: function (response) {
                var price = response.price;
                $("#order_orderItems_" + index + "_price").val(price);
                $('.quantity').show();
            }
        });
    }

    function findStockItem(item, index) {
        $.ajax({
            type: "post",
            url: Routing.generate('find_stock_item_ajax'),
            data: "item=" + item,
            dataType: 'json',
            success: function (response) {
                var onHand = response.onHand;
                var onHold = response.onHold;
                var available = response.available;

                if(available==1){
                    availableOnDemand = 'AvailableOnDemand';
                }else{
                    availableOnDemand = parseFloat(onHand)-parseFloat(onHold);
                }

                $( "div#availableOnDemand" ).html(availableOnDemand);
            }
        });
    }

    function totalPriceCalculation() {

        var price = parseFloat($(this).closest('td').parent('tr').find('.price').val());
        var quantity = parseFloat($(this).closest('td').parent('tr').find('.quantity').val());
        if (!price) { price = 0; }
        if (!quantity) { quantity = 0; }
        $(this).closest('td').parent('tr').find('.total_price').val(price * quantity);
    }

    function newOrder()
    {
        var $collectionHolder;
        var $addTagLink = $('<a href="#" class="add_tag_link blue btn" id="add_order_item">Add Item</a>');
        var $newLinkLi = $('<div style="float: right;display: none;" class="hide_button" ></div>').append($addTagLink);
        $collectionHolder = $('span.tags');
        $collectionHolder.append($newLinkLi);
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $addTagLink.on('click', function(e) {
            e.preventDefault();
            addItemForm($collectionHolder, $newLinkLi);
        });

        $("#order_customer").change(function () {
            var customer = $(this).val();
            if(customer==false){
                $('.hide_button').hide();
                $( "div.credit_limit" ).html('00');
            }else{
                $.ajax({
                    type: "post",
                    url: Routing.generate('find_customer_ajax'),
                    data: "customer=" + customer,
                    dataType: 'json',
                    success: function (response) {
                        var creditLimit = response.creditLimit;
                        $( "div.credit_limit" ).html(creditLimit);
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