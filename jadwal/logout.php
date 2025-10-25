<?php
// logout.php
session_start();
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header('Location: login.php');
exit;
?>