var ChickOrderTemp = function()
{
    function ManageInit() {

        $('.item_quantity').on('change', function () {
            var element = $(this);
            var itemId = $(this).attr('data-item-id');
            var orderId = $(this).attr('data-order-id');
            var orderItemId = $(this).attr('data-order-item-id');
            var itemQuantity = $(this).val();

            var stockId = $('.item_'+itemId).attr('data-daily-stock-id');
            var stock_quantity = $('.item_'+itemId+'_quantity').text();

            if(orderId===''){
                return false;
            }
            if(orderItemId===''){
                return false;
            }

            if(Number(stock_quantity) < Number(itemQuantity)){
                alert('This Item remaining quantity not available');
                $(this).val(0);
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('update_chick_order_item_temp_ajax', {
                    order: orderId,
                    orderItem: orderItemId,
                    stock: stockId,
                }),
                data: {
                    quantity:itemQuantity,
                } ,
                dataType: 'json',
                success: function (response) {

                    element.val(Number(response.itemQuantity));
                    $('.item_'+itemId+'_quantity').text(response.stockRemainingQuantity);

                    // console.log(response.onHand)

                }
            });
        });

        $('a.stock_item').on('click', function () {
            $('input.item_stock').toggle();
        });

        $('.chick-order-region-summary').each(function(){
            var currentRegion = $(this);
            $(this).find('thead tr:nth-child(1) td.region').each(function () {

                var regionTag = $(this).attr('data-region-item');
                var totalElm = $(currentRegion).find('.region_item_total_'+regionTag);
                // alert(totalElm);
                $(currentRegion).find('.item_quantity').keyup(function(){
                    calcTotalOfCurrentRegion(currentRegion, totalElm);
                    calTotalOfAllRegion();
                });

                calcTotalOfCurrentRegion(currentRegion, totalElm);
            });
        });

        $('.final-chick-order-region-summary').each(function(){
            var currentRegion = $(this);
            $(this).find('tfoot tr:nth-child(1) td.region').each(function () {

                var regionTag = $(this).attr('data-region-item');
                var totalElm = $(currentRegion).find('.region_item_total_'+regionTag);

                var itemId = $(totalElm).attr('data-region-item-id');
                var total = 0;
                $('.temp_item_qty_'+itemId).each(function(){
                    var val =  $(this).text();

                    if(val>0){

                        total += parseInt(val);
                    }


                });
                $('.grandTotal_'+itemId).text(total);
            });
        });
        calTotalOfAllRegion();
    }

    function calcTotalOfCurrentRegion(currentRegion, totalElm) {
        var total = 0;
        var itemId = $(totalElm).attr('data-region-item-id');
        currentRegion.find('.temp_item_qty_'+itemId).each(function(){
            var val = $(this).is('td') ? $(this).text() : $(this).val();

            if (!val) val = 0;
            total += parseInt(val);
        });
        totalElm.text(total);
    }


    function calTotalOfAllRegion() {
        var total = 0;
        $('.total-qty').each(function(){
            total += parseInt($(this).text());
        });
        $('.grand-total span').text(total);
    }



    return {
        ManageInit: ManageInit
    }
}();