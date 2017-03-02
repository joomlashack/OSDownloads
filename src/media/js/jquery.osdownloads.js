(function osdownloadsClosure($) {

    $(function osdownloadsDomReady() {
        // Move the popup containers to the body
        $('.osdownloads-modal').appendTo($('body'));
    });

    $.fn.osdownloads = function osdownloads(options) {
        var defaults = {
            animation: 'fade',
            elementsPrefix: 'osdownloads',
            popupElementId: 'osdownloadsRequirementsPopup'
        };

        var options = $.extend({}, defaults, options);

        if (this.length) {
            return this.each(function osdownloadsEachElement() {
                var $this                 = $(this),
                    $btnContinue          = $('#' + options.elementsPrefix + 'DownloadContinue'),
                    $popup                = $('#' + options.popupElementId),
                    $errorAgreeTerms      = $('#' + options.elementsPrefix + 'ErrorAgreeTerms'),
                    $errorInvalidEmail    = $('#' + options.elementsPrefix + 'ErrorInvalidEmail'),
                    $errorShare           = $('#' + options.elementsPrefix + 'ErrorShare'),
                    $fieldAgree           = $('#' + options.elementsPrefix + 'RequireAgree'),
                    $fieldEmail           = $('#' + options.elementsPrefix + 'RequireEmail'),
                    $groupEmail           = $('#' + options.elementsPrefix + 'EmailGroup'),
                    $groupAgree           = $('#' + options.elementsPrefix + 'AgreeGroup'),
                    $groupShare           = $('#' + options.elementsPrefix + 'ShareGroup'),
                    $requiredEmailMessage = $('#' + options.elementsPrefix + 'RequiredEmailMessage'),
                    $requiredShareMessage = $('#' + options.elementsPrefix + 'RequiredShareMessage'),
                    directPage            = $this.data('direct-page'),
                    requireEmail          = $this.data('require-email'),
                    requireAgree          = $this.data('require-agree') == 1,
                    requireShare          = $this.data('require-share') == 1,
                    socialShared          = false;

                var isValidForm = function () {
                    var emailRegex = /^([A-Za-z0-9_\-\.\+])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,25})$/,
                        errorElement = null,
                        hasError = false,
                        requireShare = $this.data('require-share') == 1;

                    if (requireAgree) {
                        if (! $fieldAgree.is(':checked')) {
                            hasError = true;
                            $errorAgreeTerms.show();
                        } else {
                            $errorAgreeTerms.hide();
                        }
                    }

                    if (requireEmail == 1) {
                        var email = $fieldEmail.val().trim();

                        if (email === '' || ! emailRegex.test(email)) {
                            hasError = true;
                            $errorInvalidEmail.show();
                        } else {
                            $errorInvalidEmail.hide();
                        }
                    } else {
                        if (requireEmail == 2) {
                            var email = $fieldEmail.val().trim();

                            if (email != '' && ! emailRegex.test(email)) {
                                hasError = true;
                                $errorInvalidEmail.show();
                            } else {
                                $errorInvalidEmail.hide();
                            }
                        }
                    }

                    if (requireShare) {
                        if (!socialShared) {
                            hasError = true;
                            $errorShare.show();
                        } else {
                            $errorShare.hide();
                        }
                    }

                    if (hasError) {
                        return false;
                    }

                    return true;
                };

                var showPopup = function (selector) {
                    $(selector).reveal({
                         animation: options.animation,
                         animationspeed: 200,
                         closeonbackgroundclick: true,
                         dismissmodalclass: 'close-reveal-modal',
                    });
                };

                var goToDirectPage = function () {
                    if (directPage) {
                        window.location = directPage;
                    }
                };

                var addQueryVarToUri = function(url, variable, value) {
                    if (url.indexOf('?') > -1) {
                        url += '&';
                    } else {
                        url += '?';
                    }

                    url += variable + '=' + value;

                    return url;
                };

                var download = function () {
                    var url = $this.attr('href');

                    if ($fieldEmail.length > 0) {
                        url = addQueryVarToUri(url, 'email', encodeURIComponent($fieldEmail.val().trim()));
                    }

                    if ($fieldAgree.length > 0) {
                        value = ($fieldAgree.is(':checked') ? 1 : 0);
                        url = addQueryVarToUri(url, 'agree', value);
                    }

                    // Create the popup element
                    $container = $('<div>')
                        .attr('id', options.elementsPrefix + 'PopupIframe')
                        .addClass('reveal-modal')
                        .addClass('osdownloads-modal');

                    $iframe = $('<iframe>').attr('src', url);
                    $iframe.iframeAutoHeight({
                        heightOffset: 10
                    });
                    $close = $('<a class="close-reveal-modal">&#215;</a>');

                    $iframe.appendTo($container);
                    $close.appendTo($container);
                    $container.appendTo($('body'));

                    $container.on('reveal:close', function() {
                        setTimeout(function timeoutRemoveIframePopup() {
                            $container.remove();
                        }, 500);
                    });

                    // Close the requirements popup
                    $popup.trigger('reveal:close');

                    setTimeout(function timeoutShowPopup() {
                        showPopup('#' + options.elementsPrefix + 'PopupIframe');
                    }, 500);
                };

                $this.on('click', function downloadBtnOnClick(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var requireShare = $(this).data('require-share');

                    if (requireEmail || requireAgree || requireShare) {
                        if (requireEmail != 0) {
                            if (requireEmail == 1) {
                                $requiredEmailMessage.show();
                            } else {
                                $requiredEmailMessage.hide();
                            }

                            $groupEmail.show();
                        } else {
                            $groupEmail.hide();
                        }

                        if (requireAgree) {
                            // Update the requirement article url
                            $groupAgree.find('.agreement-article').attr('href', $this.data('agreement-article'));
                            $groupAgree.show();
                        } else {
                            $groupAgree.hide();
                        }

                        if (requireShare) {
                            $groupShare.show();
                            socialShared = false;

                            $requiredShareMessage.show();

                            // Create the tweet button
                            var $btn = $('<a>')
                                .addClass('twitter-share-button')
                                .attr('href', 'https://twitter.com/share')
                                .attr('data-lang', $(this).data('lang'))
                                .attr('data-url', $(this).data('url'))
                                .attr('data-count', 'none')
                                .attr('data-hashtags', $(this).data('hashtags'))
                                .attr('data-via', $(this).data('via'))
                                .attr('data-text', $(this).data('text'))
                                .text('Tweet');
                            $errorShare.before($btn);
                            twttr.widgets.load();

                            // Create the facebook button
                            $btn = $('<div>')
                                .addClass('fb-like')
                                .attr('data-href', $(this).data('url'))
                                .attr('data-layout', 'button')
                                .attr('data-action', 'like')
                                .attr('data-show-faces', 'false')
                                .attr('data-share', 'false');
                            $errorShare.before($btn);

                            FB.init({
                                status : true,
                                cookie : true,
                                xfbml  : true
                            });

                        } else {
                            $groupShare.hide();
                        }

                        $btnContinue.attr('href', $this.attr('href'));

                        showPopup('#' + options.popupElementId);

                        $popup.on(
                            'reveal:close',
                            function requirementsRevealOnClose() {
                                // Clean fields
                                $fieldEmail.val('');
                                $fieldAgree.attr('checked', false);
                                $('.osdownloads-modal .error').hide();
                                $groupShare.children('.twitter-share-button').remove();
                                $groupShare.children('.fb-like').remove();
                                $requiredShareMessage.hide();
                            }
                        );

                        $btnContinue.off();
                        $btnContinue.on('click', function continueBtnOnClick(event) {
                            event.preventDefault();

                            if (isValidForm()) {
                                download();
                            }
                        });
                    } else {
                        download();
                    }
                });

                // Add the social buttons
                if (requireShare) {
                    window.twttr = (function(d,s,id){var t,js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id)){return}js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);return window.twttr||(t={_e:[],ready:function(f){t._e.push(f)}})}(document,"script","twitter-wjs"));

                    var twttrInterval;
                    twttrInterval = setInterval(function() {
                        if (window.twttr) {
                            clearInterval(twttrInterval);
                            twttrInterval = null;

                            twttr.ready(function(twttr) {
                                window.twttr.events.bind('tweet',
                                    function (event) {
                                        socialShared = true;
                                        $errorShare.hide();
                                    }
                                );
                            });
                        }
                    }, 300);

                    // Add the Facebook button
                    window.fbAsyncInit = function() {
                        FB.init({
                            status : true,
                            cookie : true,
                            xfbml  : true
                        });
                        // Like
                        FB.Event.subscribe('edge.create', function(response) {
                            socialShared = true;
                            $errorShare.hide();
                        });
                        // Unlike
                        FB.Event.subscribe('edge.remove', function(url, elem) {
                            socialShared = false;
                            $errorShare.show();
                        });
                        // Comment
                        FB.Event.subscribe('comment.create', function(response) {
                            socialShared = true;
                            $errorShare.hide();
                        });
                    };

                    if (!document.getElementById('facebook-jssdk')) {
                        var js, fjs = document.getElementsByTagName('script')[0];
                        js = document.createElement('script');
                        js.id = 'facebook-jssdk';
                        js.src = "//connect.facebook.net/en_US/all.js";
                        fjs.parentNode.insertBefore(js, fjs);
                    }
                }
                $this.data('configured', 1);
            });
        }
    };
})(jQuery);
