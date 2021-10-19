var Payment = function()
{
    function filterInit(allowAgentSearch){

        if (!$('#external_filter_container').length) {
            $('<div id="external_filter_container">' +
                '<div id="district-filter"></div>' +
                '<div id="date-filter"><div class="input-group input-daterange">' +
                '<input type="text" class="form-control input-small" value="">' +
                '<span class="input-group-addon">to</span>' +
                '<input type="text" class="form-control input-small" value="" style="margin-left: 0">' +
                '</div></div>' +
                '<div id="agent-filter"></div>' +
                '<button class="btn green pull-right payment-filter-btn">Filter</button>' +
                '</div>').appendTo('#payment_datatable_filter');
        }

        var table = $('#payment_datatable').DataTable();

        // Bangla hack to fix datatable break layout
        $(window).trigger('resize');
        $(window).trigger('resize');

        $('.input-daterange').datepicker({
            autoclose: true,
            todayBtn: "linked",
            format: 'dd-mm-yyyy'
        });

        if (allowAgentSearch) {
            $("#district-filter").select2({
                placeholder: "District",
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: Routing.generate('location_search'),
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            q: term
                        };
                    },
                    results: function (data) {
                        return {results: data};
                    },
                    cache: true
                }
            }).on('change', function(){
                //table.columns(1).search($(this).val()).draw();
            }).prev().addClass('form-control input-medium').css('vertical-align', 'initial');

        }
        if (allowAgentSearch) {
            $("#agent-filter").select2({
                placeholder: "Agents",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: Routing.generate('agent_search'),
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            q: term
                        };
                    },
                    results: function (data) {
                        return {results: data};
                    },
                    cache: true
                }
            }).on('change', function(){
                //table.columns(1).search($(this).val()).draw();
            }).prev().addClass('form-control input-medium').css('vertical-align', 'initial');

        }

        // Filter Button Action - Filter Payment
        $('.payment-filter-btn').on('click', function(){
            var startDate = $('#date-filter input:eq(0)').datepicker("getDate");
            var endDate = $('#date-filter input:eq(1)').datepicker("getDate");

            var fromDate = moment($('#date-filter input:eq(0)').datepicker("getDate")).format('DD-MM-YYYY');
            var toDate = moment($('#date-filter input:eq(1)').datepicker("getDate")).format('DD-MM-YYYY');

            if (startDate && endDate) {
                table.columns(0).search(fromDate+'--'+toDate);
            } else {
                table.columns(0).search('');
            }
            if (allowAgentSearch) {
                table.columns(1).search($("#agent-filter").val());
                table.columns(3).search($("#district-filter").val());
            }
            table.draw();
        });

        var orderFilterContainer = $('#payment_datatable_filter');
        // Add class to select to match with theme
        orderFilterContainer.find('select').addClass("form-control");

        // Remove global search box
        orderFilterContainer.addClass('pull-right').find('label').remove();
        // Remove Individual Filter Inputs
        $('.dataTables_scrollHead').find('table thead tr').eq(1).remove();
    }

    function handleAgentChange()
    {
        /*if (!$("#payment_agent").length) {
            return;
        }*/
        $("#payment_agent").change(function () {
            var ordersElm = $('#payment_orders');
            var payment_agentBankBranch = $('#payment_agentBankBranch');
            var payment_nourishBankAccount = $('#payment_bankAccount');
            var agent = $(this).val();
            if (agent=='') {
                // ordersElm.find('option').remove();
                return false;
            }
            $.ajax({
                type: "post",
                url: Routing.generate('partial_payment_orders', {id: agent}),
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

            $.ajax({
                type: "post",
                url: Routing.generate('agent_bank_by_agent_id', {id: agent}),
                dataType: 'json',
                success: function(data) {
                    payment_agentBankBranch.find('option').remove();
                    for (var i=0; i < data.length; i++) {
                        var arr = data[i];
                        payment_agentBankBranch.append('<option value="'+arr['id']+'">'+arr['text']+'</option>');
                    }
                },
                error: function(){
                    toastr.error('Server Error');
                }
            });

            $.ajax({
                type: "post",
                url: Routing.generate('nourish_bank_by_agent_id', {id: agent}),
                dataType: 'json',
                success: function(data) {
                    payment_nourishBankAccount.find('option').remove();
                    for (var i=0; i < data.length; i++) {
                        var arr = data[i];
                        payment_nourishBankAccount.append('<option value="'+arr['id']+'">'+arr['text']+'</option>');
                    }
                },
                error: function(){
                    toastr.error('Server Error');
                }
            });
        }).change();

        $("#payment_bank").change(function () {
            var bankId = $(this).val();
            var branchId= jQuery("#payment_branch").val();
            if(bankId==''){
                var dataOption='<option value="">Select Branch</option>';
                jQuery("#payment_branch").html(dataOption).select2();
                return false;
            }
            console.log(bankId);
            $.ajax({
                type: "get",
                url: Routing.generate('branch_by_bank', {
                    id: bankId,
                }),
                dataType: 'json',
                success: function (response) {
                    var dataOption='<option value="">Select Branch</option>';
                    jQuery.each(response, function(i, item) {
                        if(branchId==item.id){
                            var selected= 'selected="selected"'
                        }else{
                            var selected='';
                        }
                        dataOption += '<option value="'+item.id+'" '+selected+'>'+item.name+'</option>';
                    });

                    jQuery("#payment_branch").html(dataOption).select2();
                }
            });
        }).change();
    }

    function init()
    {
        handleAgentChange();
    }

    return {
        init: init,
        filterInit: filterInit
    }
}();