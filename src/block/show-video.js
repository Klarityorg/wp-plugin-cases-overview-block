function showVideo(elem, link) {
  var iframe = jQuery('<iframe>', {
    src: link + '?muted=0&autoplay=1&loop=1',
    width: '100%',
    height: jQuery(".thumbnail-container").height(),
    webkitallowfullscreen: true,
    mozallowfullscreen: true,
    allowfullscreen:true,
    frameborder: 0,
    scrolling: 'no'
  });

  jQuery(elem)
      .append(iframe)
      .children(".thumbnail-container").remove();

  if (typeof ga === "function")
  {
    ga('send', 'event', 'show-video', 'vimeo', link);
  }
}

