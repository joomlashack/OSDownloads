/**
 * @package   OSDownloads
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2005-2022 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSDownloads.
 *
 * OSDownloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSDownloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSDownloads.  If not, see <http://www.gnu.org/licenses/>.
 */

;(function osdownloadsClosure($) {
    $.fn.osdownloads = function osdownloads(options) {
        if (this.length) {
            return this.each(function osdownloadsEachElement() {
                if ($(this).data('osdownloads-loaded') === 1) {
                    return;
                }

                let $this              = $(this),
                    prefix             = $this.data('prefix'),
                    animation          = $this.data('animation'),
                    popupElementId     = prefix + '_popup',
                    $popup             = $('#' + popupElementId),
                    $btnContinue       = $popup.find('.osdownloads-continue-button'),
                    $errorAgreeTerms   = $popup.find('.osdownloads-error-agree'),
                    $errorInvalidEmail = $popup.find('.osdownloads-error-email'),
                    $fieldAgree        = $popup.find('.osdownloads-field-agree'),
                    $fieldEmail        = $popup.find('.osdownloads-field-email'),
                    $groupEmail        = $popup.find('.osdownloads-email-group'),
                    $groupAgree        = $popup.find('.osdownloads-group-agree'),
                    requireEmail       = $this.data('require-email'),
                    requireAgree       = $this.data('require-agree') === 1,
                    $form              = $popup.find('form');

                // Move the popup containers to the body
                $popup.appendTo($('body'));

                let isValidForm = function() {
                    let email      = $fieldEmail.val().trim(),
                        emailRegex = /^([A-Za-z0-9_\-.+])+@([A-Za-z0-9_\-.])+\.([A-Za-z]{2,25})$/,
                        hasError   = false;

                    if (requireAgree) {
                        if ($fieldAgree.is(':checked')) {
                            $errorAgreeTerms.hide();

                        } else {
                            hasError = true;
                            $errorAgreeTerms.show();
                        }
                    }

                    switch (requireEmail) {
                        case 1:
                            // email required
                            if (email === '' || !emailRegex.test(email)) {
                                hasError = true;
                                $errorInvalidEmail.show();

                            } else {
                                $errorInvalidEmail.hide();
                            }
                            break;

                        case 2:
                            // email optional
                            if (email !== '' && !emailRegex.test(email)) {
                                hasError = true;
                                $errorInvalidEmail.show();

                            } else {
                                $errorInvalidEmail.hide();
                            }
                            break;
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

                let showPopup = function(selector) {
                    $(selector).reveal({
                        animation             : animation,
                        animationspeed        : 200,
                        closeonbackgroundclick: true,
                        dismissmodalclass     : 'close-reveal-modal',
                    });
                };

                let download = function() {
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

                    if (requireEmail || requireAgree) {
                        if (requireEmail !== 0) {
                            $groupEmail.show();

                        } else {
                            $groupEmail.hide();
                        }

                        if (requireAgree) {
                            $groupAgree.find('.agreement-article').attr('href', $this.data('agreement-article'));
                            $groupAgree.show();

                        } else {
                            $groupAgree.hide();
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

                $this.data('osdownloads-loaded', 1);
            });
        }
    };
})(jQuery);

