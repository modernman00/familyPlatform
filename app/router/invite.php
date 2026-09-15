<?php

namespace App\router\invite;

// POST — generate an opaque invite token (authenticated, CSRF-checked)
$router->map('POST', '/api/invite/generate', 'App\controller\InviteController@generate', 'invite_generate');

// GET  — get QR-code URL for a token (authenticated)
$router->map('GET', '/api/invite/qr', 'App\controller\InviteController@qrCode', 'invite_qr');
