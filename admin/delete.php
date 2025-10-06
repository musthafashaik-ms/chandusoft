<?php
session_start();
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied.');
}

// Proceed with delete...
