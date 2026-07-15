GardenHub Authentication Setup
=============================

This project now supports a simple manual account-creation flow for Admin, Staff, and Customer accounts.
There is no public sign-up page for Admin or Staff, so accounts can be created manually in the users table.

Default password for manually created accounts
-------------------------------------------
Use this password for any manually created admin, staff, or user account when you want a known starting password:

- Default login password: 11223344

Its bcrypt hash is:
- $2y$12$HPDSVrQs2fh7OPzqwETSfOW33ozi24ZEqJiZ1nMRUQIxhsWlADu8W

Copy that hash into the password column when creating a new account manually.

How the first-login password change flow works
--------------------------------------------
- When a user signs in with the default password, the app detects that the account should change its password.
- The user is redirected to a Change Password page.
- The user must set a new password before reaching the dashboard.
- A flag named must_change_password is stored in the users table to track this requirement.

Admin account setup
-------------------
1. Make sure the database is properly configured in your .env file.
2. Run:
   php artisan migrate
3. Open the users table.
4. Insert or update a row with the following values:
   - name: Admin User
   - email: admin@example.com
   - password: $2y$12$HPDSVrQs2fh7OPzqwETSfOW33ozi24ZEqJiZ1nMRUQIxhsWlADu8W
   - role: admin
   - status: active
   - user_type: admin
   - must_change_password: 1
5. Save the row.
6. Log in with:
   - Email: admin@example.com
   - Password: 11223344
7. You will be redirected to the Change Password page. Set a new password to continue.

Staff account setup
-------------------
Follow the same process, but set:
- role: staff
- user_type: staff
- status: active
- must_change_password: 1

Then log in with:
- Email: your-staff-email@example.com
- Password: 11223344

Customer account setup
----------------------
Customers can also be created manually with:
- role: customer
- user_type: customer
- status: active
- must_change_password: 1

Then log in with:
- Email: customer@example.com
- Password: 11223344

SQL example for an admin account
--------------------------------
INSERT INTO users (
    name,
    email,
    password,
    role,
    status,
    user_type,
    must_change_password,
    created_at,
    updated_at
) VALUES (
    'Admin User',
    'admin@example.com',
    '$2y$12$HPDSVrQs2fh7OPzqwETSfOW33ozi24ZEqJiZ1nMRUQIxhsWlADu8W',
    'admin',
    'active',
    'admin',
    1,
    NOW(),
    NOW()
);

Database client example (DBeaver, phpMyAdmin, etc.)
----------------------------------------------------
1. Connect to the same database used by your Laravel app.
2. Open the users table.
3. Click Insert / Add Row.
4. Fill in the fields shown above.
5. Save the row.

Forgot password setup
----------------------
The project now includes forgot-password pages for Admin, Staff, and Customer accounts.
To make password reset emails work, configure the mail settings in your .env file, for example:

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="GardenHub"

After that, users can visit:
- /admin/forgot-password
- /staff/forgot-password
- /customer/forgot-password

Additional prerequisites
-----------------------
- Run php artisan migrate after pulling the latest code.
- Make sure the application uses the correct database connection.
- Make sure your mail configuration is correct for password reset emails.
- If login does not work, verify that the email, password hash, role, status, and must_change_password values are correct.
