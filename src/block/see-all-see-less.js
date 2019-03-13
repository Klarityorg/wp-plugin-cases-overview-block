const blockClassName = '.wp-block-klarity-klarity-cases-overview-block';

(function($){
  $.fn.stickifyElementWithSelector = function(selector) {
    let seeLessEl = this.find(selector);
    if (seeLessEl.length) {
      let blockTop = this.offset().top;
      let scrollPositionTop = $(window).scrollTop();
      let endOfBlockTop = blockTop + this.height();
      let scrollPositionBottom = scrollPositionTop + $(window).height() - 30; // Includes top and bottom paddings
      let hasReachedBlockTop = scrollPositionTop > blockTop;
      let hasReachedBlockBottom = scrollPositionBottom > endOfBlockTop;
      seeLessEl.toggleClass('sticky', hasReachedBlockTop && !hasReachedBlockBottom);
    }
  };

  $('.see-all, .see-less').on('click', function() {
    let toggle = $(this).hasClass('see-all');
    let block = $(this).closest(blockClassName);
    block
      .find('.case-wrapper')
        .css({display: toggle ? 'initial' : ''});
    block
      .find('.see-all, .see-less')
        .toggleClass('hide');

    if (toggle) {
      block.stickifyElementWithSelector('.see-less');
    }
  });

  $(window).on('scroll', function() {
    $(blockClassName).each(function() {
      $(this).stickifyElementWithSelector('.see-less');
    });
  });

})(jQuery);
