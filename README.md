# MediQuick Pharmacy

An online pharmacy web application for customers in Kurunegala, Sri Lanka. Customers can browse medicines, upload prescriptions and place orders. Pharmacy staff verify prescriptions, manage inventory and process orders. Administrators manage staff accounts and oversee the system.

---

## Features

**Customer**
- Register, log in, and manage profile (name, phone, address, password)
- Browse products with text search and category filter
- Cart and checkout (simulated payment: Card or Cash on Delivery)
- Upload prescriptions (JPG / PNG / PDF) and track their status
- View order history and status
- Contact the pharmacy via enquiry form

**Staff**
- Dashboard with low-stock counter
- Verify prescriptions (approve / reject with optional note)
- Manage inventory (add / edit products, price, stock, product image)
- Update order status (confirmed → shipped → delivered)
- Reply to customer enquiries

**Admin**
- Dashboard with revenue, order counts, and customer/staff counts
- Create staff accounts
- Change user roles

**Security and data integrity**
- PDO prepared statements (SQL injection protection)
- Passwords hashed with `password_hash()` / `password_verify()`
- Role-based access control (`require_login()`, `require_role($role)` in `includes/auth.php`)
- Server-side validation and `htmlspecialchars()` output escaping (XSS protection)
- Upload validation by real MIME type (`mime_content_type()`) and size; `.htaccess` blocks PHP execution in the uploads folder
- Database transactions for order placement (`beginTransaction()` / `rollBack()`)
- Checkout blocked for prescription-only items until a prescription is approved

---

## Tech Stack

| Layer | Technology |
| --- | --- |
| Frontend | HTML5, CSS3 (single hand-written stylesheet, CSS Grid) |
| Backend | PHP 8+ with PDO and sessions |
| Database | MySQL (phpMyAdmin) |
| Local server | XAMPP (Apache + MySQL) |

No JavaScript frameworks or CSS frameworks are used.

---

## Requirements

- XAMPP with PHP 8.0 or later (tested on PHP 8.2)
- Apache and MySQL modules running
- A modern web browser

---

## Installation

1. **Copy the project** into the XAMPP web root:
   ```
   C:\xampp\htdocs\mediquick-pharmacy\
   ```
   (Use whatever folder name your project has. The URL below must match it.)

2. **Start XAMPP.** Open the XAMPP Control Panel and start **Apache** and **MySQL**.

3. **Create the database.**
   - Open http://localhost/phpmyadmin
   - Click **New** and create a database named `mediquick` (utf8mb4_general_ci)
   - Select it, go to **Import**, and choose `database/mediquick.sql`
   - Click **Go**

4. **Check the database connection.** Open the connection file in `includes/` and make sure the settings match your XAMPP setup. XAMPP defaults:
   ```php
   host: localhost
   dbname: mediquick
   user: root
   password: (empty)
   ```

5. **Check the uploads folder.** Make sure `uploads/` exists and is writable (it stores prescriptions and product images).

6. **Open the site:**
   ```
   http://localhost/mediquick-pharmacy/
   ```

---

## Demo Accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@mediquick.test` | `Admin@123` |
| Staff | `staff@mediquick.test` | `rahiq123` |
| Customer | `customer@mediquick.test` | `rushdi123` |

> The passwords are from the project report. Confirm the email addresses against the `users` table in the imported database and edit this table if they differ. These are demo credentials for local testing only. Change them for any real deployment.

---

## Project Structure

```
mediquick-pharmacy/
├── index.php               Homepage
├── public/                 Public pages (shop, product detail, login, register, contact)
├── customer/               Customer area (cart, checkout, orders, profile, prescriptions)
├── staff/                  Staff area (dashboard, prescriptions, inventory, orders, enquiries)
├── admin/                  Admin area (dashboard, manage staff and users)
├── includes/               Shared code (header.php, footer.php, auth.php, DB connection)
├── uploads/                Uploaded prescriptions and product images
├── assets/
│   ├── css/style.css       Single stylesheet (all colours defined here)
│   ├── js/
│   └── images/
└── database/
    └── mediquick.sql       Database schema and sample data
```

> Adjust this tree if your folder layout differs slightly.

---

## Database

Seven tables in `database/mediquick.sql`:

| Table | Purpose |
| --- | --- |
| `users` | All accounts (customer, staff, admin), identified by a role column |
| `categories` | Product categories |
| `products` | Medicines and wellness items (price, stock, prescription-required flag, `image_path`) |
| `prescriptions` | Uploaded prescriptions with status: pending / approved / rejected |
| `orders` | Order header, order status, and separate `payment_status` |
| `order_items` | One row per product in an order |
| `enquiries` | Customer enquiries and staff replies (open / answered) |

---

## Usage Guide

**Buying an over-the-counter item (customer)**
Shop → Add to Cart → Cart → Checkout → choose payment method → Place Order.

**Buying a prescription item (customer)**
Add to cart → **Upload Prescription** → wait for staff approval → return to checkout → select the approved prescription → Place Order.

**Verifying a prescription (staff)**
Log in → **Verify Prescriptions** → Approve or Reject (optional note).

**Creating a staff account (admin)**
Log in → **Manage Staff & Users** → fill in the Create Staff Account form → Submit.

---

## Testing

Twelve manual test cases (TC01–TC12) cover registration, login, search and filter, the prescription gate at checkout, upload validation, role-based access, stock decrement, role changes and enquiries. All passed. Reset the database by re-importing `database/mediquick.sql` before each test run for repeatable results.

---

## Known Limitations

- Payment is **simulated**. No real payment gateway is integrated. `checkout.php` is structured so a gateway such as PayHere can be added later (hosted card page and callback updating `payment_status`).
- No email or SMS notifications. Customers check status manually under My Orders and Upload Prescription.
- Testing is manual. Automated tests around the prescription-to-checkout flow are planned future work.

---





Academic coursework project. Not intended for production use.

