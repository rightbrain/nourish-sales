handleCustomerChange = function (){ROLE_PAYMENT_VIEW
    $("#payment_customer").change(function () {
        var ordersElm = $('#payment_orders');
        var customer = $(this).val();
        if (customer == '') {
            ordersElm.find('option').remove();
            return fales;
        }
        $.ajax({
            type: "post",
            url: Routing.generate('partial_payment_orders', {id: customer}),
            dataType: 'json',
            success: function(data) {
                ordersElm.find('option').remove();
                for (var i=0; i < data.length; i++) {
                    var arr = data[i];
                    ordersElm.append('<option value="'+arr['id']+'">'+arr['text']+'</option>');
                }
            },
            error: function(){
                toastr.error('Server Error');
            }
        });
    });
}();