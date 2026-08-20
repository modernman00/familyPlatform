<?php

declare(strict_types=1);

/**
 * FamilyRequestController::request() calls the unqualified showError($err) —
 * PHP resolves an unqualified *function* call by checking the calling
 * namespace first, falling back to the global one only if no match exists.
 * The global showError() (vendor helpers.php) echoes the JSON and then calls
 * exit(), which is correct for a real HTTP request but kills the whole
 * PHPUnit process for any test that deliberately exercises an error path
 * (e.g. the "duplicate pending request" 409 case is normal business logic,
 * not a crash). Defining showError() in the controller's own namespace
 * shadows the global one for calls made from that namespace, so tests can
 * observe the JSON response instead of losing the process — production code
 * is untouched; this file is never loaded outside tests/bootstrap.php.
 */
namespace App\controller\members;

if (!function_exists(__NAMESPACE__ . '\\showError')) {
    function showError(\Throwable $th): void
    {
        \Src\Utility::showError($th);
    }
}
