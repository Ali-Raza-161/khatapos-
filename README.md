# KhataPOS: POS and Ledger System for Local Shops (PHP and Laravel)

Prepared by Ali Raza | Version 1.0

> **Demo login (demo only):** Email `demo@khatapos.local`, password `demo123` (owner role).
> A second demo account, cashier `cashier@khatapos.local` / `demo123`, is also seeded.
> Create your own owner account from Settings, then remove the demo accounts.
> No real shop data is included; all demo data is fictional.

## 1. Project Overview

KhataPOS is a web-based Point of Sale (POS) and accounting system for grocery and
general stores in Pakistan. It combines fast billing, stock management and the
traditional customer credit ledger in one place. The shop owner can create bills,
track stock, record customer and supplier ledgers, note expenses and see daily
sales and profit reports. The system is built with PHP and the Laravel framework.

## 2. Problem Statement

Most small shops still keep credit records in a notebook and calculate profit by
guessing. This causes forgotten or disputed credit amounts, stock running out
without warning, wasted time during billing and no clear idea of real profit.
Large POS software is expensive and often too complicated for a small shopkeeper.
KhataPOS offers a simple, affordable and local-shop-friendly solution.

## 3. Objectives

- Make billing fast using product search or barcode scanning.
- Keep accurate stock levels and warn when items run low.
- Maintain a clear ledger for every customer and supplier.
- Record shop expenses and calculate real profit.
- Print bills on normal or 80mm thermal printers.
- Provide daily, monthly and product-wise reports with CSV export.
- Give different access to owner, manager and cashier.
- Keep the system ready to be sold as a monthly subscription (SaaS) in the future.

## 4. Scope

In scope: products and categories, purchases, sales (POS), customer and supplier
ledgers, expenses, reports, user roles, receipt printing, backup, support tickets.
Out of scope (future work): multi-branch support, online store, automatic tax
authority integration, mobile app, accounting for large companies.

## 5. User Roles

| Role | Description | Main permissions |
|---|---|---|
| Owner | Shop owner | Everything: users, settings, reports, profit, deletion |
| Manager | Trusted staff | Products, purchases, expenses, customers, reports (without profit) |
| Cashier | Counter staff | Create bills, receive customer payments, view stock |

## 6. Features

### 6.1 Products and stock
Add products with name, category, barcode, unit (piece, kg, dozen, packet, litre),
purchase price, sale price and opening stock. Low stock alert level for each
product. Optional expiry date tracking. Stock adjustment with a reason (damage,
correction). Product import from CSV and export to CSV.

### 6.2 Billing (POS screen)
Search by name or scan the barcode (a barcode scanner works like a keyboard).
Add quantity, change price or give discount per item or on the whole bill.
Payment types: cash, partial payment, or full credit on a customer account.
Hold a bill and resume it later. Print or download the bill (A4 or 80mm thermal
receipt). Return and refund an item with automatic stock and ledger correction.

### 6.3 Customer ledger
Customer profile with name, phone, address and credit limit. Ledger showing every
bill, payment and running balance. Receive payment against a pending balance.
List of customers with pending credit. Reminder link that opens WhatsApp with a
ready message about the pending amount (nothing is sent automatically).

### 6.4 Supplier ledger and purchases
Supplier profile and ledger. Purchase entry increases stock and updates the
purchase price. Payment to supplier and balance tracking.

### 6.5 Expenses
Record expenses by category (rent, electricity, salary, transport, other).
Monthly expense summary.

### 6.6 Reports
Daily sales and cash summary. Profit report (sales minus cost of goods minus
expenses). Stock report and low stock list. Top selling and slow moving products.
Customer credit report. Export to CSV. PDF via the browser print option
("Save as PDF") on any report or receipt page.

### 6.7 System features
Role-based access and activity log (who did what). Day closing summary for the
cashier. Database backup download. English-only interface.

### 6.8 Support and Help
A Support page in the sidebar, visible to all roles, with a Help Center (short
guides for POS, ledgers, stock and reports), FAQs (billing, credit, returns, low
stock, backup, printing), a contact card that reads the shop email and phone from
Settings (empty by default — nothing is invented), a ticket form (name, subject,
message, priority) and a ticket list with status Open / In Progress / Resolved.
Owner and manager can update a ticket status and add a reply note; every ticket
update is written to the activity log.

## 7. Languages and Technologies Used

| Language | Where it is used |
|---|---|
| PHP 8.3 | Backend logic with the Laravel framework |
| SQL | Database queries and migrations (SQLite locally, MySQL via environment) |
| HTML5 | Page structure through Blade views |
| CSS3 | Hand-written stylesheet in `public/css/app.css` (no build step) |
| JavaScript | POS screen interactivity, barcode input |
| JSON | API responses and configuration |
| Markdown | README and documentation |

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Interactive UI | Blade + vanilla JavaScript for the live POS screen |
| Styling | Custom CSS: deep teal/emerald primary (#0F766E / #065F46), warm amber accent (#F59E0B), dark slate sidebar (#0F172A), off-white background (#F8FAFC), white cards with soft shadows |
| Database | SQLite locally, MySQL with Eloquent ORM via environment settings |
| Authentication | Session authentication with hashed passwords (Laravel Auth) |
| Roles | Role field on users + `role` middleware (Spatie Permission is the documented next step, not installed in v1) |
| Reports | CSV export; charts are simple tables in v1 (Chart.js is a documented next step) |
| PDF and receipts | Print styles for A4 and 80mm thermal; browser "Save as PDF" (dompdf is a documented next step) |
| Barcode | Barcode scanner as keyboard input |
| Activity log | `activity_logs` table |
| Testing | PHPUnit feature tests |
| Tools | Composer, Git, GitHub, VS Code |
| Hosting | Shared hosting, VPS, or a local server inside the shop |

Honest stack note: this folder is a real Laravel 11 application (verified with
`composer create-project laravel/laravel:^11.0`), not a standalone PHP port.
Breeze, Livewire, Spatie Permission, Maatwebsite Excel and dompdf are documented
as next steps, not installed in v1, because v1 ships session auth, Blade +
vanilla JS POS, CSV export and print-to-PDF instead. Tailwind is not built here;
the design is hand-written CSS implementing the palette above.

## 8. System Architecture

The project follows the MVC (Model, View, Controller) pattern.

1. Routes and middleware: web routes with authentication and role checks.
2. Controllers: handle requests, such as the POS screen.
3. Services: business logic for stock, billing, ledger posting and profit
   calculation (`app/Services/BillingService.php`, `LedgerService.php`,
   `ProfitService.php`).
4. Models (Eloquent): Product, Category, Customer, Supplier, Sale, SaleItem,
   Purchase, PurchaseItem, Payment, Expense, User, SupportTicket.
5. Database transactions: every sale, return, purchase and payment is saved
   inside a transaction so stock and ledger always stay correct.
6. Views: Blade templates for the website, receipt and reports.

Billing flow: Cashier scans or searches items, then the bill total is calculated,
then payment is entered (cash, partial or credit), then in one database
transaction the sale and sale items are saved, stock is reduced and the customer
ledger is updated, then the receipt is printed.

## 9. Accounting Logic

- Sale total = sum of (quantity multiplied by price) minus discount.
- Customer balance = opening balance + total of unpaid sale amounts minus
  payments received (a positive balance means the customer owes the shop).
  Payments include cash received at checkout and later ledger payments, so a
  sale's paid amount is never subtracted twice.
- Supplier balance = opening balance + total purchases minus payments made.
- Cost of goods sold = quantity sold multiplied by the purchase price recorded
  at the time of sale.
- Gross profit = sales minus cost of goods sold.
- Net profit = gross profit minus expenses.
- Returns add the items back to stock and reduce the sale amount and the
  customer's balance.
- The purchase price used for profit is saved with each sale item, so changing
  a product price later does not change old profit reports.

## 10. Database Design

users: id, name, email, password, role, created_at
settings: id, key, value
categories: id, name
products: id, category_id, name, barcode, unit, purchase_price, sale_price, stock_qty, alert_qty, expiry_date, is_active
customers: id, name, phone, address, credit_limit, opening_balance
suppliers: id, name, phone, address, opening_balance
sales: id, invoice_no, customer_id (optional), user_id, subtotal, discount, total, paid_amount, due_amount, status, cancel_reason, created_at
sale_items: id, sale_id, product_id, quantity, sale_price, purchase_price, line_total
purchases and purchase_items: id, supplier_id, invoice_no, total, paid_amount, due_amount, date; items hold product_id, quantity, purchase_price
payments: id, type (customer or supplier), party_id, related_type, related_id, amount, method, note, user_id, created_at
expenses: id, category, amount, expense_date, description, user_id
stock_movements: id, product_id, type (sale, purchase, return, adjustment), quantity, reference_type, reference_id, note, created_at
activity_logs: id, user_id, action, detail, created_at
held_bills: id, user_id, name, items_json, discount, created_at
support_tickets: id, name, subject, message, priority, status, reply_note, user_id, created_at

Relationships: a sale has many sale items; a customer has many sales and
payments; a product has many stock movements; a ticket belongs to a user.

## 11. Main Routes

| Route | Purpose |
|---|---|
| /login | Sign in |
| /dashboard | Today's sales, cash, low stock, pending credit |
| /pos | Billing screen |
| /products | Product and stock management |
| /purchases | Purchase entries |
| /customers and /customers/{id}/ledger | Customer list and ledger |
| /suppliers and /suppliers/{id}/ledger | Supplier list and ledger |
| /expenses | Expenses |
| /reports/* | Sales, profit, stock and credit reports |
| /reports/day-closing | Day closing summary |
| /support | Help Center, FAQs, tickets |
| /settings | Shop details, receipt format, users |
| /backup | Database backup download |

## 12. Folder Structure (Laravel)

```
khatapos/
  app/
    Http/Controllers/    (Auth, Dashboard, Pos, Product, Customer, Supplier, Purchase, Expense, Report, Settings, Backup, Support)
    Http/Middleware/     (EnsureRole)
    Models/
    Services/            (BillingService, LedgerService, ProfitService)
  database/
    migrations/
    seeders/
  resources/
    views/               (layouts, auth, pos, products, customers, suppliers, purchases, expenses, reports, settings, support, sales)
  public/css/app.css
  routes/web.php
  tests/Feature/KhataPosTest.php
  docs/screenshots/
  README.md
```

## 13. User Interface Pages

- Dashboard: today's sale, profit (owner only), cash, low stock list, top customers with credit.
- POS screen: large search and scan box, bill list, totals and payment panel, hold and resume.
- Products: table with search, stock colors (red for low, green for in stock), import and export.
- Customer ledger: timeline of bills and payments with the running balance, WhatsApp reminder link.
- Reports: filters by date, export buttons, day closing page.
- Support: Help Center guides, FAQs, contact card, ticket form and ticket list.
- Settings: shop name, phone, email, receipt footer, printer size, users, activity log.
- Receipt: A4 and 80mm thermal layouts, print button, return and cancel actions.

## 14. Security Considerations

- Passwords hashed, CSRF protection and input validation through Laravel.
- Role-based access; only the owner can see profit, settings and delete records.
- Bills are never deleted; they are cancelled with a reason and the activity log keeps the record.
- Database transactions protect against half-saved bills when power or internet fails.
- Regular database backups (owner download for SQLite; mysqldump for MySQL).
- HTTPS on online hosting and limited login attempts (rate limiting is a documented next step).

## 15. Testing Plan

- Unit and feature tests: profit calculation, stock reduction, ledger balance, returns, partial payment, credit bill, authorization by role, support ticket flow.
- Concurrency test: two cashiers selling the same item at the same time (row locks are used in BillingService; a live two-cashier test was not run).
- Hardware test: barcode scanner and thermal printer layout (not run — no hardware in this environment).
- Real shop trial: run the system in one real shop for one to two weeks and collect feedback (not run).

## 16. Setup Guide

Prerequisites: PHP 8.3 or later, Composer, SQLite (demo) or MySQL, Git.

1. Clone the project and run `composer install`.
2. Copy `.env.example` to `.env` and set the database settings.
3. Run `php artisan key:generate`.
4. Run `php artisan migrate --seed` to create tables and the demo accounts.
5. Run `php artisan serve`.
6. Sign in with the demo account, create your own owner account from Settings, then remove the demo accounts.
7. For MySQL, set `DB_CONNECTION=mysql` and the host, database, username and password in `.env`, then run migrations again.

## 17. Deployment

- Online: shared hosting or a VPS with PHP 8.3 and MySQL, HTTPS enabled.
- Offline in the shop: a local server using XAMPP or Laragon on the shop computer, with daily backups to a USB drive or cloud storage, because shop internet can be unreliable.
- Set `APP_ENV=production` and `APP_DEBUG=false`.

## 18. Business Model (Optional)

| Plan | Suggested use |
|---|---|
| One-time license | Offline version installed in a shop with training |
| Monthly subscription | Online version with backups and support |
| Setup and training fee | Product import, staff training, printer setup |

Prices should be decided after talking to real shopkeepers. Before selling to
retailers, check the current tax invoicing rules that apply to their business.

## 19. Development Timeline

| Week | Task |
|---|---|
| 1 | Requirements, database design, Laravel setup, authentication and roles |
| 2 | Products, categories and stock |
| 3 | Customers, suppliers and ledgers |
| 4 | POS billing screen and receipts |
| 5 | Purchases, returns and expenses |
| 6 | Reports, support, testing and bug fixing |
| 7 | Activity log, backup, testing and bug fixing |
| 8 | Deployment, shop trial and documentation |

## 20. Limitations

- Single shop (no multi-branch) in version 1.
- No integration with tax authority systems in version 1.
- Works best with a keyboard-style barcode scanner and a standard thermal printer.
- Live MySQL, real barcode scanner, real thermal printer, live deployment and a two-cashier concurrency test were not verified in this environment.

## 21. Future Improvements

- Multi-branch and multi-shop (SaaS) support.
- Automatic WhatsApp and SMS reminders for credit.
- Mobile app for the owner to see daily sales.
- Tax invoicing integration after checking current regulations.
- Online ordering and delivery.
- Supplier price comparison and smart reorder suggestions.
- Breeze or Livewire POS, Spatie Permission, Maatwebsite Excel, dompdf PDF export, Chart.js charts.

## 22. Conclusion

KhataPOS solves a daily problem of local shopkeepers by joining billing, stock
and the traditional customer ledger in one simple system. It demonstrates Laravel
MVC, role-based access, database transactions, accounting logic, reports and
receipt printing, and it can be tested with a real shop and turned into a real
product, which makes it a strong portfolio project and a possible business.

## Screenshots

Screenshots are in `docs/screenshots/`: login, dashboard, POS billing, products,
customer ledger, purchases, expenses, reports, receipt (80mm thermal) and support.
