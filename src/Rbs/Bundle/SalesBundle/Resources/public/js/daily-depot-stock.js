var DailyDepotSock = function()
{
    function ManageInit() {

        var date = new Date();
        date.setDate(date.getDate()-1);
        $('.custom-date-picker').datepicker({
            autoclose: true,
            todayBtn: "linked",
            todayHighlight:'TRUE',
            startDate: 'now',
            format: 'dd-mm-yyyy'
        });

        $('.item_stock').on('change', function () {
            var element = $(this);
            var stockId = $(this).attr('data-id');
            var stockItemOnHand = $(this).val();
            if(stockId===''){
                return false;
            }
            if(stockItemOnHand===''){
                return false;
            }

            $.ajax({
                type: "post",
                url: Routing.generate('update_daily_depot_stock_ajax', {
                    stock: stockId,
                }),
                data: "stockItemOnHand=" + stockItemOnHand,
                dataType: 'json',
                success: function (response) {
                    element.val(Number(response.onHand));

                    element.closest('td').find('span.stock_item').text(response.onRemaining);
                    var total = 0;
                    element.closest('tr').find('span.remainingQty').each (function() {
                        total += parseInt($(this).text());
                    });
                    if(total>0){
                        element.closest('tr').find('.chick_order_generate').show();
                    }else {
                        element.closest('tr').find('.chick_order_generate').hide();
                    }
                }
            });
        });

            $('.chick-daily-stock .item_stock').each(function () {
                var currentRegion = $(this);
                var itemId = $(this).attr('data-item');
                $(currentRegion).keyup(function(){
                    calculateItemTotal(itemId);
                });

                calculateItemTotal(itemId);
            });
        }

    function calculateItemTotal(itemId) {
        var total = 0;
        var totalElm = $('.item_wise_'+itemId);
        $(totalElm).each(function(){
            var val = $(this).val();
            if (!val) val = 0;
            total += parseInt(val);
        });
        $('.colTotal_'+itemId).text(total);
    }


    return {
        ManageInit: ManageInit
    }
}();