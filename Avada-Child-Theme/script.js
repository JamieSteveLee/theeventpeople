(function($) {
    /*
    Business 'Request A Callback' form
    */
    let isBusinessPage = $(body).hasClass('single-businesses');
    if(isBusinessPage) {
        // cf7 hidden field names
        let hidBusinessName = $('[name="business-name"]');
        let hidBusinessEmail = $('[name="business-email"]');

        // Get business details from the page
        let businessName = $('h1.fusion-title-heading').text();

        // Populate hidden fields with business details
        hidBusinessName.val(businessName);

        let businessEmail = $('a[href^="mailto:"]').attr('href'); // Find the mailto link
        if (businessEmail) {
            businessEmail = businessEmail.replace('mailto:', ''); // Remove 'mailto:' prefix
            hidBusinessEmail.val(businessEmail);
        }
    }
}(jQuery));
