<?php
declare(strict_types=1);

/** @var \AltoRouter $router */

// Member Family Journals
$router->map('GET', '/familyJournal', 'App\controller\members\FamilyJournalController@index', 'family_journal_index');
$router->map('GET', '/familyJournal/create', 'App\controller\members\FamilyJournalController@create', 'family_journal_create');
$router->map('POST', '/familyJournal/store', 'App\controller\members\FamilyJournalController@store', 'family_journal_store');
$router->map('POST', '/familyJournal/ai-draft', 'App\controller\members\FamilyJournalController@aiDraft', 'family_journal_ai_draft');
$router->map('GET', '/familyJournal/[i:id]', 'App\controller\members\FamilyJournalController@show', 'family_journal_show');
$router->map('POST', '/familyJournal/[i:id]/delete', 'App\controller\members\FamilyJournalController@delete', 'family_journal_delete');
$router->map('GET', '/familyJournal/[i:id]/edit', 'App\controller\members\FamilyJournalController@edit', 'family_journal_edit');
$router->map('POST', '/familyJournal/[i:id]/update', 'App\controller\members\FamilyJournalController@update', 'family_journal_update');
