(function($){
    $(document).ready(function(){
        $('#ctm-add-client').on('click', function(e){
            e.preventDefault();
            var name = $('#ctm-new-client-name').val() || '';
            var email = $('#ctm-new-client-email').val() || '';
            var status = $('#ctm-add-client-status');
            status.text(ctm_client_quick.texts.creating);
            $.post(ctm_client_quick.ajax_url, {
                action: 'ctm_create_client',
                nonce: ctm_client_quick.nonce,
                name: name,
                email: email
            }, function(resp){
                if (resp && resp.success && resp.data && resp.data.id) {
                    var id = resp.data.id;
                    var title = resp.data.title || name || email;
                    // append option to select and select it
                    var opt = $('<option/>').val(id).text(title).prop('selected', true);
                    $('#ctm-clients-select').append(opt).trigger('change');
                    status.text('');
                    $('#ctm-new-client-name').val('');
                    $('#ctm-new-client-email').val('');
                } else {
                    status.text(ctm_client_quick.texts.create_failed);
                }
            }, 'json').fail(function(){
                status.text(ctm_client_quick.texts.create_failed);
            });
        });
    });
})(jQuery);
