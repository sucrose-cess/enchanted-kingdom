<?php
session_start();
session_unset();   // Clear all session variables
session_destroy(); // Destroy the session on the server

// Redirect back to the home page or login page
header("Location: login.php");
exit;