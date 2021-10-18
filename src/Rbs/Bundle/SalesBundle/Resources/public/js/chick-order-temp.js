var ChickOrderTemp = function()
{
    function ManageInit() {

        $('.item_quantity').on('change', function () {
            var element = $(this);
            var itemId = $(this).attr('data-item-id');
            var orderId = $(this).attr('data-order-id');
            var orderItemId = $(this).attr('data-order-item-id');
            var itemQuantity = $(this).val();
            var prevItemQuantity = $(this).attr('data-item-value');

            var stockId = $('.item_'+itemId).attr('data-daily-stock-id');
            var stock_quantity = $('.item_'+itemId+'_quantity').text();

            if(orderId===''){
                return false;
            }
            if(orderItemId===''){
                return false;
            }

            if((parseInt(stock_quantity)+parseInt(prevItemQuantity)) < Number(itemQuantity)){
                alert('This Item remaining quantity not available');
                $(this).val(parseInt(prevItemQuantity));
                updateRegionTotal();
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
                    element.attr('data-item-value', Number(response.itemQuantity));
                    $('.item_'+itemId+'_quantity').text(response.stockRemainingQuantity);

                    var row = $(element).closest('tr');
                    var rowTotal = 0;
                    $(row).find('.item_quantity').each(function () {
                        var itemQuantity = $(this);
                        if (itemQuantity.val()>0) {
                            rowTotal += parseFloat(itemQuantity.val());
                        }
                    });
                    row.find('.line_total').text(rowTotal);

                    updateRegionTotal();
                }
            });
        });

        $('a.stock_item').on('click', function () {
            $('input.item_stock').toggle();
        });

        $('.chick-order-region-summary').each(function(){
            var currentRegion = $(this);
            var regionLineTotal=0;
            $(this).find('thead tr:nth-child(1) td.region').each(function () {

                var element=$(this);

                var regionTag = $(this).attr('data-region-item');
                var totalElm = $(currentRegion).find('.region_item_total_'+regionTag);
                // alert(totalElm);
                $(currentRegion).find('.item_quantity').keyup(function(){
                    calcTotalOfCurrentRegion(currentRegion, totalElm);
                    calTotalOfAllRegion();
                });

                calcTotalOfCurrentRegion(currentRegion, totalElm);

                var val = $(this).is('td') ? $(this).text() : $(this).val();

                if (!val) val = 0;
                regionLineTotal += parseInt(val);
            });
            $(this).find('thead tr:nth-child(1) td.region_line_total').text(regionLineTotal);
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
        calLineTotal();
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

    function calLineTotal() {
        $('.chick-order-region-summary .item_table tr').each(function () {
            var row = $(this);
            var rowTotal = 0;
            $(this).find('.item_quantity').each(function () {
                var itemQuantity = $(this);
                if (itemQuantity.val()>0) {
                    rowTotal += parseFloat(itemQuantity.val());
                }
            });
            row.find('.line_total').text(rowTotal);
        });
    }

    function updateRegionTotal() {

        $('.chick-order-region-summary').each(function(){
            var currentRegion = $(this);
            var regionLineTotal=0;
            $(this).find('thead tr:nth-child(1) td.region').each(function () {

                var regionTag = $(this).attr('data-region-item');
                var totalElm = $(currentRegion).find('.region_item_total_'+regionTag);

                calcTotalOfCurrentRegion(currentRegion, totalElm);

                var val = $(this).is('td') ? $(this).text() : $(this).val();

                if (!val) val = 0;
                regionLineTotal += parseInt(val);
            });
            $(this).find('thead tr:nth-child(1) td.region_line_total').text(regionLineTotal);
        });
    }



    return {
        ManageInit: ManageInit
    }
}();