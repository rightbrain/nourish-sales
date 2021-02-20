
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

$('#dailyOrderReportRegionWiseExcel').click(function (url) {
    setTimeout(function(){
        $('#feed_order_report_region_wise').attr('action', '');
        $('#feed_order_report_region_wise').attr('method', 'get');
    }, 1000);

    $.ajax({
        form: 'feed_order_report_region_wise',
        url: '/report/region/feed/order/excel',
        dataType: 'form html'
    });
});
