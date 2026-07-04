# TODO for Adding CAPTCHA and Email OTP Verification to Registration

## Plan Overview
1. Add Google reCAPTCHA v2 checkbox to the registration form in `resources/views/auth/register.blade.php`.
2. Validate CAPTCHA response in `RegisteredUserController@store`.
3. Implement email OTP verification before final user registration:
   - Modify registration flow to store unverified user data temporarily with OTP.
   - Send OTP email to the user's email.
   - Create a new verification form to input OTP.
   - Verify OTP and then finalize the user registration after OTP confirmation.

## Detailed Steps
- [ ] Update registration blade form to include reCAPTCHA widget.
- [ ] Update `RegisteredUserController@store` to:
  - Validate reCAPTCHA response from Google API.
  - Upon valid CAPTCHA, generate OTP.
  - Save OTP and user data temporarily (e.g., session or a temporary table).
  - Send OTP to user email.
  - Redirect to OTP verification form.
- [ ] Create OTP verification view and route.
- [ ] Create OTP verification controller method to:
  - Validate OTP input.
  - On success, finalize the user creation and login.
  - On failure, return error and retry option.
- [ ] Add necessary mail template for OTP email.
- [ ] Test full flow: registration with CAPTCHA, OTP verification, and user creation.

## Dependencies and Considerations
- Add Google reCAPTCHA site and secret keys to `.env`.
- Use Laravel Mail for sending OTP emails.
- Consider security and expiration of OTP codes.

## Follow-up steps after code implementation
- Run tests on registration, CAPTCHA validation, OTP email sending, and verification.
- Ensure UX with proper error messages and UI states.
