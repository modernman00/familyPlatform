<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

try {
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
    $dbName = $_ENV['DB_NAME'] ?? 'family';
    $dbUser = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $count = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    if ($count > 0) {
        echo "Blogs table already has {$count} entries. Skipping seed.\n";
        exit(0);
    }

    // Find first valid author ID from personal table
    $stmtAuthor = $pdo->query("SELECT id FROM personal LIMIT 1");
    $authorId = $stmtAuthor->fetchColumn() ?: null;

    $posts = [
        [
            'title' => 'Why Digital Sovereignty Matters for Your Family Archive',
            'slug' => 'digital-sovereignty-for-family-archives',
            'summary' => 'Public social networks monetize your children’s photos and train advertising algorithms on your personal memories. Here is why private, ad-free family platforms are the future.',
            'content' => '<p>When you post a milestone photo on ad-funded platforms, you trade intimacy for convenience. Your family’s sacred milestones become data points in an advertising broker’s database.</p><h2>Zero Advertising, Absolute Privacy</h2><p>A true family archive must be walled from third-party scrapers and surveillance capitalism. Your family tree belongs solely to the loved ones you explicitly invite.</p>',
            'cover_image' => '/public/img/og-invite.jpg',
            'author_id' => $authorId,
            'status' => 'published',
            'created_at' => '2026-10-17 14:00:00',
        ],
        [
            'title' => 'How to Plan an Unforgettable Multi-Generational Family Reunion',
            'slug' => 'planning-unforgettable-family-reunion',
            'summary' => 'From coordinating dietary needs to organizing engaging heritage games that bridge the gap between toddlers and great-grandparents, here is your definitive reunion blueprint.',
            'content' => '<p>Gathering four generations under one roof is an extraordinary feat of love and logistics. A great family reunion balances structured storytelling with plenty of unhurried downtime.</p><h2>Choose an Accessible Sanctuary</h2><p>Look for spaces that offer step-free access for elders alongside open grounds where children can play safely without traffic concerns.</p><h2>Crowdsource the Family Recipe Book</h2><p>Create a shared album on FamilyPlatform where every branch uploads their signature dish recipe ahead of the celebration.</p>',
            'cover_image' => '/public/img/og-invite.jpg',
            'author_id' => $authorId,
            'status' => 'published',
            'created_at' => '2026-10-16 14:00:00',
        ],
        [
            'title' => '10 Heartwarming Ways to Chronicle Your Grandparents’ Stories',
            'slug' => '10-ways-to-chronicle-grandparents-stories',
            'summary' => 'Preserving oral histories before they fade into the past is one of the greatest gifts you can give future generations. Here are 10 interactive prompts and digital archiving techniques.',
            'content' => '<p>Every grandparent carries a living library of wisdom, triumph, laughter, and resilience. Yet, all too often, their treasured memories remain unrecorded until it is too late.</p><h2>1. The Power of Sensory Memory Prompts</h2><p>Rather than asking broad questions like "Tell me about your childhood," start with sensory triggers: the scent of their mother\'s kitchen, the songs that played on the radio during their first car ride, or the sound of the evening rain on the porch roof.</p><h2>2. Capturing Casual Audio Moments</h2><p>Formal video interviews can feel intimidating. Instead, record casual conversations over afternoon tea using FamilyPlatform\'s voice notes or private archive vault.</p><blockquote>"Family history isn\'t just names on a pedigree chart—it is the living tapestry of love, struggle, and laughter that shaped who we are today."</blockquote><h2>3. Archiving Keepsakes Digitally</h2><p>Take high-resolution photos of handwritten recipe cards, vintage postcards, and family heirlooms, attaching them to their personal node on your private Family Tree.</p>',
            'cover_image' => '/public/img/og-invite.jpg',
            'author_id' => $authorId,
            'status' => 'published',
            'created_at' => '2026-10-15 14:00:00',
        ]
    ];

    $insertStmt = $pdo->prepare("INSERT INTO blogs (title, slug, summary, content, cover_image, author_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($posts as $post) {
        $insertStmt->execute([
            $post['title'],
            $post['slug'],
            $post['summary'],
            $post['content'],
            $post['cover_image'],
            $post['author_id'],
            $post['status'],
            $post['created_at']
        ]);
        echo "Seeded: {$post['title']}\n";
    }

    echo "Successfully seeded " . count($posts) . " blog articles.\n";
} catch (\Throwable $e) {
    echo "Seed Error: " . $e->getMessage() . "\n";
    exit(1);
}
