<?php
session_start();
session_unset();
session_destroy();

// ARAHKAN KE HALAMAN LOGIN
header("Location: ../auth/login.php");
exit;
