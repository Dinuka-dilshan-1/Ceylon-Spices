# Week 06 Update – Ceylon Spice Hub

Implemented from the supplied Week 06 practical: registration, bcrypt password hashing, login/logout, session state, protected profile/checkout routes, profile dashboard, editable contact/address details, password change, and guest-cart association on authentication.

## API
- POST `api/register.php` – registration endpoint
- Login/logout are handled through the web session lifecycle.

## Security
- Passwords use PHP `password_hash(..., PASSWORD_BCRYPT)`.
- Login returns a generic invalid-credentials message.
- Session ID is regenerated after login/logout.
- CSRF tokens are used on forms.

## Test flow
Register → Login → Profile → Edit Details → Add to Cart → Checkout → Place Order → Logout.
