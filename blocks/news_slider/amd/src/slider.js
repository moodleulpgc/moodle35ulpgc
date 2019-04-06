/* jshint ignore:start */
define(['jquery', 'block_news_slider/slick', 'core/log'], function($, slick, log) {

    "use strict"; // ... jshint ;_;.

    log.debug('news_slider slider.js function called');

    return {
        init: function(showdots) {
            $(document).ready(function($) {

                $('.responsive').not('.slick-initialized').slick({
                    dots: showdots,
                    speed: 300,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 5000,
                    responsive: [
                      {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                    },
                      {
                            breakpoint: 600,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                    },
                      {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                    }
                        // You can unslick at a given breakpoint now by adding:
                        // settings: "unslick"
                        // instead of a settings object.
                    ]
                  });

            });
        }
    };
});


/* jshint ignore:end */
