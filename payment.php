<?php
/*
 * payment.php?code=HH-XXXXXX - Dummy payment gateway ("HeritagePay", test mode).
 * No real money moves. The form posts to api/pay.php with Ajax, which records the
 * payment and turns the booking from Pending into Booked.
 */
require_once 'includes/functions.php';
require_visitor_page();
$me = current_visitor();
expire_pending_bookings();

$code = strtoupper(trim($_GET['code'] ?? ''));
$stmt = db()->prepare(
    "SELECT b.*, TIMESTAMPDIFF(SECOND, NOW(), b.created_at + INTERVAL " . (int)HOLD_MINUTES . " MINUTE) AS hold_seconds
     FROM bookings b WHERE b.booking_code = ? AND b.visitor_id = ?"
);
$stmt->execute([$code, $me['id']]);
$b = $stmt->fetch();

if ($b && in_array($b['status'], ['Booked', 'Checked-in'], true)) {
    header('Location: ticket.php?code=' . urlencode($b['booking_code']));   // already paid
    exit;
}

$pageTitle  = 'Payment';
$activePage = 'booking';
$pageScripts = ['assets/js/payment.js'];
require 'includes/header.php';
?>

<section class="section pay-section">
    <div class="container narrow-wide">
    <?php if (!$b): ?>
        <div class="empty-state"><h2>Booking not found</h2><p><a href="my-bookings.php">Go to My Bookings</a></p></div>
    <?php elseif ($b['status'] === 'Cancelled'): ?>
        <div class="empty-state">
            <h2>This booking has expired</h2>
            <p>Seats are held for <?= HOLD_MINUTES ?> minutes while you pay. They have been released, so please book again.</p>
            <a class="btn btn-primary" href="booking.php">Book again</a>
        </div>
    <?php else: ?>

        <div class="gateway" id="gateway" data-code="<?= e($b['booking_code']) ?>" data-seconds="<?= max(0, (int)$b['hold_seconds']) ?>">
            <div class="gw-head">
                <div class="gw-brand"><span class="gw-logo">HP</span> HeritagePay <small>secure checkout</small></div>
                <span class="gw-test">TEST MODE &middot; no real money</span>
            </div>

            <div class="gw-body">
                <!-- Order summary -->
                <aside class="gw-summary">
                    <p class="eyebrow">Paying Heritage Hall Museum</p>
                    <p class="gw-amount">&#8377;<?= e(number_format($b['total_amount'])) ?></p>
                    <dl>
                        <dt>Booking</dt><dd><code><?= e($b['booking_code']) ?></code></dd>
                        <dt>Visit</dt><dd><?= e(date('D, d M Y', strtotime($b['visit_date']))) ?>, <?= e($b['time_slot']) ?></dd>
                        <dt>Tickets</dt><dd><?= (int)$b['adults'] ?> adult<?= $b['adults'] == 1 ? '' : 's' ?>, <?= (int)$b['students'] ?> student<?= $b['students'] == 1 ? '' : 's' ?></dd>
                        <dt>Visitor ID</dt><dd><?= e($me['visitor_code']) ?></dd>
                    </dl>
                    <div class="gw-timer">Seats held for <strong id="holdTimer">--:--</strong></div>
                </aside>

                <!-- Payment methods (jQuery UI Tabs) -->
                <div class="gw-methods">
                    <div id="payTabs">
                        <ul>
                            <li><a href="#tab-card">Card</a></li>
                            <li><a href="#tab-upi">UPI</a></li>
                            <li><a href="#tab-nb">Net Banking</a></li>
                        </ul>

                        <form id="tab-card" class="pay-form" data-method="Card" novalidate>
                            <div class="field"><label for="card_number">Card number</label>
                                <div class="card-input"><input type="text" id="card_number" inputmode="numeric" maxlength="19" placeholder="0000 0000 0000 0000" autocomplete="off"><span id="cardBrand"></span></div>
                                <small class="error"></small></div>
                            <div class="two-fields">
                                <div class="field"><label for="expiry">Expiry (MM/YY)</label>
                                    <input type="text" id="expiry" maxlength="5" placeholder="12/28" autocomplete="off"><small class="error"></small></div>
                                <div class="field"><label for="cvv">CVV</label>
                                    <input type="password" id="cvv" maxlength="3" placeholder="123" autocomplete="off"><small class="error"></small></div>
                            </div>
                            <div class="field"><label for="card_name">Name on card</label>
                                <input type="text" id="card_name" maxlength="60" value="<?= e($me['full_name']) ?>"><small class="error"></small></div>
                            <button class="btn btn-primary btn-block pay-btn">Pay &#8377;<?= e(number_format($b['total_amount'])) ?></button>
                        </form>

                        <form id="tab-upi" class="pay-form" data-method="UPI" novalidate>
                            <div class="field"><label for="upi_id">UPI ID</label>
                                <input type="text" id="upi_id" maxlength="50" placeholder="yourname@okbank" autocomplete="off"><small class="error"></small></div>
                            <p class="hint">A payment request would be sent to your UPI app. In test mode it is approved instantly.</p>
                            <button class="btn btn-primary btn-block pay-btn">Pay &#8377;<?= e(number_format($b['total_amount'])) ?></button>
                        </form>

                        <form id="tab-nb" class="pay-form" data-method="Net Banking" novalidate>
                            <div class="field"><label for="bank">Choose your bank</label>
                                <select id="bank">
                                    <option value="">Select a bank</option>
                                    <option>State Bank of India</option><option>HDFC Bank</option><option>ICICI Bank</option>
                                    <option>Axis Bank</option><option>Kotak Mahindra Bank</option><option value="Test Bank (fails)">Test Bank (always fails)</option>
                                </select><small class="error"></small></div>
                            <button class="btn btn-primary btn-block pay-btn">Pay &#8377;<?= e(number_format($b['total_amount'])) ?></button>
                        </form>
                    </div>

                    <p class="pay-message" id="payMessage"></p>

                    <details class="test-data">
                        <summary>Test details for the demo</summary>
                        <ul>
                            <li><strong>4242 4242 4242 4242</strong>, any future expiry, any CVV: payment succeeds</li>
                            <li><strong>4000 0000 0000 0002</strong>: card is declined</li>
                            <li>UPI <strong>success@upi</strong> succeeds, <strong>fail@upi</strong> fails</li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        <!-- "Processing" overlay shown while the fake bank responds -->
        <div class="gw-overlay" id="processing" hidden>
            <div class="gw-spinner"></div>
            <p>Contacting your bank&hellip;</p>
        </div>
    <?php endif; ?>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
