/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2018 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

(function osdownloadsClosure($) {

    $.fn.osdownloads = function osdownloads(options) {
        if (this.length) {
            return this.each(function osdownloadsEachElement() {

                if ($(this).data('osdownloads-loaded') == 1) {
                    return;
                }

                var $this                 = $(this),
                    prefix                = $this.data('prefix'),
                    animation             = $this.data('animation'),
                    fieldsLayout          = $this.data('fields-layout'),
                    popupElementId        = prefix + '_popup',
                    $popup                = $('#' + popupElementId),
                    $btnContinue          = $popup.find('.osdownloads-continue-button'),
                    $errorAgreeTerms      = $popup.find('.osdownloads-error-agree'),
                    $errorInvalidEmail    = $popup.find('.osdownloads-error-email'),
                    $errorShare           = $popup.find('.osdownloads-error-share'),
                    $fieldAgree           = $popup.find('.osdownloads-field-agree'),
                    $fieldEmail           = $popup.find('.osdownloads-field-email'),
                    $groupEmail           = $popup.find('.osdownloads-email-group'),
                    $groupAgree           = $popup.find('.osdownloads-group-agree'),
                    $groupShare           = $popup.find('.osdownloads-group-share'),
                    directPage            = $this.data('direct-page'),
                    requireEmail          = $this.data('require-email'),
                    requireAgree          = $this.data('require-agree') == 1,
                    requireShare          = $this.data('require-share') == 1,
                    prefix                = $this.data('prefix'),
                    socialShared          = false,
                    $form                 = $popup.find('form');

                // Move the popup containers to the body
                $popup.appendTo($('body'));

                /*
                  Fix the click event on tabs, for supporting multiple popup boxes on the same page.
                  Without this fix, if 2 files have the same fieldset (represented by tabs), the
                  click event only work on the first popup, since the id of the elements will be
                  duplicated.
                 */
                // Find tabs and respective content containers, to update the ID for a unique value
                $tabs = $popup.find('.osdownloads-custom-fields-container ul.nav li a');

                if ($tabs.length > 0) {
                    $.each($tabs, function(index, elem) {
                        var $tab          = $(elem),
                            panelSelector = $tab.attr('href');
                            $panel        = $popup.find(panelSelector),
                            uniqueId      = $form.attr('id') + '-' + $panel.attr('id');

                        $tab.attr('href', '#' + uniqueId);
                        $panel.attr('id', uniqueId);
                    });
                }

                // Apply the correct layout for fields
                if ('block' === fieldsLayout) {
                    $tabs = $popup.find('.osdownloads-custom-fields-container ul.nav li a');

                    if ($tabs.length > 0) {
                        $.each($tabs, function(index, elem) {
                            var $elem = $(elem),
                                $panel = $($elem.attr('href'));

                            $title = $('<h3>')
                                .addClass('osdownloads-fieldset-title')
                                .text($elem.text());


                            $panel.before($title);
                            $panel.addClass('active');
                        });
                    }

                    $popup.find('.osdownloads-custom-fields-container ul.nav').remove();
                }

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

                    // Validate the form for custom fields before submitting
                    if ($form.length > 0) {
                        $form.attr('target', 'osdownloads-tmp-iframe-' + $form.attr('id'));

                        return document.formvalidator.isValid($form[0]);
                    } else {
                        return true;
                    }

                };

                var showPopup = function (selector) {
                    $(selector).reveal({
                        animation: animation,
                        animationspeed: 200,
                        closeonbackgroundclick: true,
                        dismissmodalclass: 'close-reveal-modal',
                    });

                    // Force to show the first tab of custom fields, if exists
                    window.setTimeout(
                        function () {
                            $popup.find('.osdownloads-custom-fields-container ul.nav li').first().find('a').trigger('click');
                        },
                        300
                    );
                };

                var download = function () {
                    var url = $this.attr('href');
                    $form.attr('target', 'osdownloads-tmp-iframe-' + $form.attr('id'));

                    // Create the popup element
                    $container = $('<div>')
                        .attr('id', prefix + 'PopupIframe')
                        .addClass('reveal-modal')
                        .addClass('osdownloads-modal');

                    $iframe = $('<iframe>').attr('name', 'osdownloads-tmp-iframe-' + $form.attr('id'));
                    $iframe.iframeAutoHeight({
                        heightOffset: 10
                    });
                    $close = $('<a class="close-reveal-modal">&#215;</a>');

                    $iframe.appendTo($container);
                    $close.appendTo($container);
                    $container.appendTo($('body'));

                    // Submit the form
                    $form.submit();

                    // Close the requirements popup
                    $container.on('reveal:close', function() {
                        setTimeout(function timeoutRemoveIframePopup() {
                            $container.remove();
                        }, 500);
                    });
                    $popup.trigger('reveal:close');

                    setTimeout(function timeoutShowPopup() {
                        showPopup('#' + prefix + 'PopupIframe');
                    }, 500);
                };

                $this.on('click', function downloadBtnOnClick(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var requireShare = $(this).data('require-share');

                    if (requireEmail || requireAgree || requireShare) {
                        if (requireEmail != 0) {
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

                        showPopup('#' + popupElementId);

                        $popup.on(
                            'reveal:close',
                            function requirementsRevealOnClose() {
                                // Clean fields
                                $fieldEmail.val('');
                                $fieldAgree.attr('checked', false);
                                $('.osdownloads-modal .error').hide();
                                $groupShare.children('.twitter-share-button').remove();
                                $groupShare.children('.fb-like').remove();
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

                $this.data('osdownloads-loaded', 1);
            });
        }
    };
})(jQuery);

