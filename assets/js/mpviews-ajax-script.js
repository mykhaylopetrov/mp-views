jQuery(document).ready(function($) {
    // Function to increase the view counter via AJAX
    function increasePageViews(post_id, element) {
        $.ajax({
            url: mpviews_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'mpviews_increase_page_views',
                post_id: post_id,
                mpviews_nonce: mpviews_ajax_object.nonce
            },
            success: function(response) {
                console.log('Page views increased: ' + response);
                // Set the value of views on the corresponding element
                $(element).text(response);
                // We store browsing information in a cookie after successfully increasing the counter
                setPageViewedCookie(post_id);
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ' + error);
            }
        });
    }

    // Check if the user has already viewed the page
    function hasPageBeenViewed(post_id) {
        var viewedPages = getCookie('mp_views_viewed_pages');
        if (viewedPages) {
            var viewedArray = viewedPages.split(',');
            return viewedArray.includes(String(post_id));
        }
        return false;
    }

    // Setting cookies for the pages you view
    function setPageViewedCookie(post_id) {
        var viewedPages = getCookie('mp_views_viewed_pages');
        var viewedArray = viewedPages ? viewedPages.split(',') : [];
        if (!viewedArray.includes(String(post_id))) {
            viewedArray.push(post_id);
            setCookie('mp_views_viewed_pages', viewedArray.join(','), 7);
        }
    }

    // Getting a cookie value
    function getCookie(name) {
        var value = "; " + document.cookie;
        var parts = value.split("; " + name + "=");
        if (parts.length === 2) return parts.pop().split(";").shift();
    }

    // Setting the cookie value
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    // Check is a Post or Page
    if ($('body').hasClass('single') || $('body').hasClass('page')) {
        $('.mpviews__counter').each(function() {
            var postId = $(this).data('post-id');
            var counterElement = $(this).find('.mpviews__count');
            // Increase the counter if the page hasn't been viewed yet
            if (postId && !hasPageBeenViewed(postId)) {
                increasePageViews(postId, counterElement);
            }
        });
    }
});


/* Counter without cookies */

// jQuery(document).ready(function($) {
//     function increasePageViews(post_id, element) {
//         $.ajax({
//             url: mpviews_ajax_object.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'mpviews_increase_page_views',
//                 post_id: post_id,
//                 mpviews_nonce: mpviews_ajax_object.nonce
//             },
//             success: function(response) {
//                 console.log('Page views increased: ' + response);
//                 // Set the value of views on the corresponding element
//                 $(element).text(response);
//             },
//             error: function(xhr, status, error) {
//                 console.error('AJAX error: ' + error);
//             }
//         });
//     }

//     // Check is a Post or Page
//     if ($('body').hasClass('single') || $('body').hasClass('page')) {
//         $('.mpviews__counter').each(function() {
//             var postId = $(this).data('post-id');
//             var counterElement = $(this).find('.mpviews__count');
//             if(postId) {
//                 increasePageViews(postId, counterElement);
//             }
//         });
//     }
// });


