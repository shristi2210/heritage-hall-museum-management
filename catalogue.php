<?php
/*
 * catalogue.php - Searchable collection
 * The page loads once; results are fetched with Ajax from api/artifacts.php
 * every time the visitor types or changes a filter (see assets/js/catalogue.js).
 */
$pageTitle  = 'The Collection';
$activePage = 'catalogue';
$pageScripts = ['assets/js/catalogue.js'];
require 'includes/header.php';

$pdo = db();
// Filter options come from the database so they stay in sync with the collection
$categories = $pdo->query("SELECT DISTINCT category FROM artifacts WHERE gallery_id IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$eras       = $pdo->query("SELECT DISTINCT era FROM artifacts WHERE gallery_id IS NOT NULL ORDER BY FIELD(era,'Ancient','Medieval','Modern')")->fetchAll(PDO::FETCH_COLUMN);
$galleries  = $pdo->query("SELECT id, name FROM galleries ORDER BY id")->fetchAll();
$preGallery = (int)($_GET['gallery'] ?? 0);   // when coming from a gallery card on the home page
?>

<section class="page-head">
    <div class="container">
        <p class="eyebrow">The Collection</p>
        <h1>Search everything on display</h1>
        <p>Results update as you type. Click any object to read its story.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form id="filterForm" class="filter-bar" autocomplete="off">
            <input type="search" id="q" name="q" placeholder="Search by name, material or place (e.g. bronze, Pune)">
            <select id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= e($c) ?>"><?= e($c) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="era" name="era">
                <option value="">All eras</option>
                <?php foreach ($eras as $er): ?>
                <option value="<?= e($er) ?>"><?= e($er) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="gallery" name="gallery">
                <option value="">All galleries</option>
                <?php foreach ($galleries as $g): ?>
                <option value="<?= (int)$g['id'] ?>" <?= $preGallery === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="sort" name="sort">
                <option value="popular">Most viewed</option>
                <option value="name">Name A&ndash;Z</option>
                <option value="oldest">Oldest first</option>
            </select>
        </form>

        <div class="result-info">
            <span id="resultCount">Loading&hellip;</span>
            <button type="button" id="clearFilters" class="link-btn">Clear filters</button>
        </div>

        <div id="results" class="artifact-grid"></div>

        <div id="noResults" class="empty-state" hidden>
            <h3>Nothing matches that search</h3>
            <p>Try a different word, or clear the filters.</p>
        </div>
    </div>
</section>

<!-- jQuery UI Dialog: filled with Ajax when an artifact card is clicked -->
<div id="artifactDialog" title="Artifact" hidden>
    <div class="dialog-body">
        <img id="dlgImage" src="" alt="">
        <div>
            <p class="dlg-period" id="dlgPeriod"></p>
            <p id="dlgDescription"></p>
            <dl class="facts">
                <dt>Material</dt><dd id="dlgMaterial"></dd>
                <dt>Origin</dt><dd id="dlgOrigin"></dd>
                <dt>Category</dt><dd id="dlgCategory"></dd>
                <dt>Where to see it</dt><dd id="dlgGallery"></dd>
            </dl>
            <a id="dlgLink" class="btn btn-small" href="#">Open full page</a>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
