(function( jQuery ) {
    var matched,
        userAgent = navigator.userAgent || "";

    // Use of jQuery.browser is frowned upon.
    // More details: http://api.jquery.com/jQuery.browser
    // jQuery.uaMatch maintained for back-compat
    jQuery.uaMatch = function( ua ) {
        ua = ua.toLowerCase();

        var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
            /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
            /(opera)(?:.*version)?[ \/]([\w.]+)/.exec( ua ) ||
            /(msie) ([\w.]+)/.exec( ua ) ||
            ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+))?/.exec( ua ) ||
            [];

        return {
            browser: match[ 1 ] || "",
            version: match[ 2 ] || "0"
        };
    };

    matched = jQuery.uaMatch( userAgent );

    jQuery.browser = {};

    if ( matched.browser ) {
        jQuery.browser[ matched.browser ] = true;
        jQuery.browser.version = matched.version;
    }

    // Deprecated, use jQuery.browser.webkit instead
    // Maintained for back-compat only
    if ( jQuery.browser.webkit ) {
        jQuery.browser.safari = true;
    }

}( jQuery ));

/*jslint white: true, indent: 2, onevar: false, browser: true, undef: true, nomen: false, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, strict: false, newcap: true, immed: true */
/*global window, console, jQuery, setTimeout */

/*
  Plugin: iframe autoheight jQuery Plugin
  Version: 1.9.5
  Author and Contributors
  ========================================
  NATHAN SMITH (http://sonspring.com/)
  Jesse House (https://github.com/house9)
  aaron manela (https://github.com/aaronmanela)
  Hideki Abe (https://github.com/hideki-a)
  Patrick Clark (https://github.com/hellopat)
  ChristineP2 (https://github.com/ChristineP2)
  Mmjavellana (https://github.com/Mmjavellana)
  yiqing-95 (https://github.com/yiqing-95)
  jcaspian (https://github.com/jcaspian)
  adamjgray (https://github.com/adamjgray)
  Jens Bissinger (https://github.com/dpree)
  jbreton (https://github.com/jbreton)
  mindmelting (https://github.com/mindmelting)

  File: jquery.iframe-auto-height.plugin.js
  Remarks: original code from http://sonspring.com/journal/jquery-iframe-sizing
  Description: when the page loads set the height of an iframe based on the height of its contents
  see README: http://github.com/house9/jquery-iframe-auto-height

*/
(function ($) {
  $.fn.iframeAutoHeight = function (spec) {

    var undef;
    if ($.browser === undef) {
      var message = [];
      message.push("WARNING: you appear to be using a newer version of jquery which does not support the $.browser variable.");
      message.push("The jQuery iframe auto height plugin relies heavly on the $.browser features.");
      message.push("Install jquery-browser: https://raw.github.com/house9/jquery-iframe-auto-height/master/release/jquery.browser.js");
      alert(message.join("\n"));
      return $;
    }

    // set default option values
    var options = $.extend({
        heightOffset: 0,
        minHeight: 0,
        maxHeight: 0,
        callback: function (newHeight) {},
        animate: false,
        debug: false,
        diagnostics: false, // used for development only
        resetToMinHeight: false,
        triggerFunctions: [],
        heightCalculationOverrides: []
      }, spec);

    // logging
    function debug(message) {
      if (options.debug && options.debug === true && window.console) {
        console.log(message);
      }
    }

    // not used by production code
    function showDiagnostics(iframe, calledFrom) {
      debug("Diagnostics from '" + calledFrom + "'");
      try {
        debug("  " + $(iframe, window.top.document).contents().find('body')[0].scrollHeight + " for ...find('body')[0].scrollHeight");
        debug("  " + $(iframe.contentWindow.document).height() + " for ...contentWindow.document).height()");
        debug("  " + $(iframe.contentWindow.document.body).height() + " for ...contentWindow.document.body).height()");
      } catch (ex) {
        // ie fails when called during for each, ok later on
        // probably not an issue if called in a document ready block
        debug("  unable to check in this state");
      }
      debug("End diagnostics -> results vary by browser and when diagnostics are requested");
    }

    // show all option values
    debug(options);

    // ******************************************************
    // iterate over the matched elements passed to the plugin ; return will make it chainable
    return this.each(function () {

      // ******************************************************
      // http://api.jquery.com/jQuery.browser/
      var strategyKeys = ['webkit', 'mozilla', 'msie', 'opera'];
      var strategies = {};
      strategies['default'] = function (iframe, $iframeBody, options, browser) {
        // NOTE: this is how the plugin determines the iframe height, override if you need custom
        return $iframeBody[0].scrollHeight + options.heightOffset;
      };

      jQuery.each(strategyKeys, function (index, value) {
        // use the default strategy for all browsers, can be overridden if desired
        strategies[value] = strategies['default'];
      });

      // override strategies if registered in options
      jQuery.each(options.heightCalculationOverrides, function (index, value) {
        strategies[value.browser] = value.calculation;
      });

      function findStrategy(browser) {
        var strategy = null;

        jQuery.each(strategyKeys, function (index, value) {
          if (browser[value]) {
            strategy = strategies[value];
            return false;
          }
        });

        if (strategy === null) {
          strategy = strategies['default'];
        }

        return strategy;
      }
      // ******************************************************

      // for use by webkit only
      var loadCounter = 0;

      // Fix issue with unloaded iframes
      // Customisation by Joomlashack
      if (this.contentDocument == null && this.contentWindow == null) {
        return false;
      }

      var iframeDoc = this.contentDocument || this.contentWindow.document;

      // resizeHeight
      function resizeHeight(iframe) {
        if (options.diagnostics) {
          showDiagnostics(iframe, "resizeHeight");
        }

        // set the iframe size to minHeight so it'll get smaller on resizes in FF and IE
        if (options.resetToMinHeight && options.resetToMinHeight === true) {
          iframe.style.height = options.minHeight + 'px';
        }

        // get the iframe body height and set inline style to that plus a little
        var $body = $(iframe, window.top.document).contents().find('body');
        var strategy = findStrategy($.browser);
        var newHeight = strategy(iframe, $body, options, $.browser);
        debug(newHeight);

        if (newHeight < options.minHeight) {
          debug("new height is less than minHeight");
          newHeight = options.minHeight;
        }

        if (options.maxHeight > 0 && newHeight > options.maxHeight) {
          debug("new height is greater than maxHeight");
          newHeight = options.maxHeight;
        }

        newHeight += options.heightOffset;

        debug("New Height: " + newHeight);
        if (options.animate) {
          $(iframe).animate({height: newHeight + 'px'}, {duration: 500});
        } else {
          iframe.style.height = newHeight + 'px';
        }

        options.callback.apply($(iframe), [{newFrameHeight: newHeight}]);
      } // END resizeHeight

      // debug me
      debug(this);
      if (options.diagnostics) {
        showDiagnostics(this, "each iframe");
      }

      // if trigger functions are registered, invoke them
      if (options.triggerFunctions.length > 0) {
        debug(options.triggerFunctions.length + " trigger Functions");
        for (var i = 0; i < options.triggerFunctions.length; i++) {
          options.triggerFunctions[i](resizeHeight, this);
        }
      }

      // Check if browser is Webkit (Safari/Chrome) or Opera
      if ($.browser.webkit || $.browser.opera || $.browser.chrome) {
        debug("browser is webkit or opera");

        // Start timer when loaded.
        $(this).load(function () {
          var delay = 0;
          var iframe = this;

          var delayedResize = function () {
            resizeHeight(iframe);
          };

          if (loadCounter === 0) {
            // delay the first one
            delay = 500;
          } else {
            // Reset iframe height to 0 to force new frame size to fit window properly
            // this is only an issue when going from large to small iframe, not executed on page load
            iframe.style.height = options.minHeight + 'px';
          }

          debug("load delay: " + delay);
          setTimeout(delayedResize, delay);
          loadCounter++;
        });

        // Safari and Opera need a kick-start.
        var source = $(this).attr('src');
        $(this).attr('src', '');
        $(this).attr('src', source);
      } else {
        // For other browsers.
        if(iframeDoc.readyState  === 'complete') {
          resizeHeight(this);
        } else {
          $(this).load(function () {
            resizeHeight(this);
          });
        }
      } // if browser

    }); // $(this).each(function () {
  }; // $.fn.iframeAutoHeight = function (options) {
}(jQuery)); // (function ($) {

/*
 * jQuery Reveal Plugin 1.0
 * www.ZURB.com
 * Copyright 2010, ZURB
 * Free to use under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
*/


(function ($) {
  $('a[data-reveal-id]').on('click', function (event) {
    event.preventDefault();
    var modalLocation = $(this).attr('data-reveal-id');
    $('#' + modalLocation).reveal($(this).data());
  });

  $.fn.reveal = function (options) {
    var defaults = {
      animation: 'fadeAndPop',                // fade, fadeAndPop, none
      animationSpeed: 300,                    // how fast animtions are
      closeOnBackgroundClick: true,           // if you click background will modal close?
      dismissModalClass: 'close-reveal-modal' // the class of a button or element that will close an open modal
    };
    var options = $.extend({}, defaults, options);

    return this.each(function () {
      var modal    = $(this),
        topMeasure = parseInt(modal.css('top')),
        topOffset  = modal.height() + topMeasure,
        locked     = false,
        modalBg    = $('.reveal-modal-bg');

      if (modalBg.length == 0) {
        modalBg = $('<div class="reveal-modal-bg" />').insertAfter(modal);
        modalBg.fadeTo('fast', 0.8);
      }

      function openAnimation() {
        modalBg.unbind('click.modalEvent');
        $('.' + options.dismissModalClass).unbind('click.modalEvent');
        if (!locked) {
          lockModal();
          if (options.animation == "fadeAndPop") {
            modal.css({'top': $(document).scrollTop() - topOffset, 'opacity': 0, 'visibility': 'visible'});
            modalBg.fadeIn(options.animationSpeed / 2);
            modal.delay(options.animationSpeed / 2).animate({
              "top": $(document).scrollTop() + topMeasure + 'px',
              "opacity": 1
            }, options.animationSpeed, unlockModal);
          }
          if (options.animation == "fade") {
            modal.css({'opacity': 0, 'visibility': 'visible', 'top': $(document).scrollTop() + topMeasure});
            modalBg.fadeIn(options.animationSpeed / 2);
            modal.delay(options.animationSpeed / 2).animate({
              "opacity": 1
            }, options.animationSpeed, unlockModal);
          }
          if (options.animation == "none") {
            modal.css({'visibility': 'visible', 'top': $(document).scrollTop() + topMeasure});
            modalBg.css({"display": "block"});
            unlockModal();
          }
        }
        modal.unbind('reveal:open', openAnimation);
      }
      modal.bind('reveal:open', openAnimation);

      function closeAnimation() {
        if (!locked) {
          lockModal();
          if (options.animation == "fadeAndPop") {
            modalBg.delay(options.animationSpeed).fadeOut(options.animationSpeed);
            modal.animate({
              "top":  $(document).scrollTop() - topOffset + 'px',
              "opacity": 0
            }, options.animationSpeed / 2, function () {
              modal.css({'top': topMeasure, 'opacity': 1, 'visibility': 'hidden'});
              unlockModal();
            });
          }
          if (options.animation == "fade") {
            modalBg.delay(options.animationSpeed).fadeOut(options.animationSpeed);
            modal.animate({
              "opacity" : 0
            }, options.animationSpeed, function () {
              modal.css({'opacity': 1, 'visibility': 'hidden', 'top': topMeasure});
              unlockModal();
            });
          }
          if (options.animation == "none") {
            modal.css({'visibility': 'hidden', 'top': topMeasure});
            modalBg.css({'display': 'none'});
          }
        }
        modal.unbind('reveal:close', closeAnimation);
      }
      modal.bind('reveal:close', closeAnimation);
      modal.trigger('reveal:open');

      var closeButton = $('.' + options.dismissModalClass).bind('click.modalEvent', function () {
        modal.trigger('reveal:close');
      });

      if (options.closeOnBackgroundClick) {
        modalBg.css({"cursor": "pointer"});
        modalBg.bind('click.modalEvent', function () {
          modal.trigger('reveal:close');
        });
      }

      $('body').keyup(function (event) {
        if (event.which === 27) { // 27 is the keycode for the Escape key
          modal.trigger('reveal:close');
        }
      });

      function unlockModal() {
        locked = false;
      }

      function lockModal() {
        locked = true;
      }
    });
  };
})(jQuery);

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


