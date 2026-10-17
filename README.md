# Heritage Hall Museum Management System

A web application for a museum: visitors explore the collection and book timed-entry tickets, and staff manage artifacts, plan exhibitions with drag and drop, and check visitors in at the entrance.

**Course:** Web UI and Content Management
**Stack:** PHP, MySQL, Apache (LAMP via XAMPP), jQuery, jQuery UI, Ajax, HTML/CSS

---

## 1. How to run it (XAMPP)

1. Install **XAMPP** and open the XAMPP Control Panel. Start **Apache** and **MySQL**.
2. Copy the `museum` folder into `C:\xampp\htdocs\` so you have `C:\xampp\htdocs\museum\index.php`.
3. Open **http://localhost/phpmyadmin**, click **Import**, choose `museum/database/museum_db.sql` and click **Go**.
   This creates the `museum_db` database with sample data.
4. Open **http://localhost/museum** in the browser.

**Staff login:** http://localhost/museum/admin/login.php → username `admin`, password `admin123`

> Tip: the sample bookings are created relative to the day you import the SQL file.
> Re-import it on the morning of the presentation so "today" has bookings to show.

If your MySQL root user has a password, change `DB_PASS` in `config/config.php`.

All libraries (jQuery, jQuery UI, QR code) are inside `assets/vendor`, so the project works without internet.

---

## 2. Features

### Visitor side
| Feature | What it does | Concepts |
|---|---|---|
| Home page | Live counts from the database, galleries, most-viewed artifacts | PHP + MySQL, jQuery `animate()` counters |
| Collection search | Results update while typing, filters for category / era / gallery, search word highlighted | **Ajax** (`$.getJSON`), debounce, DOM building |
| Artifact popup | Click a card to see details without leaving the page | **jQuery UI Dialog**, Ajax |
| Artifact page | Full page per object (this is what the exhibit QR code opens) | PHP, GET parameters |
| Ticket booking | Calendar with Mondays disabled, live seats per time slot, live price total | **jQuery UI Datepicker**, Ajax, DOM |
| Booking safety | Server re-checks everything and locks the slot in a transaction so the last seat can't be sold twice | PHP validation, MySQL transactions |
| E-ticket | Ticket with booking code and QR code, printable | QR library, print CSS |

### Staff side (Curator Panel)
| Feature | What it does | Concepts |
|---|---|---|
| Login | Session-based login, hashed passwords | `password_hash` / `password_verify`, sessions |
| Dashboard | Visitors for the next 7 days, today's slots, most viewed, gallery space, conservation alerts. Auto-refreshes every 30 s | Ajax, **jQuery UI Tabs**, animated bar charts in jQuery |
| Artifacts (CRUD) | Add, edit, delete artifacts with image upload and preview; print a QR label for each exhibit | Ajax with `FormData`, file upload, jQuery UI Dialog |
| Exhibition Planner | Drag artifacts between storage and galleries; gallery space limits enforced; save the layout | **jQuery UI Sortable** (`connectWith`), Ajax + JSON |
| Bookings & Check-in | Filter bookings, type/scan a ticket code at the entrance, check in, cancel or restore | Ajax, jQuery UI Datepicker, business rules in PHP |

### Security measures
- PDO **prepared statements** everywhere (no SQL injection)
- All output passed through `htmlspecialchars()` / `escapeHtml()` (no XSS)
- Passwords stored as bcrypt hashes
- **CSRF token** sent with every admin Ajax POST
- Admin APIs reject requests without a login session
- Uploads: only JPG/PNG/WEBP up to 2 MB, checked with `getimagesize()`; scripts blocked in `uploads/` by `.htaccess`

---

## 3. Folder structure

```
museum/
├── index.php            Home page
├── catalogue.php        Collection with live search
├── artifact.php         One artifact (QR code target)
├── booking.php          Ticket booking
├── ticket.php           E-ticket with QR code
├── config/
│   ├── config.php       Settings (DB login, slots, prices)
│   └── db.php           PDO database connection
├── includes/            Shared header/footer + helper functions
├── api/                 Ajax endpoints (all return JSON)
│   ├── artifacts.php        public: search + details
│   ├── slots.php            public: seats left per slot
│   ├── book.php             public: create booking
│   ├── admin_stats.php      admin: dashboard numbers
│   ├── admin_artifacts.php  admin: artifact CRUD + upload
│   ├── admin_layout.php     admin: exhibition planner
│   └── admin_bookings.php   admin: bookings + check-in
├── admin/               Curator panel pages + admin/js/*.js
├── assets/css, assets/js, assets/img, assets/vendor
├── uploads/             Images uploaded by staff
└── database/museum_db.sql
```

### Database tables
- `admins` – staff accounts
- `galleries` – rooms, floor and how many artifacts each can hold
- `artifacts` – the collection (`gallery_id` NULL = in storage, `position` = display order)
- `bookings` – visitor tickets with slot, ticket counts, amount and status

---

## 4. Suggested split for 2 members

| Member 1: Visitor side | Member 2: Staff side |
|---|---|
| Home page, catalogue with Ajax search and dialog | Login, sessions, CSRF |
| Booking page (datepicker, slots, validation) | Dashboard with tabs and charts |
| `api/artifacts.php`, `api/slots.php`, `api/book.php` | Artifact CRUD with image upload and QR labels |
| E-ticket with QR code | Exhibition Planner (drag and drop) |
| Visitor CSS, responsive layout | Bookings & check-in |
| Shared: database design, testing, report and slides | |

---

## 5. Demo script (about 8 minutes)

1. **Home** – point out the live counts come from MySQL.
2. **Collection** – type "bronze": results change without reload (Ajax). Open one in the dialog.
3. **Book tickets** – pick a date (Mondays disabled), show seats per slot, add tickets, watch the total update, confirm → ticket with QR.
4. **Staff login** → **Dashboard** – the new booking appears in the chart; switch tabs.
5. **Exhibition Planner** – drag an artifact from storage into a gallery, try overfilling a gallery (blocked), save → refresh the public collection to show it appeared.
6. **Artifacts** – add an artifact with an image, print a QR label.
7. **Bookings** – enter the code from the ticket and check the visitor in.

**Phone demo for QR codes:** connect laptop and phone to the same Wi-Fi, open the site using the laptop's IP address (e.g. `http://192.168.1.5/museum`), then generate the QR label. The QR uses the address in the browser, so the phone can open it.

---

## 6. Testing done
| Test | Expected | Result |
|---|---|---|
| Book with empty/invalid name, email, phone | Error messages, nothing saved | Pass |
| Book a Monday or a past date | Rejected by server | Pass |
| Book more seats than left in a slot | "Only N seats left" | Pass |
| Check in a ticket for another day | Blocked with message | Pass |
| Cancel then restore a booking | Seats released / re-checked | Pass |
| Upload a .php file as image | Rejected | Pass |
| Admin API without login | 401 error | Pass |
| Admin POST without CSRF token | 403 error | Pass |
| Drag a 7th artifact into a 6-item gallery | Drop cancelled + message | Pass |
| Mobile width (390 px) | Layout stacks, menu collapses | Pass |

---

## Version 2: visitor accounts, payments and cancellations

### What is new
| Feature | Pages / files | Concepts |
|---|---|---|
| Visitor sign-up & login, each visitor gets a **Visitor ID** (e.g. `HHV1002`) saved in the `visitors` table | `register.php`, `login.php`, `logout.php`, `api/check_email.php` | Sessions, `password_hash`, Ajax email check while typing, jQuery validation and password-strength meter |
| Booking needs a login; seats are **held for 15 minutes** while the visitor pays | `booking.php`, `api/book.php` | Booking status `Pending`, automatic release of unpaid holds |
| **Dummy payment gateway "HeritagePay"** (Card / UPI / Net Banking) | `payment.php`, `api/pay.php`, `assets/js/payment.js` | jQuery UI Tabs, card formatting, Luhn check, processing overlay, Ajax; card numbers are never stored (only brand + last 4) |
| **My Bookings**: upcoming / past / cancelled tickets | `my-bookings.php`, `api/visitor_bookings.php`, `assets/js/account.js` | Ajax list, filter tabs, jQuery UI Dialog |
| **Cancellation policy with automatic refunds** | `cancellation-policy.php`, `api/visitor_cancel.php` | Refund worked out on the server |
| Staff see payment + refund status and visitor IDs; museum cancellations refund 100% | `admin/bookings.php`, `api/admin_bookings.php` | |

### Cancellation policy
| Cancelled | Refund |
|---|---|
| 10 or more days before the visit | 100% |
| 9 days before ... 1 day before | 90% ... 10% (10% less per day) |
| On the visit day | No refund |

Rules live in `config/config.php` (`FULL_REFUND_DAYS`, `REFUND_STEP`, `HOLD_MINUTES`) and in `refund_percent()` in `includes/functions.php`.

### Test details
- Visitor demo login: **demo@heritagehall.in / visitor123** (has bookings 15 and 6 days away, so you can show a 100% and a 60% refund)
- Staff login is still **admin / admin123**
- Card **4242 4242 4242 4242**, any future expiry, any CVV: payment succeeds
- Card **4000 0000 0000 0002**: payment is declined
- UPI **success@upi** works, **fail@upi** fails; Net Banking "Test Bank" always fails

### Installing version 2
- **Fresh install / new laptop:** import `database/museum_db.sql` (it creates the database itself).
- **Already running version 1 and want to keep your data:** import `database/upgrade_v2.sql` instead.

### New database tables
- `visitors` – visitor accounts (`visitor_code` is the Visitor ID)
- `payments` – every payment attempt from the dummy gateway (Success / Failed / Refunded)
- `bookings` gained `visitor_id`, `payment_status`, `refund_percent`, `refund_amount`, `cancelled_at`, and the status `Pending`
