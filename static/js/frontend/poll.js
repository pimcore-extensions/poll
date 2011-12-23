/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */
(function( $ ) {
    $.fn.poll = function() {
        return this.each(function(){
            var _this = $(this);
            var _form = _this.find('form');
            var _answers = _form.find('input[name="answer[]"]');
            var _responses = _this.find('.responses');

            var _showResult = function(type){
                $.ajax({
                    type: type,
                    url: _form.attr('action'),
                    data: _form.serialize(),
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR){
                        _this.find('.loader').fadeOut('fast');
                        _form.slideUp('fast');
                        _responses.append(data.responses);

                        _responses.find('.bar').each(function(){
                            $(this).progressbar({
                                value: $(this).data('percent')
                            }).find('.ui-progressbar-value').addClass('ui-corner-right');
                        });

                        _responses.slideDown('fast');
                    },
                    error: function() {
                        _this.find('.loader').fadeOut('fast');
                        _form
                            .css({opacity:1})
                            .find('button')
                            .removeAttr('disabled');
                        _this.find('.error.request').show();
                    }
                });
            }

            _form.submit(function(e){
                e.preventDefault();
                if(!_answers.filter(':checked').length) {
                    e.preventDefault();
                    _this.find('.error.invalid').show();
                    return;
                }

                _this.find('.error').hide();
                _form
                    .css({opacity:0.3})
                    .find('button')
                    .attr('disabled', 'disabled');
                _this
                    .css({position:'relative'})
                    .find('.loader').fadeIn('fast').css({
                        position: 'absolute',
                        top: _form.height() / 2,
                        left: _form.width() / 2 - 32
                    });

                _showResult('post');
            });

            if(_this.data('voted')) {
                _form.hide();
                _showResult('get');
            }
        });
    };
})(jQuery);

$(document).ready(function(){
    $('.poll-container').poll();
});