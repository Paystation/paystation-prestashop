# Paystation payment module for Prestashop

This integration is currently only tested up to Prestashop 1.6.0.6

## Requirements
* An account with [Paystation](https://www2.paystation.co.nz/).

## Installation

These instructions will guide you through installing the module and conducting a test transaction. 

1. Login into your Prestashop administration pages

2. Select 'Modules' from the Modules menu. 

3. Click 'Add a new module' (on the right of the page). An extra panel headed 'Add a new module' will appear.

4. In the box labelled "Module file", select the name of this ZIP file, and click "Upload this module". 

5. The message "The module was downloaded successfully." will appear. Scroll down to the Modules List, and select "Payments and Gateways". 
The Paystation module should appear in the right-hand panel.

6. Click the 'Install' button for the Prestashop module.

7. The message 'Module(s) installed successfully' will appear, and below that boxes for your Paystation settings.

8. Set 'Paystation ID' to the Paystation ID provided. 

9. Set 'Gateway ID' to the Gateway ID provided. 

Note: In steps 8 and 9, be careful not to leave trailing and leading spaces in the input fields.

10. Put some text in 'Display Label'. This is the text that the Paystation Payment Gateway method is
labelled as on the checkout page. For example 'Pay by Visa or Mastercard'.

11. Check the 'Enable test mode' check box. 

12. Click 'Update Paystation Settings'. The page will reload and appear exactly the same.

13. You may need to change the module restriction settings - to do this, select 'Payment' from the Modules menu.
Note that although the Paystation module will not appear on the 'Modules List' on this page, it will appear under the 'Payment module restrictions' heading. For information on setting Payment module restrictions, refer to the Prestashop documentation.

14. The return URL is: {host}/{Prestashop_directory}/index.php?fc=module&module=paystation&controller=validation

For example - www.yourwebsite.co.nz/prestshop/index.php?fc=module&module=paystation&controller=validation

The Postback URL is: {host}/{Prestashop_directory}/index.php?fc=module&module=paystation&controller=postback

For example - www.yourwebsite.co.nz/prestshop/index.php?fc=module&module=paystation&controller=postback

Send both the return and postback URLs to <support@paystation.co.nz> with your Paystation ID and request your Return URL to be updated.
Also send your IP address to <support@paystation.co.nz>, as this module uses the Remote Lookup (Quick) Interface API and is IP limited.

15. Go to your online store. 

16. To do a successful test transaction make a purchase where the final cost will have the cent value set to .00, for example $1.00, this will return a successful test transaction.
To do an unsuccessful test transaction make a purchase where the final cost will have the cent value set to anything other than .00, for example $1.01-$1.99, this will return an unsuccessful test transaction. 

Important: You can only use the test Visa and Mastercards supplied by Paystation for test transactions.
They can be found here [Visit the Test Card Number page](https://www2.paystation.co.nz/developers/test-cards/). 

17. When you go to checkout - make sure you choose Paystation Payment Gateway in the Payment method section. 

18. If everything works ok, go back to the 'List of modules' page, find the Paystation module, and click the Configure link.

19. Uncheck the 'Enable test mode' check box, and click 'Update Paystation Settings'.

Fill in the form found on https://www2.paystation.co.nz/go-live so that Paystation can test and set your account into Production Mode. 

20. Congratulations - you can now process online credit card payments

### Notes
* Orders with successful payments will have a status of 'Payment accepted'.
* Orders with unsuccessful payments will have a status of 'Payment error'.

