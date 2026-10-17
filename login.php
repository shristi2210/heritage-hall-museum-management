<?php
/*
 * login.php - Visitor login (separate from the staff login in admin/).
 * Checks the email and password against the visitors table.
 */
require_once 'includes/functions.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
if (is_visitor()) {
    header('Location: ' . $next);
    exit;
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare("SELECT id, password_hash FROM visitors WHERE email = ?");
    $stmt->execute([$email]);
    $visitor = $stmt->fetch();

    if ($visitor && password_verify($password, $visitor['password_hash'])) {
        session_regenerate_id(true);             // new session id after login (security)
        $_SESSION['visitor_id'] = $visitor['id'];
        header('Location: ' . $next);
        exit;
    }
    $error = 'Wrong email or password.';
}

$pageTitle  = 'Visitor Login';
$activePage = 'login';
$pageScripts = ['assets/js/auth.js'];
require 'includes/header.php';
?>

<section class="section auth-section">
    <div class="container auth-wrap">
        <div class="auth-side">
            <p class="eyebrow">Visitor account</p>
            <h1>Welcome back</h1>
            <p>Log in to book a time slot, pay online, see your tickets and cancel a visit if your plans change.</p>
            <ul class="auth-points">
                <li>Your own Visitor ID</li>
                <li>All your tickets in one place</li>
                <li>Cancel online with an automatic refund</li>
            </ul>
        </div>

        <form method="post" class="auth-card" id="loginForm" novalidate>
            <h2>Log in</h2>
            <?php if ($error): ?><p class="alert-box error-box"><?= e($error) ?></p><?php endif; ?>
            <?php if (isset($_GET['registered'])): ?><p class="alert-box ok-box">Account created. Please log in.</p><?php endif; ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">

            <div class="field"><label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus><small class="error"></small></div>
            <div class="field"><label for="password">Password</label>
                <div class="pw-wrap"><input type="password" id="password" name="password" required>
                <button type="button" class="pw-toggle" data-for="password">Show</button></div><small class="error"></small></div>

            <button class="btn btn-primary btn-block">Log in</button>
            <p class="auth-switch">New here? <a href="register.php?next=<?= e(urlencode($next)) ?>">Create a visitor account</a></p>
            <p class="hint center">Demo account: <strong>demo@heritagehall.in</strong> / <strong>visitor123</strong></p>
        </form>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
