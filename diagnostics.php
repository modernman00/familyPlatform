<?php
if (!isset($_GET['token']) || $_GET['token'] !== 'diagnose123') die('Unauthorized');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/init.php';

header('Content-Type: application/json');

try {
    $db = \Src\Db::connect2();
    
    $id = '318422WALLY';
    $famCodes = ['OLAO60446'];
    
    $query = "SELECT post.*, pp.img, rm.requester_id, rm.approver_id, rm.status, rm.requesterCode
              FROM post
              LEFT JOIN profilePics pp ON post.id = pp.id
              LEFT JOIN (
                      SELECT requester_id, approver_id, status, requesterCode
                      FROM requestMgt
                      WHERE requester_id IS NOT NULL AND requester_id = ?
                  ) AS rm ON post.id = rm.approver_id
            WHERE (post.postFamCode IN (?) OR post.id = ?)
            ORDER BY post.post_no DESC
            LIMIT 10 OFFSET 0";
            
    $stmt = $db->prepare($query);
    $stmt->execute([$id, $famCodes[0], $id]);
    
    echo json_encode([
        'query' => $query,
        'params' => [$id, $famCodes[0], $id],
        'results' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ], JSON_PRETTY_PRINT);
    
} catch (\Throwable $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
