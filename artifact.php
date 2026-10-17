<?php
/*
 * artifact.php?id=N - Full page for one artifact.
 * This is the page a visitor reaches by scanning the QR code next to an exhibit.
 */
require_once 'includes/functions.php';

$id   = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare(
    "SELECT a.*, g.name AS gallery, g.floor
     FROM artifacts a LEFT JOIN galleries g ON g.id = a.gallery_id
     WHERE a.id = ?"
);
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a) {
    http_response_code(404);
}
if ($a) {
    db()->prepare("UPDATE artifacts SET views = views + 1 WHERE id = ?")->execute([$id]);

    // "More from this gallery" - other objects in the same room
    $related = [];
    if ($a['gallery_id']) {
        $r = db()->prepare("SELECT id, name, period, image FROM artifacts WHERE gallery_id = ? AND id <> ? ORDER BY position LIMIT 3");
        $r->execute([$a['gallery_id'], $id]);
        $related = $r->fetchAll();
    }
}

$pageTitle  = $a ? $a['name'] : 'Not found';
$activePage = 'catalogue';
require 'includes/header.php';
?>

<?php if (!$a): ?>
<section class="section"><div class="container empty-state">
    <h2>We couldn't find that object</h2>
    <p><a href="catalogue.php">Back to the collection</a></p>
</div></section>
<?php else: ?>

<section class="section">
    <div class="container artifact-detail">
        <img class="detail-img" src="<?= e(img_src($a['image'])) ?>" alt="<?= e($a['name']) ?>">
        <div>
            <p class="eyebrow"><?= e($a['category']) ?> &middot; <?= e($a['era']) ?></p>
            <h1><?= e($a['name']) ?></h1>
            <p class="detail-period"><?= e($a['period']) ?></p>
            <p class="detail-text"><?= nl2br(e($a['description'])) ?></p>
            <dl class="facts">
                <dt>Material</dt><dd><?= e($a['material'] ?: '-') ?></dd>
                <dt>Origin</dt><dd><?= e($a['origin'] ?: '-') ?></dd>
                <dt>Location</dt>
                <dd><?= $a['gallery'] ? e($a['gallery']) . ' gallery, ' . e($a['floor']) : 'Currently in storage' ?></dd>
            </dl>
            <a href="catalogue.php" class="btn btn-ghost-dark">&larr; Back to the collection</a>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
<section class="section section-alt">
    <div class="container">
        <div class="section-head"><h2>Also in this gallery</h2></div>
        <div class="artifact-grid three">
            <?php foreach ($related as $r): ?>
            <a class="artifact-card" href="artifact.php?id=<?= (int)$r['id'] ?>">
                <img src="<?= e(img_src($r['image'])) ?>" alt="<?= e($r['name']) ?>">
                <div class="artifact-body"><h3><?= e($r['name']) ?></h3><p><?= e($r['period']) ?></p></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
