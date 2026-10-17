<?php
// logout.php - log the visitor out (staff sessions use admin/logout.php)
require_once 'includes/functions.php';

unset($_SESSION['visitor_id'], $_SESSION['welcome']);
session_regenerate_id(true);
header('Location: index.php');
exit;
