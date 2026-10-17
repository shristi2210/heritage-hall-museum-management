# Heritage Hall Museum Management System 🏛️

## About the Project

Heritage Hall is a museum management web application that I worked on to bring different museum activities together in one place. The idea was to make it easier for visitors to explore museum collections and book tickets, while also helping museum staff manage artifacts, galleries, and bookings.

Instead of keeping these activities separate, the project connects the visitor-facing website with a database and an admin panel.

## What I Built

- **Artifact Catalogue:** Visitors can browse museum artifacts and search for specific items using filters.
- **Interactive Artifact Details:** Artifact information opens in a popup without having to reload the entire page.
- **Ticket Booking:** Visitors can select dates and entry slots, check availability, and calculate ticket prices.
- **E-Tickets with QR Codes:** Generates tickets that can be printed and used for check-in.
- **Admin Dashboard:** Displays booking information and other museum statistics.
- **Artifact Management:** Staff can add, edit, delete, and manage artifacts and their images.
- **Exhibition Planner:** Allows staff to organize artifacts into galleries using drag-and-drop functionality.
- **Booking Management:** Staff can view bookings, check visitors in, and manage cancellations.

## Technologies Used

- PHP for server-side logic and APIs
- MySQL for database management
- HTML and CSS for the interface
- JavaScript and jQuery for interactive features
- jQuery UI for dialogs, date selection, tabs, and drag-and-drop
- AJAX for fetching and updating data without full page reloads
- PDO and prepared statements for database operations

## How the Project Works

The frontend communicates with PHP pages and API endpoints. These endpoints interact with the MySQL database to retrieve artifact information, check ticket availability, process bookings, and update administrative data.

jQuery and AJAX make several interactions dynamic, so users can search collections, open artifact details, and check available slots without constantly refreshing the page.

The database stores information about administrators, visitors, galleries, artifacts, bookings, and payments.

## How to Run the Project

1. Install XAMPP and start Apache and MySQL.
2. Copy the `museum` folder into the XAMPP `htdocs` directory.
3. Open phpMyAdmin and import `museum/database/museum_db.sql` for a fresh installation.
4. Check the database settings in `museum/config/config.php`.
5. Open `http://localhost/museum` in your browser.

**Note:** The SQL file creates the database from scratch and drops an existing database with the same name. Back up any existing data before importing it.

## What I Learned

This project helped me understand how frontend interactions connect with backend logic and a relational database. Working with PHP APIs, AJAX requests, SQL queries, and dynamic UI components gave me a better understanding of how a complete web application works.

I also learned how database relationships, booking rules, and server-side validation contribute to building a more reliable application.

## Future Improvements

Some areas I would like to explore further include integrating a real payment gateway, improving the deployment process, and adding more advanced reporting and analytics for museum administrators.

---

This project was a hands-on learning experience in web development, database management, and building interactive applications.
