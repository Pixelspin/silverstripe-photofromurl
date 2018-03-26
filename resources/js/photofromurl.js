(function ($) {
  $.entwine('ss', function ($) {

    $('.photofromurl-button').entwine({

      onmatch: function () {

        var _this = this;
        var form = this.closest('form');
        var href = this.attr('href');
        var originalHTML = this.html();

        this.on('click', function (e) {
          e.preventDefault();
          if(_this.hasClass('loading')){
            return false;
          }
          if (form.hasClass('changed')) {
            alert('Save your changes first!');
          } else {
            var url = window.prompt('URL');
            if (url) {
              _this.addClass('btn--loading loading');
              _this.css('width', _this.outerWidth() + 'px');
              _this.css('height', _this.outerHeight() + 'px');

              _this.html($(
                '<div class="btn__loading-icon">'+
                '<span class="btn__circle btn__circle--1" />'+
                '<span class="btn__circle btn__circle--2" />'+
                '<span class="btn__circle btn__circle--3" />'+
                '</div>'));
              $.getJSON(href + url, function (response) {
                if (response.success) {
                  window.location.reload();
                } else {
                  _this.removeClass('btn--loading loading');
                  _this.html(originalHTML);
                  alert(response.message);
                }
              });
            }
          }
        });

      }

    });

  });

})(jQuery);
