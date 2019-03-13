function showVideo(elem, link) {
  let iframe = jQuery('<iframe>', {
    src: link + '?muted=0&autoplay=1&loop=1',
    webkitallowfullscreen: true,
    mozallowfullscreen: true,
    allowfullscreen:true,
    frameborder: 0,
    scrolling: 'no'
  });

  jQuery(elem)
      .append(iframe)
      .children(".thumbnail-container").remove();

  if (typeof ga === "function") {
    ga('send', 'event', 'show-video', 'vimeo', link);
  }
}

