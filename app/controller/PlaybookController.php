<?php

declare(strict_types=1);

namespace App\controller;

use Src\Utility;

final class PlaybookController
{
    /**
     * Public interactive playbook walkthrough.
     */
    public function index(): void
    {
        try {
            Utility::view('playbook', [
                'mode' => 'public',
                'is_admin' => false,
                'page_title' => 'FamilyPlatform Interactive Playbook & Heritage Engine'
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Admin/Operations interactive playbook walkthrough.
     */
    public function adminPlaybook(): void
    {
        try {
            Utility::view('playbook', [
                'mode' => 'admin',
                'is_admin' => true,
                'page_title' => 'FamilyPlatform Operations & Kinship Playbook (Admin Console)'
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * Living Product Guide — How to Use FamilyPlatform.
     */
    public function howToUse(): void
    {
        try {
            Utility::view('howToUse', [
                'page_title' => 'Living Product Guide — How to Use FamilyPlatform'
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }
}
