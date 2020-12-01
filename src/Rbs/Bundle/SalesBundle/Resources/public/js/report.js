
$('#dailyOrderReportExcel').click(function (url) {
    setTimeout(function(){
        $('#feed_order_report').attr('action', '');
        $('#feed_order_report').attr('method', 'get');
    }, 1000);

    $.ajax({
        form: 'feed_order_report',
        url: '/report/feed/order/excel',
        dataType: 'form html'
    });
});
