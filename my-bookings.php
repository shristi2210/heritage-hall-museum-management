<?php
/*
 * my-bookings.php - the visitor's account page.
 * Shows their Visitor ID and every booking; the list is loaded with Ajax
 * from api/visitor_bookings.php and cancelling goes to api/visitor_cancel.php.
 */
require_once 'includes/functions.php';
require_visitor_page();
$me = current_visitor();

$welcome = $_SESSION['welcome'] ?? null;   // set right after sign-up
unset($_SESSION['welcome']);

$pageTitle  = 'My Bookings';
$activePage = 'account';
$pageScripts = ['assets/js/account.js'];
require 'includes/header.php';
?>

<section class="page-head">
    <div class="container account-head">
        <div>
            <p class="eyebrow">My account</p>
            <h1>Hello, <?= e(explode(' ', $me['full_name'])[0]) ?></h1>
            <p><?= e($me['email']) ?> &middot; <?= e($me['phone']) ?></p>
        </div>
        <div class="visitor-id-card">
            <span>Visitor ID</span>
            <strong><?= e($me['visitor_code']) ?></strong>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($welcome): ?>
            <div class="success-banner">Welcome to Heritage Hall! Your Visitor ID is <strong><?= e($welcome) ?></strong>.</div>
        <?php endif; ?>

        <div class="account-toolbar">
            <div class="tabs-simple" id="filterTabs">
                <button class="active" data-filter="upcoming">Upcoming</button>
                <button data-filter="past">Past</button>
                <button data-filter="cancelled">Cancelled</button>
                <button data-filter="all">All</button>
            </div>
            <a href="booking.php" class="btn btn-primary">+ Book a new visit</a>
        </div>

        <div id="bookingList" class="booking-list"><p class="hint">Loading your bookings&hellip;</p></div>

        <p class="hint">Refunds follow our <a href="cancellation-policy.php">cancellation policy</a>: 100% up to <?= FULL_REFUND_DAYS ?> days before, then <?= REFUND_STEP ?>% less for each day closer.</p>
    </div>
</section>

<!-- Cancel confirmation (jQuery UI dialog) -->
<div id="cancelDialog" title="Cancel booking" hidden>
    <p class="dlg-lead">Are you sure you want to cancel <strong id="cCode"></strong>?</p>
    <dl class="facts">
        <dt>Visit</dt><dd id="cVisit"></dd>
        <dt>Amount paid</dt><dd id="cPaid"></dd>
        <dt>Days before visit</dt><dd id="cDays"></dd>
        <dt>Refund</dt><dd><strong id="cRefund"></strong></dd>
    </dl>
    <div class="refund-meter"><b id="cMeter"></b></div>
    <p class="hint" id="cNote"></p>
</div>

<?php require 'includes/footer.php'; ?>
