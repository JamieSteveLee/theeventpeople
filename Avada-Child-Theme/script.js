(function($) {
    /*
    Business 'Request A Callback' form
    */
    let isBusinessPage = $('body').hasClass('single-businesses');
    if(isBusinessPage) {
        // cf7 hidden field names
        let hidBusinessName = $('[name="business-name"]');
        let hidBusinessEmail = $('[name="business-email"]');

        // Get business details from the page
        let businessName = $('h1.fusion-title-heading').text();

        // Populate hidden fields with business details
        hidBusinessName.val(businessName);

        let businessEmail = $('h6 a[href^="mailto:"]').attr('href').replace('mailto:', '');
        if (businessEmail) hidBusinessEmail.val(businessEmail);
    }
}(jQuery));
