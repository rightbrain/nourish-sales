var App = function() {

    function initDeleteButton()
    {
        $('body').on('click', '.delete-list-btn', function(){
            var url = $(this).attr('href');
            bootbox.confirm("Are you sure?", function(result) {
                if (result) {
                    var deleteForm = $('form#delete-form');
                    if (deleteForm.length) {
                        deleteForm.attr('action', url).submit();
                    } else {
                        document.location.href = url;
                    }
                }
            });

            return false;
        });
    }

    function initConfirmationButton()
    {
        $('body').on('click', '.confirmation-btn', function(){
            var url = $(this).attr('href');
            bootbox.confirm("Are you sure?", function(result) {
                if (result) {
                    document.location.href = url;
                }
            });

            return false;
        });
    }

    var handleMultiSelect = function() {
        if (!$().multiSelect) {
            return;
        }
        $("select[multiple=multiple]").multiSelect({selectableOptgroup: true});
    };

    function init()
    {
        initDeleteButton();
        initConfirmationButton();
        handleMultiSelect();
    }

    return {
        init: init
    }
}();