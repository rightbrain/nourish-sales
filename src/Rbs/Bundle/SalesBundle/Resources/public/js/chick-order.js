var ChickOrder = function()
{
    function ManageInit() {
        $('.chick-order-region-summary').each(function(){
            var currentRegion = $(this);
            var totalElm = $(this).find('.total-qty');
            $(this).find('.qty').change(function(){
                var currentElement = $(this);

                var currentRemainQty = parseInt($(this).closest('tr').find('.remaining_qty').text());
                if(parseInt($(this).val())>(parseInt($(this).attr('data-item-qty'))+currentRemainQty)){
                    alert('Remaining quantity are not available.');
                    $(this).val($(this).attr('data-item-qty'));
                    // return false;
                }else {
                    updateChickOrderAndDailyStock(currentElement)
                }
                calcTotalOfCurrentRegion(currentRegion, totalElm);
                calTotalOfAllRegion();
            });

            calcTotalOfCurrentRegion(currentRegion, totalElm);
        });
        calTotalOfAllRegion();

        $("input[name=save]").click(function (e) {
            e.preventDefault();
            save(false);
        });

        $("input[name=save-and-deliver]").click(function (e) {
            e.preventDefault();

            bootbox.confirm('Are you sure?', function(res){
                if (res) {
                    save(true);
                }
            });
        });

        $('#chick-order-manage-form').submit(function(e){
            e.preventDefault();
            //save(false);
        });

        initGotoViewPage();
        $(".quantity").contextmenu(function () {
            return false;
        });
        $(".itemPrice").contextmenu(function () {
            return false;
        });
        var order_generate_table_body = $('.check_order_generate tbody');
        order_generate_table_body.on("click keyup", ".quantity", (totalPriceCalculation));
        order_generate_table_body.on("click keyup", ".itemPrice", (totalPriceCalculation));

        $("input.tr_clone_add").live('click', function() {
            var selected_table = $('.active .check_order_generate');
            var $tr    = $(selected_table).find('tbody').find("tr:first");
            var $tr_last = $(selected_table).find('tbody').find("tr:last");
            var $clone = $tr.clone();
            $clone.find(':text').val('');
            $clone.find('select.agent_id').val('');
            $clone.find('input[type=hidden]').val('');
            $clone.find(':button').removeClass('hidden');
            $clone.find(':button').val('');
            $clone.find('button.remove_item').data('order_id',0);
            $clone.find('div.order_status').removeClass('old_item').addClass('new_item');
            $clone.find('div.order_status').text('New');
            $tr_last.after($clone);
        });


        $('.check_order_generate').on('change','.depot, .item', function () {
            var element = $(this);
            var depot = $(this).closest('tr').find('.depot').val();
            var item = $(this).closest('tr').find('.item').val();

            if(depot==''){
                return false;
            }
            if(item==''){
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('find_item_price_depo_ajax', {
                    item: item,
                    depo: depot
                }),
                dataType: 'json',
                success: function (response) {
                    var price = response.price;
                    element.closest('tr').find('.quantity').val('');
                    element.closest('tr').find('.itemPrice').val(price);

                }
            });


        });


        $(document).on('click','button.remove_item', function() {
            var element = $(this);
            var r = confirm('Are you sure delete?');
            var order_id = $(this).data('order_id');

            if (r == true) {
                if(order_id!=0){
                    $.ajax({
                        type: "post",
                        url: Routing.generate('delete_check_order_ajax', {
                            order: order_id
                        }),
                        dataType: 'json',
                        success: function (response) {
                            $(element).closest('tr').remove();
                        }
                    });
                }else {
                    $(element).closest('tr').remove();
                }

            }
            return false;
        });


    }

    function save(sendToDeliver){
        var form = $('#chick-order-manage-form');
        Metronic.blockUI({
            target: form,
            animate: true,
            overlayColor: 'black'
        });

        $('input[type=submit]').attr('disabled', true);

        $.ajax({
            type: "post",
            url: Routing.generate('order_manage_chick_save'),
            data: form.serialize() + '&deliver=' + sendToDeliver,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (sendToDeliver) {
                        toastr.success('Please wait, redirecting to view page.');
                        window.location.href = getChickOrderViewPageUrl();
                    } else {
                        toastr.success('Save Successfully');
                        $('input[type=submit]').attr('disabled', false);
                    }
                } else {
                    toastr.error('Service Error: ' + response.message);
                    $('input[type=submit]').attr('disabled', false);
                }
                Metronic.unblockUI(form);
            },
            error: function(){
                Metronic.unblockUI(form);
                $('input[type=submit]').attr('disabled', false);
            }
        });
    }

    function updateChickOrderAndDailyStock(currentElement ) {

        var depotId = currentElement.attr('data-depot-id');
        var stockId = currentElement.attr('data-stock-id');
        var orderId = currentElement.attr('data-order-id');
        var orderItemId = currentElement.attr('data-order-item-id');
        var itemQuantity = parseInt(currentElement.val());
        var itemMrpPrice = Number(currentElement.closest('tr').find('.itemMrpPrice').val());
        var itemPrice = Number(currentElement.closest('tr').find('.itemPrice').val());

        if(orderId===''){
            return false;
        }
        if(orderItemId===''){
            return false;
        }
        $.ajax({
            type: "post",
            url: Routing.generate('update_final_chick_order_item_ajax', {
                order: orderId,
                orderItem: orderItemId,
                stock: stockId,
            }),
            data: {
                quantity:itemQuantity,
                itemMrpPrice:itemMrpPrice,
                itemPrice:itemPrice,
            } ,
            dataType: 'json',
            success: function (response) {

                currentElement.attr('data-item-qty',Number(response.itemQuantity));
                currentElement.val(Number(response.itemQuantity));
                $('.remaining_qty_'+depotId).text(response.stockRemainingQuantity);

            }
        });
    }

    function calcTotalOfCurrentRegion(currentRegion, totalElm) {
        var total = 0;
        currentRegion.find('.qty').each(function(){
            var val = $(this).is('td') ? $(this).text() : $(this).val();
            if (!val) val = 0;
            total += parseInt(val);
        });
        totalElm.text(total);
    }

    function getChickOrderViewPageUrl() {
        return Routing.generate('order_manage_chick_summary', {'date': $('form.chick-order-filter').find('input[type=text]').val(), 'depot': $('form.chick-order-filter').find('select[name=depot]').val(), 'item': $('form.chick-order-filter').find('select[name=item]').val()})
    }

    function initGotoViewPage() {
        $('.go-to-view-page').click(function(){
            if ($('form.chick-order-filter').find('input[type=text]').val() == '') {
                toastr.error('Please select a date');
                return false;
            }

            window.location.href = getChickOrderViewPageUrl();
        });
    }

    function calTotalOfAllRegion() {
        var total = 0;
        $('.total-qty').each(function(){
            total += parseInt($(this).text());
        });
        $('.grand-total span').text(total);
    }

    function totalPriceCalculation() {
        // alert($(this));
        var row = $(this).closest('tr');
        var price = parseFloat(row.find('.itemPrice').val());
        var quantity = parseFloat(row.find('.quantity').val());
        if (!price) { price = 0; }
        if (!quantity) { quantity = 0; }
        row.find('.total_amount').val((price * quantity).toFixed(2));
        // totalAmountCalculate();
    }

    return {
        ManageInit: ManageInit
    }
}();