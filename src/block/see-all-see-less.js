(function($){
  $('.see-all, .see-less').on('click', function() {
    let toggle = $(this).hasClass('see-all');
    let block = $(this).closest('.wp-block-klarity-klarity-cases-overview-block');
    block
      .find('.case-wrapper')
        .css({display: toggle ? 'initial' : ''});
    block
      .find('.see-all, .see-less')
        .toggleClass('hide');
  })
})(jQuery);
