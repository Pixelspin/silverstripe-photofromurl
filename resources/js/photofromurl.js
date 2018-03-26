(function ($) {
  $.entwine('ss', function ($) {

    $('.photofromurl-button').entwine({

      onmatch: function () {

        var form = this.closest('form');
        var href = this.attr('href');

        this.on('click', function (e) {
          e.preventDefault();
          if(form.hasClass('changed')){
            alert('Save your changes first!');
          }else{
            var url = window.prompt('URL');
            $.getJSON(href + url, function(response){
              if(response.success){
                window.location.reload();
              }else{
                alert(response.message);
              }
            });
          }
        });

      }

    });

  });

})(jQuery);
