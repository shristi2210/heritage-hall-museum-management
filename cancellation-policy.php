<?php
/*
 * cancellation-policy.php - the refund rules, built from the same settings
 * (FULL_REFUND_DAYS, REFUND_STEP) that the cancel API uses, so they always match.
 */
$pageTitle  = 'Cancellation Policy';
$activePage = '';
require 'includes/header.php';
?>

<section class="page-head">
    <div class="container">
        <p class="eyebrow">Tickets</p>
        <h1>Cancellation &amp; refunds</h1>
        <p>Plans change. Cancel online from My Bookings and the refund is worked out automatically.</p>
    </div>
</section>

<section class="section">
    <div class="container policy-layout">
        <div>
            <h2>How much you get back</h2>
            <p class="muted-text">The refund depends on how many days before your visit you cancel.</p>
            <table class="policy-table">
                <thead><tr><th>When you cancel</th><th>Refund</th><th></th></tr></thead>
                <tbody>
                    <tr class="full"><td><?= FULL_REFUND_DAYS ?> or more days before the visit</td><td>100%</td><td><span class="refund-bar"><b style="width:100%"></b></span></td></tr>
                    <?php for ($d = FULL_REFUND_DAYS - 1; $d >= 1; $d--): $pct = $d * REFUND_STEP; ?>
                    <tr><td><?= $d ?> day<?= $d === 1 ? '' : 's' ?> before</td><td><?= $pct ?>%</td><td><span class="refund-bar"><b style="width:<?= $pct ?>%"></b></span></td></tr>
                    <?php endfor; ?>
                    <tr class="none"><td>On the day of the visit</td><td>No refund</td><td><span class="refund-bar"><b style="width:0"></b></span></td></tr>
                </tbody>
            </table>
        </div>

        <aside class="policy-notes">
            <h3>Good to know</h3>
            <ul>
                <li>You can cancel until your entry slot starts. After that the ticket can no longer be cancelled.</li>
                <li>Tickets that have been used at the entrance cannot be cancelled.</li>
                <li>The refund goes back to the same payment method (in this demo it is only recorded, no real money moves).</li>
                <li>If the museum cancels your visit, you always get a 100% refund.</li>
                <li>Unpaid bookings are released automatically after <?= HOLD_MINUTES ?> minutes.</li>
            </ul>
            <p class="example"><strong>Example:</strong> a ticket for &#8377;250 cancelled 6 days before the visit gets 60% back, which is &#8377;150.</p>
            <a href="my-bookings.php" class="btn btn-primary">Go to My Bookings</a>
        </aside>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
