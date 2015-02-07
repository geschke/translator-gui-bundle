   $(function () {

       
            $('.chooseLocaleReference').on('click', function () {
                localeReference = $(this).attr('data-locale');


                var identifier = $('#modalTranslate').children().find('#modal-edit-body').attr('data-identifier');
                var messageId = ($('#messageItems').children("[data-identifier='" + identifier + "']").attr('data-id'));
                // todo: check existence of identifier and messageId

               // var locale =  $('#form_locale').val();
                //var domain = $(this).attr('data-domain');
                var url = adminUrl + 'language-messageref/';

           
                $.ajax({
                    type: "GET",
                    url: url,
                    data: {bundle: bundle, locale: localeReference, domain: domain, messageId: messageId}
                })
                        .done(function (msg) {
                            if (msg.success == true) {
                                $('#form_message_reference').val(msg.messageTranslation.translation);
                                $('#localeReference').html(localeReference);
                            }
                          });

            });



            $('.btn-action-edit').click(function () {
                var modal = $('#modalTranslate');
                var messageId = $(this).parents('.message-item').attr('data-id');
                var identifier = $(this).parents('.message-item').attr('data-identifier');

                var locale = $(this).attr('data-locale');
                var domain = $(this).attr('data-domain');
                var url = adminUrl + 'language-translate/';
                $('#form_locale').val(locale);
                $('#form_message').val('');
                $('#form_message_reference').val('');
                $('#form_translation').val('');
                $('#modal-edit-body').attr('data-identifier', identifier);
                modal.modal('show');



                $.ajax({
                    type: "GET",
                    url: url,
                    data: {bundle: bundle, locale: locale, domain: domain, messageId: messageId}
                })
                        .done(function (msg) {
                            $('#form_message').val(msg.messageTranslation.message);
                            $('#form_message_reference').val(msg.messageTranslationReference.message);
                            $('#form_translation').val(msg.messageTranslation.translation);
                        });
            });

            $('.do-edit-action').click(function () {

                var modal = $('#modalTranslate');
                var url = adminUrl + 'language-translate/';
                var messageId = $('#form_message').val();
                var identifier = $('#modal-edit-body').attr('data-identifier');

                var data = {
                    bundle: bundle,
                    locale: $('#form_locale').val(),
                    domain: domain,
                    message: messageId,
                    translation: $('#form_translation').val()
                };

                $.ajax({
                    type: "POST",
                    url: url,
                    data: data
                })
                        .done(function (msg) {

                            if (msg.success == true) {
                                var message = msg.messageTranslation;
                                $('#translation_' + identifier).text(message.translation);
                            }
                            else {
                                $('#message_' + identifier).before('<div id="box-alert_' + identifier + '" class="col-md-12">' +
                                '<div id="alert_' + identifier + '" class="alert alert-danger alert-dismissible fade in">' +
                                '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">' + button_close + '</span></button>' + message_translation_save_error +
                                '</div></div>');
                                setTimeout(function () {
                                    $('#alert_' + identifier).alert('close');
                                }, 2500);

                                $('#alert_' + identifier).on('closed.bs.alert', function () {
                                    $('#box-alert_' + identifier).remove();
                                });
                            }
                        });

                modal.modal('hide');
            });


        });
