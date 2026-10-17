<?php
/*
 * ticket.php?code=HH-XXXXXX - e-ticket with a QR code.
 * Staff scan / type this code on the admin Bookings page to check visitors in.
 * Without a code, the page shows a "find my ticket" form.
 */
require_once 'includes/functions.php';

$code = strtoupper(trim($_GET['code'] ?? ''));
$b = null;
if ($code !== '') {
    expire_pending_bookings();
    $stmt = db()->prepare(
        "SELECT b.*, v.visitor_code, p.txn_id, p.detail AS pay_detail
         FROM bookings b
         LEFT JOIN visitors v ON v.id = b.visitor_id
         LEFT JOIN payments p ON p.booking_id = b.id AND p.status <> 'Failed'
         WHERE b.booking_code = ?"
    );
    $stmt->execute([$code]);
    $b = $stmt->fetch();
}
$isOwner = $b && is_visitor() && (int)$b['visitor_id'] === (int)$_SESSION['visitor_id'];

$pageTitle  = $b ? 'Your Ticket' : 'Find Your Ticket';
$activePage = 'booking';
$pageScripts = ['assets/vendor/qrcode.min.js', 'assets/js/ticket.js'];
require 'includes/header.php';
?>

<section class="section">
    <div class="container narrow">
    <?php if ($b && $b['status'] === 'Pending'): ?>
        <div class="find-ticket">
            <h1>Payment pending</h1>
            <p>Booking <strong><?= e($b['booking_code']) ?></strong> is not paid yet, so there is no ticket.</p>
            <?php if ($isOwner): ?><a class="btn btn-primary" href="payment.php?code=<?= e(urlencode($b['booking_code'])) ?>">Complete payment</a><?php endif; ?>
        </div>
    <?php elseif ($b): ?>
        <?php if ($b['status'] === 'Cancelled'): ?>
            <div class="alert-box error-box center">This booking was cancelled<?= $b['refund_amount'] > 0 ? '. Refund: &#8377;' . e(number_format($b['refund_amount'], 2)) . ' (' . (int)$b['refund_percent'] . '%)' : '' ?>. The ticket is no longer valid.</div>
        <?php endif; ?>
        <?php if (isset($_GET['new']) && $b['status'] === 'Booked'):   // booking.js adds &new=1 right after booking ?>
            <div class="success-banner">Booking confirmed. Show this ticket at the entrance.</div>
        <?php endif; ?>

        <div class="ticket" id="ticket">
            <div class="ticket-main">
                <p class="eyebrow">Heritage Hall Museum &middot; Entry Ticket</p>
                <h2><?= e($b['visitor_name']) ?></h2>
                <dl class="ticket-facts">
                    <div><dt>Date</dt><dd><?= e(date('D, d M Y', strtotime($b['visit_date']))) ?></dd></div>
                    <div><dt>Entry slot</dt><dd><?= e($b['time_slot']) ?></dd></div>
                    <div><dt>Visitors</dt><dd><?= (int)$b['adults'] ?> adult<?= $b['adults'] == 1 ? '' : 's' ?>, <?= (int)$b['students'] ?> student<?= $b['students'] == 1 ? '' : 's' ?></dd></div>
                    <div><dt><?= $b['pay_detail'] ? 'Paid online' : 'Paid at counter' ?></dt><dd>&#8377;<?= e(number_format($b['total_amount'])) ?><?= $b['pay_detail'] ? ' <small>' . e($b['pay_detail']) . '</small>' : '' ?></dd></div>
                    <?php if ($b['visitor_code']): ?><div><dt>Visitor ID</dt><dd><?= e($b['visitor_code']) ?></dd></div><?php endif; ?>
                    <?php if ($b['txn_id']): ?><div><dt>Transaction</dt><dd><code><?= e($b['txn_id']) ?></code></dd></div><?php endif; ?>
                </dl>
                <p class="ticket-status status-<?= e(strtolower($b['status'])) ?>"><?= e($b['status']) ?></p>
            </div>
            <div class="ticket-stub">
                <div id="qrcode" data-code="<?= e($b['booking_code']) ?>"></div>
                <p class="ticket-code"><?= e($b['booking_code']) ?></p>
            </div>
        </div>

        <div class="ticket-actions">
            <button class="btn btn-primary" id="printTicket">Print ticket</button>
            <?php if ($isOwner): ?><a class="btn btn-ghost-dark" href="my-bookings.php">My Bookings</a><?php else: ?><a class="btn btn-ghost-dark" href="catalogue.php">Explore the collection</a><?php endif; ?>
        </div>
        <p class="hint center">Students must carry a valid ID card. Please arrive within 15 minutes of your slot.</p>

    <?php else: ?>
        <div class="find-ticket">
            <h1>Find your ticket</h1>
            <?php if ($code !== ''): ?><p class="error-text">No booking found with code <?= e($code) ?>.</p><?php endif; ?>
            <form method="get" class="inline-form">
                <input type="text" name="code" placeholder="e.g. HH-7K2Q9X" value="<?= e($code) ?>" required>
                <button class="btn btn-primary">Show ticket</button>
            </form>
        </div>
    <?php endif; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
