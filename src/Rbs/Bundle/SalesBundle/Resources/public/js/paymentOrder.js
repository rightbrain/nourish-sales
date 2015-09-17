var paymentOrder = function()
{
    var $collectionHolder;
    var $addTagLink = $('#add_order');
    var $newLinkLi = $('<tr></tr>').append($addTagLink);

    jQuery(document).ready(function() {
        $collectionHolder = $('tbody.tags');
        $collectionHolder.append($newLinkLi);
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $addTagLink.on('click', function(e) {
            e.preventDefault();
            addTagForm($collectionHolder, $newLinkLi);
        });
    });

    function addTagForm($collectionHolder, $newLinkLi) {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        var newForm = prototype.replace(/__name__/g, index);

        $collectionHolder.data('index', index + 1);

        var $newFormLi = $('<tr></tr>').append(newForm);
        $newLinkLi.after($newFormLi);
    }

}();