<?php
/*
 * booking.php - Ticket booking
 * Step 1: jQuery UI Datepicker -> Step 2: Ajax loads free seats per slot
 * Step 3: visitor details -> Ajax POST to api/book.php -> redirect to payment.php (dummy gateway)
 * Visitors must be logged in (v2), so their details are filled in from their account.
 */
require_once 'includes/functions.php';
require_visitor_page();
$me = current_visitor();

$pageTitle  = 'Book Tickets';
$activePage = 'booking';
$pageScripts = ['assets/js/booking.js'];
require 'includes/header.php';
?>

<section class="page-head">
    <div class="container">
        <p class="eyebrow">Tickets</p>
        <h1>Book your time slot</h1>
        <p>Each slot admits up to <?= SLOT_CAPACITY ?> visitors. Seats update live as other people book.</p>
    </div>
</section>

<section class="section">
    <div class="container booking-layout">

        <form id="bookingForm" class="booking-form" novalidate>
            <!-- Settings the JavaScript needs, printed from PHP config -->
            <input type="hidden" id="cfg" data-adult="<?= PRICE_ADULT ?>" data-student="<?= PRICE_STUDENT ?>"
                   data-max="<?= MAX_TICKETS_PER_BOOKING ?>" data-days="<?= BOOKING_DAYS_AHEAD ?>">

            <div class="step">
                <h3><span>1</span> Choose a date</h3>
                <div id="datepicker"></div>
                <input type="hidden" name="visit_date" id="visit_date">
                <p class="hint">Closed on Mondays. Bookings open <?= BOOKING_DAYS_AHEAD ?> days ahead.</p>
            </div>

            <div class="step">
                <h3><span>2</span> Pick a time slot</h3>
                <div id="slots" class="slot-grid"><p class="hint">Select a date to see available slots.</p></div>
                <input type="hidden" name="time_slot" id="time_slot">
            </div>

            <div class="step">
                <h3><span>3</span> Tickets</h3>
                <div class="ticket-row">
                    <label for="adults">Adults <small>&#8377;<?= PRICE_ADULT ?></small></label>
                    <div class="qty"><button type="button" class="qty-btn" data-for="adults" data-step="-1">&minus;</button>
                        <input type="number" id="adults" name="adults" value="1" min="0" max="<?= MAX_TICKETS_PER_BOOKING ?>" readonly>
                        <button type="button" class="qty-btn" data-for="adults" data-step="1">+</button></div>
                </div>
                <div class="ticket-row">
                    <label for="students">Students <small>&#8377;<?= PRICE_STUDENT ?>, ID required</small></label>
                    <div class="qty"><button type="button" class="qty-btn" data-for="students" data-step="-1">&minus;</button>
                        <input type="number" id="students" name="students" value="0" min="0" max="<?= MAX_TICKETS_PER_BOOKING ?>" readonly>
                        <button type="button" class="qty-btn" data-for="students" data-step="1">+</button></div>
                </div>
            </div>

            <div class="step">
                <h3><span>4</span> Your details</h3>
                <p class="hint booked-as">Booking as <strong><?= e($me['full_name']) ?></strong> &middot; Visitor ID <?= e($me['visitor_code']) ?>. You can change the name for this ticket.</p>
                <div class="field"><label for="visitor_name">Full name</label>
                    <input type="text" id="visitor_name" name="visitor_name" maxlength="100" value="<?= e($me['full_name']) ?>"><small class="error"></small></div>
                <div class="field"><label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="120" value="<?= e($me['email']) ?>"><small class="error"></small></div>
                <div class="field"><label for="phone">Mobile number</label>
                    <input type="tel" id="phone" name="phone" maxlength="10" placeholder="10 digits" value="<?= e($me['phone']) ?>"><small class="error"></small></div>
            </div>
        </form>

        <!-- Live order summary (updated by jQuery) -->
        <aside class="summary" id="summary">
            <h3>Your visit</h3>
            <dl>
                <dt>Date</dt><dd id="sumDate">-</dd>
                <dt>Slot</dt><dd id="sumSlot">-</dd>
                <dt>Adults</dt><dd id="sumAdults">1 &times; &#8377;<?= PRICE_ADULT ?></dd>
                <dt>Students</dt><dd id="sumStudents">0 &times; &#8377;<?= PRICE_STUDENT ?></dd>
            </dl>
            <p class="total">Total <strong id="sumTotal">&#8377;<?= PRICE_ADULT ?></strong></p>
            <button type="submit" form="bookingForm" id="bookBtn" class="btn btn-primary btn-block">Continue to payment</button>
            <p class="form-message" id="formMessage"></p>
            <div class="policy-mini">
                <strong>Free cancellation</strong> up to <?= FULL_REFUND_DAYS ?> days before your visit, then <?= REFUND_STEP ?>% less refund per day.
                <a href="cancellation-policy.php" target="_blank">Full policy</a>
            </div>
        </aside>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
