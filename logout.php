<?php
require_once 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    updateOnlineStatus($conn, $_SESSION['user_id'], 0);
}
session_destroy();
header('Location: login.php');
exit;
?>
