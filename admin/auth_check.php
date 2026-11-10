<?php
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    header('Location: login.php');
    exit;
}
