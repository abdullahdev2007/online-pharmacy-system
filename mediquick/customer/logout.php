<?php
/**
 * LOGOUT
 * ---------------------------------------------------------------
 * session_destroy() clears everything PHP knows about this session
 * (user_id, full_name, role, cart contents, etc.) on the server side,
 * so the next request from this browser is treated as a brand new,
 * logged-out visitor.
 */
require_once '../includes/auth.php';
session_destroy();
header("Location: login.php");
exit;
