<?php
echo "<h1>✅ Your XAMPP is working!</h1>";
echo "<p>Project folder: " . __DIR__ . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
phpinfo();
?>