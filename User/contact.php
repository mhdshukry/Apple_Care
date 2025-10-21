<?php
session_start();
// Preserve any flash then redirect to the consolidated Contact page used across the app
header('Location: ./contact-us.php');
exit;
