<?php
/*
 * register.php - Create a visitor account.
 * Each new visitor gets a Visitor ID like HHV1002 that is saved in the database.
 */
require_once 'includes/functions.php';

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
if (is_visitor()) {
    header('Location: ' . $next);
    exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'phone'     => trim($_POST['phone'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // ---- Server-side validation (never trust only the JavaScript checks) ----
    if ($old['full_name'] === '' || mb_strlen($old['full_name']) > 100) $errors['full_name'] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))              $errors['email'] = 'Please enter a valid email.';
    if (!preg_match('/^[6-9][0-9]{9}$/', $old['phone']))                $errors['phone'] = 'Enter a 10-digit mobile number.';
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password))     $errors['password'] = 'Use at least 8 characters including a number.';
    if ($password !== $confirm)                                         $errors['confirm'] = 'Passwords do not match.';

    if (!isset($errors['email'])) {
        $check = db()->prepare("SELECT 1 FROM visitors WHERE email = ?");
        $check->execute([$old['email']]);
        if ($check->fetchColumn()) $errors['email'] = 'An account with this email already exists.';
    }

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        $ins = $pdo->prepare("INSERT INTO visitors (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
        $ins->execute([$old['full_name'], $old['email'], $old['phone'], password_hash($password, PASSWORD_DEFAULT)]);
        $id = (int)$pdo->lastInsertId();

        // Visitor ID = HHV + (1000 + database id), e.g. id 2 -> HHV1002
        $code = 'HHV' . (1000 + $id);
        $pdo->prepare("UPDATE visitors SET visitor_code = ? WHERE id = ?")->execute([$code, $id]);
        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['visitor_id'] = $id;
        $_SESSION['welcome'] = $code;            // shown once on the next page
        header('Location: ' . $next);
        exit;
    }
}

$pageTitle  = 'Create Account';
$activePage = 'login';
$pageScripts = ['assets/js/auth.js'];
require 'includes/header.php';
?>

<section class="section auth-section">
    <div class="container auth-wrap">
        <div class="auth-side">
            <p class="eyebrow">Visitor account</p>
            <h1>Create your account</h1>
            <p>It takes a minute. You will get your own Visitor ID, and every ticket you book is saved to your account.</p>
            <ul class="auth-points">
                <li>Pay online with our secure (test) gateway</li>
                <li>Full refund if you cancel 10 or more days ahead</li>
                <li><a href="cancellation-policy.php">Read the cancellation policy</a></li>
            </ul>
        </div>

        <form method="post" class="auth-card" id="registerForm" novalidate>
            <h2>Sign up</h2>
            <?php if ($errors): ?><p class="alert-box error-box">Please fix the highlighted fields.</p><?php endif; ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">

            <div class="field"><label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" maxlength="100" value="<?= e($old['full_name']) ?>" class="<?= isset($errors['full_name']) ? 'invalid' : '' ?>">
                <small class="error"><?= e($errors['full_name'] ?? '') ?></small></div>
            <div class="field"><label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="120" value="<?= e($old['email']) ?>" class="<?= isset($errors['email']) ? 'invalid' : '' ?>">
                <small class="error"><?= e($errors['email'] ?? '') ?></small><small class="ok-text" id="emailOk"></small></div>
            <div class="field"><label for="phone">Mobile number</label>
                <input type="tel" id="phone" name="phone" maxlength="10" placeholder="10 digits" value="<?= e($old['phone']) ?>" class="<?= isset($errors['phone']) ? 'invalid' : '' ?>">
                <small class="error"><?= e($errors['phone'] ?? '') ?></small></div>
            <div class="two-fields">
                <div class="field"><label for="password">Password</label>
                    <div class="pw-wrap"><input type="password" id="password" name="password" class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
                    <button type="button" class="pw-toggle" data-for="password">Show</button></div>
                    <div class="strength"><span id="strengthBar"></span></div>
                    <small class="error"><?= e($errors['password'] ?? '') ?></small></div>
                <div class="field"><label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm" class="<?= isset($errors['confirm']) ? 'invalid' : '' ?>">
                    <small class="error"><?= e($errors['confirm'] ?? '') ?></small></div>
            </div>

            <button class="btn btn-primary btn-block">Create account</button>
            <p class="auth-switch">Already have an account? <a href="login.php?next=<?= e(urlencode($next)) ?>">Log in</a></p>
        </form>
    </div>
</section>

<?php require 'includes/footer.php'; ?>
