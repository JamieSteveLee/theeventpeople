# The Event People Child Theme
Avada Child Theme for The Event People

---

## Business 'Request A Callback' form
A single contact form can be reused on all business pages, with the business name and email address automatically populated from the page, and submissions sent directly to the business contact email address.

### Requirements:
1. The following fields should be added to the Contact Form 7 form (updating the default email address):
    - `[hidden business-name default:"Name not found"]`
    - `[hidden business-email default:"[DEFAULT EMAIL ADDRESS]"]`
2. In the **Mail** settings of the Contact Form 7 contact form:
    - Set the **To** address to **[business-email]**. (Ignore the warning from CF7 "Invalid mailbox syntax is used.")
3. Only works on Business pages (checks for the `<body>` class `single-businesses`)

### Functionality
On page load, if the page is a single business page, the hidden fields are populated:
- **business-name** - uses the `h1` page title from the page
- **business-email** - uses the first `mailto:` link on the page


## List My Business form
When a user submits the List My Business form, a Business page will be created using the details submitted by the user. This new page will be in draft.

### Adding/changing fields
If a field is added to the Avada form, the function `create_business_from_avada_form_submission` in `functions.php` will need to be updated.

Some useful links for amending this function:
- https://developer.wordpress.org/reference/functions/wp_insert_post/
- https://www.advancedcustomfields.com/resources/update_field/