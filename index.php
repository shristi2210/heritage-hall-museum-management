<?php
/*
 * index.php - Home page
 * Shows museum highlights pulled live from the database.
 */
$pageTitle  = 'Welcome';
$activePage = 'home';
$pageScripts = ['assets/js/home.js'];
require 'includes/header.php';

$pdo = db();

// Numbers for the "at a glance" strip
$totalArtifacts = $pdo->query("SELECT COUNT(*) FROM artifacts")->fetchColumn();
$onDisplay      = $pdo->query("SELECT COUNT(*) FROM artifacts WHERE gallery_id IS NOT NULL")->fetchColumn();
$galleryCount   = $pdo->query("SELECT COUNT(*) FROM galleries")->fetchColumn();
$visitors       = $pdo->query("SELECT COALESCE(SUM(adults + students), 0) FROM bookings WHERE status <> 'Cancelled'")->fetchColumn();

// Galleries with how many artifacts each one shows
$galleries = $pdo->query(
    "SELECT g.*, COUNT(a.id) AS items
     FROM galleries g LEFT JOIN artifacts a ON a.gallery_id = g.id
     GROUP BY g.id ORDER BY g.id"
)->fetchAll();

// Most viewed artifacts that are currently on display
$popular = $pdo->query(
    "SELECT a.id, a.name, a.period, a.image, g.name AS gallery
     FROM artifacts a JOIN galleries g ON g.id = a.gallery_id
     ORDER BY a.views DESC LIMIT 4"
)->fetchAll();
?>

<section class="hero">
    <div class="container hero-inner">
        <p class="eyebrow">Five thousand years under one roof</p>
        <h1>Walk through India's past,<br>one object at a time.</h1>
        <p class="hero-sub">Seals from the Indus Valley, Chola bronzes, Mughal miniatures and the instruments that guided ships across the Arabian Sea.</p>
        <div class="hero-actions">
            <a href="booking.php" class="btn btn-primary">Book a visit</a>
            <a href="catalogue.php" class="btn btn-ghost">Explore the collection</a>
        </div>
    </div>
</section>

<section class="stats-strip">
    <div class="container stats-grid">
        <div class="stat"><span class="count" data-target="<?= (int)$totalArtifacts ?>">0</span><label>Objects in the collection</label></div>
        <div class="stat"><span class="count" data-target="<?= (int)$onDisplay ?>">0</span><label>On display today</label></div>
        <div class="stat"><span class="count" data-target="<?= (int)$galleryCount ?>">0</span><label>Galleries</label></div>
        <div class="stat"><span class="count" data-target="<?= (int)$visitors ?>">0</span><label>Visitors booked online</label></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>The galleries</h2>
            <p>Each gallery is arranged by our curators and updated as objects return from conservation.</p>
        </div>
        <div class="gallery-grid">
            <?php foreach ($galleries as $i => $g): ?>
            <a class="gallery-card reveal" href="catalogue.php?gallery=<?= (int)$g['id'] ?>">
                <span class="gallery-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <h3><?= e($g['name']) ?></h3>
                <p><?= e($g['description']) ?></p>
                <span class="gallery-meta"><?= e($g['floor']) ?> &middot; <?= (int)$g['items'] ?> objects</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Visitor favourites</h2>
            <p>The objects people look at most on this website.</p>
        </div>
        <div class="artifact-grid">
            <?php foreach ($popular as $a): ?>
            <a class="artifact-card reveal" href="artifact.php?id=<?= (int)$a['id'] ?>">
                <img src="<?= e(img_src($a['image'])) ?>" alt="<?= e($a['name']) ?>">
                <div class="artifact-body">
                    <h3><?= e($a['name']) ?></h3>
                    <p><?= e($a['period']) ?> &middot; <?= e($a['gallery']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container plan-visit">
        <div>
            <h2>Plan your visit</h2>
            <p>Entry is by time slot so the galleries never get crowded. Pick a date, choose a slot that still has space, and your ticket with a QR code is ready instantly.</p>
        </div>
        <ul class="visit-facts">
            <li><strong>Hours</strong> Tue to Sun, 10:00 to 17:30</li>
            <li><strong>Adults</strong> &#8377;<?= PRICE_ADULT ?></li>
            <li><strong>Students</strong> &#8377;<?= PRICE_STUDENT ?></li>
            <li><strong>Slot size</strong> <?= SLOT_CAPACITY ?> visitors</li>
        </ul>
        <a href="booking.php" class="btn btn-primary">Check available slots</a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
