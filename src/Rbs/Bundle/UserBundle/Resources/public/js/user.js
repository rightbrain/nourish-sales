var Area = function()
{
    function areaChangeAction()
    {
        $('select.zilla-selector, select.thana-selector').on('change', function(e){
            e.preventDefault();

            var level = $(this).hasClass('zilla-selector') ? 3 : 4;
            var appendTo = level === 3 ? 'thana-selector' : 'union-selector';
            var param = 'id='+$(this).val();
            if (level === 3) {
                $('select.union-selector, select.thana-selector').select2('data', null);
            } else {
                $('select.union-selector').select2('data', null);
            }

            if (!$(this).val()) {
                return;
            }
            var el = $(this).closest(".portlet").children(".portlet-body");
            Metronic.blockUI({
                target: el,
                animate: true,
                overlayColor: 'black'
            });

            $.ajax({
                url: Routing.generate('location_filter'),
                data: param,
                success: function(html){
                    $("select."+appendTo).html(html).select2('val', '');
                    Metronic.unblockUI(el);
                },
                error: function(){
                    Metronic.alert('Server Error');
                    Metronic.unblockUI(el);
                }
            });
        });
    }

    function userOrAgent()
    {
        $("#user_userType").change(function () {
            if(this.value == 'AGENT'){
                document.getElementById("user_level2").disabled = false;
                document.getElementById("user_level3").disabled = false;
            }else {
                document.getElementById("user_level2").disabled = true;
                document.getElementById("user_level3").disabled = true;
            }
        });
    }

    function init()
    {
        areaChangeAction();
        userOrAgent();
    }

    return {
        init: init
    }
}();

Area.init();