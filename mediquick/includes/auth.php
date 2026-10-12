<?php
/**
 * AUTHENTICATION & ACCESS CONTROL HELPERS
 * ---------------------------------------------------------------
 * This file is included (directly or indirectly) at the top of every
 * page in the site. It does two things:
 *
 *   1. Starts the PHP session, so we can remember who is logged in
 *      as the user moves from page to page ($_SESSION).
 *   2. Provides two small "gatekeeper" functions that other pages
 *      call to protect themselves: require_login() and
 *      require_role($role).
 *
 * Centralising these checks in one file means every protected page
 * enforces security the same way, and if we ever need to change how
 * login checking works, we only have to change it here instead of
 * in twenty different files.
 */

// session_start() must run before any HTML is sent to the browser.
// It either creates a new session for a first-time visitor, or
// resumes the existing session (reading their session cookie) for
// a returning visitor — this is how $_SESSION['user_id'] "remembers"
// someone across multiple page loads.
session_start();

/**
 * require_login()
 * Call this at the top of any page that should only be visible to
 * someone who is logged in (any role). If nobody is logged in, we
 * immediately redirect them to the login page and stop the script
 * with exit — this guarantees none of the protected page's code
 * below this point ever runs for a logged-out visitor.
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /mediquick/customer/login.php");
        exit;
    }
}

/**
 * require_role($role)
 * Call this at the top of any page that should only be visible to
 * ONE specific role, e.g. require_role('staff') at the top of every
 * staff page. It first makes sure the person is logged in at all
 * (by reusing require_login above), then checks their role matches.
 * If a customer tries to open a staff or admin URL directly by typing
 * it into the address bar, they are stopped here with a 403 error
 * instead of being allowed to see staff-only data.
 */
function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403); // "Forbidden" HTTP status code
        die("Access denied: you do not have permission to view this page.");
    }
}
