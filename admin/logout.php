<?php
require_once __DIR__ . '/../includes/functions.php';

unset($_SESSION['admin_logged_in']);
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    unset($_SESSION['user_role']);
}

header('Location: login.php');
exit;
