CEYLON SPICE HUB - PROFESSIONAL PHP + MYSQL + XAMPP VERSION

WHAT WAS UPDATED
- Fixed the incorrect MySQL port in db.php: normal XAMPP uses localhost:3306.
- Rebuilt the homepage with a more professional e-commerce layout.
- Kept all local image assets so the site does not depend on external image URLs.
- Added responsive mobile navigation and mobile-friendly product grid.
- Added live search and category filtering.
- Added product sorting.
- Added wishlist UI.
- Added MySQL-backed Add to Cart.
- Added quantity + / - controls and remove item.
- Added professional cart summary.
- Added checkout form.
- Added order placement into orders/order_items/payments tables.
- Added stock reduction after a successful order.
- Added order confirmation page.
- Added newsletter subscription storage.
- Added clearer database error message.
- Added safer prepared SQL statements.

INSTALLATION
1. Extract this folder to:
   C:\xampp\htdocs\Ceylon_Spice_Hub

2. Open XAMPP Control Panel.
   Start Apache.
   Start MySQL.

3. Open:
   http://localhost/phpmyadmin

4. Click Import and select:
   database.sql

5. After importing, check that the database "ceylon_spice_hub" exists.

6. Open:
   http://localhost/Ceylon_Spice_Hub/

DATABASE SETTINGS
Host: localhost
Port: 3306
Database: ceylon_spice_hub
Username: root
Password: empty

IMPORTANT
If MySQL in your XAMPP is configured to a different port, change only $port in db.php.

FILES
index.php             - professional homepage
db.php                - MySQL/PDO connection
database.sql          - database + sample data
add_to_cart.php       - add product to cart
update_cart.php       - update/remove cart items
cart.php              - shopping cart
checkout.php          - checkout form
place_order.php       - create order and reduce stock
order_success.php     - order confirmation
subscribe.php         - newsletter subscription
script.js             - search/filter/cart UI
style.css             - complete responsive styling
images/               - local website images

TEST FLOW
Home -> Shop -> Add to Cart -> Cart -> Change Quantity -> Checkout
-> Enter details -> Place Order -> Order Confirmation

NOTE
This coursework version includes Cash on Delivery and Bank Transfer as payment selections. It does NOT connect to a live payment gateway such as PayHere or Stripe.
