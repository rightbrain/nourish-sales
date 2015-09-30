$.xhrPool = [];
$.xhrPool.queue = [];
$.xhrPool.abortAll = function() {
    if (!$.xhrPool) {
        return;
    }
    $($.xhrPool.queue).each(function(idx, jqXHR) {
        jqXHR.abort();
    });
    $.xhrPool.queue = [];
};

$.ajaxSetup({
    beforeSend: function(jqXHR) {
        $.xhrPool.queue.push(jqXHR);
    },
    complete: function(jqXHR) {
        var index = $.xhrPool.indexOf(jqXHR);
        if (index > -1) {
            $.xhrPool.queue.splice(index, 1);
        }
    }
});

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
            var msg = $(this).attr('data-title') != '' ? $(this).attr('data-title') : 'Are you sure?';
            bootbox.confirm(msg, function(result) {
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

    function handleAjaxModal()
    {
        var modals = [];
        $('div.modal').each(function(index, modal){
            modals.push(modal);
        });

        if (!modals.length) return false;

        $(modals).on('hidden.bs.modal', function (e) {
            $(this).removeData('bs.modal');
            if ($(e.target).attr('role') == 'dialog') {
                return false;
            }
            $(this).find('.modal-content').html('<div class="modal-body">'+
                '<img src="/assets/global/img/loading-spinner-grey.gif" alt="" class="loading">' +
                '<span> &nbsp;&nbsp;Loading... </span></div>');
            try{
                $.xhrPool.abortAll();
            } catch(e){}
        });
    }

    function init()
    {
        initDeleteButton();
        initConfirmationButton();
        handleMultiSelect();
        handleAjaxModal();

        $('body').on('click', '.order-cancel-modal-action', function(){
            $('.main-content').slideUp();
            $('.action-content-cancel').slideDown();
        }).on('click', '.order-hold-modal-action', function(){
            $('.main-content').slideUp();
            $('.action-content-hold').slideDown();
        }).on('click', '.cancel-submit', function(){
            $('[class^=action-content]').slideUp();
            $('.main-content').slideDown();
        }).on('submit', '.action-content-form', function(){
            $(this).find('input[type=submit], button[type=submit]').attr('disabled', true);
        });

        if (jQuery().datepicker) {
            $('.date-picker, .input-daterange').datepicker({
                rtl: Metronic.isRTL(),
                //orientation: "left",
                autoclose: true
            });
            //$('body').removeClass("modal-open"); // fix bug when inline picker is used in modal
        }
    }

    return {
        init: init
    }
}();

function resolveCustomerName(data, type, row, meta)
{
    return row.fullName;
}
