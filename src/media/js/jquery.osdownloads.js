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
                    $form              = $popup.find('form');

                if ($popup.length !== 1 && $form.length !== 1) {
                    return;
                }

                // Move the popup containers to the body
                $popup.appendTo($('body'));

                $form.validate({
                    submitHandler: function(form) {
                        download();
                    }
                });

                let showPopup = function(selector) {
                    $(selector).reveal({
                        animation             : animation,
                        animationspeed        : 200,
                        closeonbackgroundclick: true,
                        dismissmodalclass     : 'close-reveal-modal',
                    });
                };

                let download = function() {
                    $form.prop('target', 'osdownloads-tmp-iframe-' + $form.prop('id'));

                    // Create the popup element
                    $container = $('<div>')
                        .prop('id', prefix + 'PopupIframe')
                        .addClass('reveal-modal')
                        .addClass('osdownloads-modal');

                    $iframe = $('<iframe>').prop('name', 'osdownloads-tmp-iframe-' + $form.prop('id'));
                    $close  = $('<a class="close-reveal-modal">&#215;</a>');

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

                    $btnContinue.prop('href', $this.prop('href'));

                    showPopup('#' + popupElementId);

                    $popup.on(
                        'reveal:close',
                        function requirementsRevealOnClose() {
                            $form[0].reset();
                        }
                    );

                    $btnContinue.off();
                    $btnContinue.on('click', function continueBtnOnClick(event) {
                        event.preventDefault();

                        $form.submit();
                    });
                });

                $this.data('osdownloads-loaded', 1);
            });
        }
    };
})(jQuery);

