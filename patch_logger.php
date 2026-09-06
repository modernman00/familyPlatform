<?php
$file = 'app/controller/auth/FamilyCodeApprovalController.php';
$content = file_get_contents($file);
$content = str_replace(
    '$exists = $this->approvalService->familyCodeExists($cleanCode);',
    '$exists = $this->approvalService->familyCodeExists($cleanCode); file_put_contents("cypress_debug.log", "Code: $cleanCode, Exists: " . ($exists ? "1" : "0") . "\n", FILE_APPEND);',
    $content
);
file_put_contents($file, $content);
