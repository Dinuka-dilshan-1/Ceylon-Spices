CEYLON SPICE HUB - PROFESSIONAL PHP/MYSQL/XAMPP + PAYHERE CHECKOUT

PAYHERE
- Added PayHere Checkout API integration.
- Checkout now has Cash on Delivery, Bank Transfer and PayHere Online Payment.
- PayHere hash is generated server-side; the Merchant Secret is never sent to JavaScript.
- PayHere payment notifications are checksum-verified before an order is marked Paid.
- Stock is reduced only after a verified PayHere success notification (status_code=2).
- PayHere cancellation/failure does not reduce stock and the cart remains available for another attempt.
- PayHere return page reads payment status from the database instead of trusting browser parameters.

IMPORTANT PAYHERE SETUP
1. Open payhere_config.php.
2. PAYHERE_MODE is set to 'sandbox' for safe testing. Change to 'live' only after your PayHere Live account is ready.
3. PAYHERE_MERCHANT_ID is already configured with the Merchant ID supplied for this project.
4. PAYHERE_MERCHANT_SECRET is configured server-side. Do NOT place this secret in JavaScript or HTML.
5. PAYHERE_PUBLIC_BASE_URL currently points to the local project. PayHere cannot call a localhost notify_url.
   For real callback testing, use a public HTTPS URL/tunnel, for example an ngrok URL, and set:
   PAYHERE_PUBLIC_BASE_URL = 'https://YOUR-PUBLIC-URL';
6. In your PayHere account, add/approve the integrating domain/app and use the Merchant Secret generated for that exact domain.

DATABASE
A fresh installation:
1. Extract to C:\xampp\htdocs\Ceylon_Spice_Hub
2. Start Apache and MySQL.
3. Open http://localhost/phpmyadmin
4. Import database.sql.

If you want to keep an existing database:
- Import payhere_update.sql instead of dropping/recreating the whole database.

RUN
http://localhost/Ceylon_Spice_Hub/

PAYHERE FLOW
1. Login.
2. Add products to cart.
3. Checkout.
4. Select PayHere Online Payment.
5. Click PLACE ORDER.
6. The site creates a Pending order and redirects to PayHere.
7. PayHere processes the payment.
8. PayHere calls payhere_notify.php on the public URL.
9. The notification checksum is verified.
10. status_code=2 changes the order to Paid, records the payment and reduces stock.
11. The customer returns to payhere_return.php and sees the database-backed status.

SANDBOX TESTING
PayHere Sandbox simulates payments; it does not charge real money.
Official sandbox test cards include:
- Visa: 4916217501611292
- MasterCard: 5307732125531191
- AMEX: 346781005510225
For Name on Card, CVV and Expiry, PayHere says any valid data can be used in Sandbox.

LOCALHOST LIMITATION
PayHere documentation states that notify_url must be publicly accessible and cannot be tested directly on localhost. Therefore, the redirect/payment page can be tested locally, but the automatic database confirmation requires a public URL/tunnel.

SECURITY
The Merchant Secret is sensitive. Because it was provided during development, regenerate it in PayHere before using the site in a real production environment if it has been exposed outside your trusted development environment.
