  $(function () {
            $('.btn-action-language-add').click(function () {
                var options = {};
                bundle = $(this).parents('.bundle-item').attr('data-name');
                window.location = adminUrl + 'language-add/?bundle=' + bundle;
            });

            $('.btn-action-copy').click(function () {
                var options = {};
                bundle = $(this).parents('.bundle-item').attr('data-name');

            
                var locale = $(this).attr('data-locale');
                var domain = $(this).attr('data-domain');
                $('#languagechoice_locale_additional').val('');

                $('#modal-copy-body').attr('data-domain', domain);
                $('#modal-copy-body').attr('data-bundle', bundle);
                $('#modal-copy-body').attr('data-locale', locale);

                $('#modalCopy').modal(options);

            });

            $('.do-copy-action').click(function() {
                var modal = $('#modalCopy');

                var localeChosen = $("input[name='languagechoice[locale]']:checked").val();
                var localeAdditional = $('#languagechoice_locale_additional').val();
                if (localeChosen == undefined || (localeChosen != 'misc' && localeAdditional) || (localeChosen == 'misc' && !localeAdditional)) {
                    
                    $('#alertCopyError').html('<div id="box-alert_copy_error" >' +
                    '<div id="alert_copy_error" class="alert alert-danger alert-dismissible fade in">' +
                    '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">' + button_close + '</span></button>' +
                    locale_copy_alert_message + '</div></div>');
                    setTimeout(function () {
                        $('#alert_copy_error').alert('close');
                    }, 2500);
                    $('#alert_copy_error').on('closed.bs.alert', function () {
                        $('#box-alert_copy_error').remove();
                    });

                    return;
                }
               
                
                
                var domain = $('#modal-copy-body').attr('data-domain');
                var bundle = $('#modal-copy-body').attr('data-bundle');
                var locale = $('#modal-copy-body').attr('data-locale');
                if (localeChosen == 'misc') {
                    localeChosen = localeAdditional;
                }

              
                modal.modal('hide');
                window.location = adminUrl + 'language-copy/?bundle=' + bundle + '&locale_from=' + locale + '&locale_to=' + localeChosen + '&domain=' + domain;
            });

            $('.btn-action-delete').click(function () {
                var options = {};
                bundle = $(this).parents('.bundle-item').attr('data-name');
                locale = $(this).attr('data-locale');
                $('#deleteLocale').html(locale);
                $('#modalDelete').modal(options);

            });

            $('.do-delete-action').click(function () {
                $('#modalDelete').modal('hide');
                var deleteBundle = bundle;
                var deleteLocale = locale;
                bundle = undefined;
                locale = undefined;
                window.location = adminUrl + 'language-delete/?bundle=' + deleteBundle + '&locale=' + deleteLocale;
            });

            $('.btn-action-edit').click(function () {
                var options = {};
                bundle = $(this).parents('.bundle-item').attr('data-name');
                locale = $(this).attr('data-locale');
                domain  = $(this).attr('data-domain');
                window.location = adminUrl + 'language-edit/?bundle=' + bundle + '&locale=' + locale + '&domain=' + domain;

            });
            $('.btn-action-rescan').click(function () {
                var modal = $('.js-loading-bar');
                bundle = $(this).parents('.bundle-item').attr('data-name');
                locale = $(this).attr('data-locale');
                var url = adminUrl + 'language-rescan/?bundle=' + bundle + '&locale=' + locale;

                $('.rescan-ready').hide();
                $('.rescan-success').hide();
                $('.rescan-error').hide();
                $('.rescan-loading').show();

                modal.modal('show');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {bundle: bundle, locale: locale}
                })
                        .done(function (msg) {
                            $('.rescan-ready').show();
                            $('.rescan-loading').hide();
                            if (msg.success == true) {
                                $('.rescan-success').show();
                                $('.rescan-error').hide();

                            } else {
                                $('.rescan-success').hide();
                                $('.rescan-error').show();
                            }
                        });

            });

        });
