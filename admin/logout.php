<?php
require __DIR__ . '/../app/config.php';  // correct path to config.php
session_unset();
session_destroy();
header("Location: login.php");  // assuming login.php is in admin folder
exit();
