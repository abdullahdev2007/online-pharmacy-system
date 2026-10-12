<?php
/**
 * DATABASE CONNECTION
 * ---------------------------------------------------------------
 * Every page that needs to talk to the database starts by including
 * this file (require_once '../config/db.php'). It creates a single
 * $pdo object using PDO (PHP Data Objects), which is PHP's built-in
 * way of talking to a database safely.
 *
 * We use PDO with "prepared statements" everywhere in this project
 * instead of writing raw SQL with variables glued into the string.
 * Prepared statements are the standard defence against SQL Injection
 * attacks, because the database driver keeps the SQL structure and
 * the user's data completely separate — user input can never be
 * interpreted as part of the SQL command itself.
 */

// XAMPP's default MySQL setup: server running on this machine,
// default username "root", and no password set.
$host     = 'localhost';
$dbname   = 'mediquick_pharmacy';
$username = 'root';
$password = ''; // change this if your MySQL root account has a password

try {
    // Build the connection string ("DSN") and open the connection.
    // charset=utf8mb4 lets us store any language/emoji safely.
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Tell PDO to throw real PHP exceptions when something goes wrong
    // (e.g. a bad query) instead of failing silently. This makes bugs
    // much easier to notice and fix during development.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If the database itself can't be reached at all (wrong password,
    // MySQL not running, database not imported yet), stop the page
    // here with a clear message rather than letting the rest of the
    // script run against a connection that doesn't exist.
    die("Database connection failed: " . $e->getMessage());
}
