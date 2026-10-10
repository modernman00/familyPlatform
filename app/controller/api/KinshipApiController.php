<?php

declare(strict_types=1);

namespace App\controller\api;

use App\controller\BaseController;
use App\services\ApiKeyAuthService;
use App\services\KinshipCalculatorService;
use App\services\KinshipEntityResolver;
use Src\Db;

/**
 * Universal Headless Kinship REST API Controller
 * 
 * Exposes ego-centric family tree graph data, lineage validation, and relationship
 * calculation to authorized external platforms (PartyPlatform, TenantScore, etc.)
 * under strict cryptographic API key governance.
 * 
 * (c) 2026 The Modernman Platform Group. All Rights Reserved.
 */
final class KinshipApiController extends BaseController
{
    public function __construct()
    {
        // Headless API Controller: Authentication is delegated to ApiKeyAuthService
    }
    /**
     * GET /api/v1/kinship/tree/[*:id]
     * Fetch complete ego-centric family tree graph
     * 
     * @param string|array<string, mixed>|null $id User ID, Node ID, or Family Code
     */
    public function getTree($id = null): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $rawId = is_string($id) ? trim($id) : (string)($_GET['id'] ?? '');

            if (empty($rawId)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Missing parameter: User ID, Node ID, or Family Code required in URL path.'
                ]);
                return;
            }

            $db = Db::connect2();

            // 1. Resolve Family Context and Ego Node
            $familyCode = null;
            $rootUserId = null;
            $egoNodeId = 0;

            // Check if $rawId is a user_id in personal table
            $userStmt = $db->prepare("SELECT id, famCode, firstName, lastName FROM personal WHERE id = ? LIMIT 1");
            $userStmt->execute([$rawId]);
            $userRow = $userStmt->fetch(\PDO::FETCH_ASSOC);

            if ($userRow) {
                $familyCode = (string)$userRow['famCode'];
                $rootUserId = (string)$userRow['id'];
            } else {
                // Check if $rawId is a numeric node_id in family_nodes
                if (is_numeric($rawId)) {
                    $nodeStmt = $db->prepare("SELECT id, family_code, user_id FROM family_nodes WHERE id = ? LIMIT 1");
                    $nodeStmt->execute([(int)$rawId]);
                    $nodeRow = $nodeStmt->fetch(\PDO::FETCH_ASSOC);
                    if ($nodeRow) {
                        $familyCode = (string)$nodeRow['family_code'];
                        $rootUserId = $nodeRow['user_id'] ? (string)$nodeRow['user_id'] : null;
                        $egoNodeId = (int)$nodeRow['id'];
                    }
                }

                // Check if $rawId is directly a family_code
                if (!$familyCode) {
                    $famStmt = $db->prepare("SELECT DISTINCT family_code FROM family_nodes WHERE family_code = ? LIMIT 1");
                    $famStmt->execute([$rawId]);
                    $famCodeRow = $famStmt->fetchColumn();
                    if ($famCodeRow) {
                        $familyCode = (string)$famCodeRow;
                    }
                }
            }

            if (empty($familyCode)) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Family tree not found for the specified identifier.'
                ]);
                return;
            }

            // 2. Authenticate API Key & Verify Tenant Permissions
            $keyRecord = ApiKeyAuthService::authenticate('tree:read', $familyCode);

            // 3. Heal duplicate nodes if any exist
            KinshipEntityResolver::healDuplicateNodes($familyCode);

            // 4. Fetch Nodes, Unions, and Children
            $stmt = $db->prepare("
                SELECT fn.*, 
                       COALESCE(NULLIF(p.firstName, ''), fn.first_name) AS first_name,
                       COALESCE(NULLIF(p.lastName, ''), fn.last_name) AS last_name,
                       CASE 
                           WHEN pp.img IS NOT NULL AND pp.img != '' AND pp.img NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', pp.img)
                           WHEN pp.img IS NOT NULL AND pp.img != '' THEN pp.img
                           WHEN fn.avatar_url IS NOT NULL AND fn.avatar_url != '' AND fn.avatar_url NOT LIKE '%/%' THEN CONCAT('/resources/images/profile/', fn.avatar_url)
                           ELSE fn.avatar_url
                       END AS avatar_url 
                FROM family_nodes fn 
                LEFT JOIN personal p ON fn.user_id = p.id
                LEFT JOIN profilePics pp ON fn.user_id = pp.id 
                WHERE fn.family_code = ? 
                ORDER BY fn.generation_level ASC, fn.id ASC
            ");
            $stmt->execute([$familyCode]);
            $nodes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $unionStmt = $db->prepare("SELECT * FROM family_unions WHERE family_code = ?");
            $unionStmt->execute([$familyCode]);
            $unions = $unionStmt->fetchAll(\PDO::FETCH_ASSOC);

            $childStmt = $db->prepare("
                SELECT fnc.* 
                FROM family_node_children fnc
                JOIN family_unions fu ON fu.id = fnc.union_id
                WHERE fu.family_code = ?
            ");
            $childStmt->execute([$familyCode]);
            $children = $childStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Determine Root Node
            $rootNode = null;
            if ($egoNodeId > 0) {
                foreach ($nodes as $n) {
                    if ((int)$n['id'] === $egoNodeId) {
                        $rootNode = $n;
                        break;
                    }
                }
            }
            if (!$rootNode && !empty($rootUserId)) {
                foreach ($nodes as $n) {
                    if (($n['user_id'] ?? null) === $rootUserId) {
                        $rootNode = $n;
                        break;
                    }
                }
            }
            if (!$rootNode && !empty($nodes)) {
                $rootNode = $nodes[0];
            }

            $effectiveEgoId = $rootNode ? (int)$rootNode['id'] : 0;

            // Format graph structure
            $formattedNodes = [];
            foreach ($nodes as $node) {
                $first = trim((string)($node['first_name'] ?? ''));
                $last = trim((string)($node['last_name'] ?? ''));
                $fullName = trim((string)preg_replace('/\b(\w+)\s+\1\b/iu', '$1', trim($first . ' ' . $last)));

                $formattedNodes[] = [
                    'id' => (int)$node['id'],
                    'user_id' => $node['user_id'] ?? null,
                    'first_name' => $first,
                    'last_name' => $last,
                    'full_name' => $fullName,
                    'gender' => (string)($node['gender'] ?? 'Male'),
                    'generation_level' => (int)($node['generation_level'] ?? 0),
                    'avatar_url' => $node['avatar_url'] ?? null,
                    'bio' => $node['bio'] ?? null,
                    'occupation' => $node['occupation'] ?? null,
                    'location' => $node['location'] ?? null,
                    'is_deceased' => (bool)($node['is_deceased'] ?? false),
                    'is_ego' => ($effectiveEgoId === (int)$node['id'])
                ];
            }

            $formattedUnions = [];
            foreach ($unions as $u) {
                $formattedUnions[] = [
                    'id' => (int)$u['id'],
                    'partner_1_id' => (int)$u['partner_1_id'],
                    'partner_2_id' => (int)$u['partner_2_id'],
                    'union_type' => (string)$u['union_type'],
                    'is_current' => (bool)$u['is_current']
                ];
            }

            $formattedChildren = [];
            foreach ($children as $c) {
                $formattedChildren[] = [
                    'union_id' => (int)$c['union_id'],
                    'child_id' => (int)$c['child_id'],
                    'relationship_type' => (string)$c['relationship_type']
                ];
            }

            $rawGraph = [
                'nodes' => $formattedNodes,
                'unions' => $formattedUnions,
                'children' => $formattedChildren
            ];

            // 5. Compute Ego-Centric Kinship Titles
            $decoratedNodes = KinshipCalculatorService::calculateEgoKinship($rawGraph, $effectiveEgoId);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'authenticated_client' => (string)($keyRecord['name'] ?? 'Client'),
                'family_code' => $familyCode,
                'ego' => [
                    'id' => $effectiveEgoId,
                    'user_id' => $rootNode['user_id'] ?? null,
                    'name' => $rootNode['full_name'] ?? ($rootNode['first_name'] ?? 'Primary Member')
                ],
                'total_members' => count($decoratedNodes),
                'total_unions' => count($formattedUnions),
                'nodes' => array_values($decoratedNodes),
                'unions' => $formattedUnions,
                'children' => $formattedChildren
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal Kinship API Error: ' . $th->getMessage()
            ]);
        }
    }

    /**
     * POST /api/v1/admin/api-keys
     * Provision a new API Key for an authorized internal or partner system
     */
    public function provisionKey(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            // Require Admin Session or Master Key
            if (empty($_SESSION['id']) && empty($_SESSION['admin'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Admin authorization required']);
                return;
            }

            $name = trim((string)($_POST['name'] ?? ''));
            if (empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Client name is required']);
                return;
            }

            $scopes = isset($_POST['scopes']) && is_array($_POST['scopes']) ? $_POST['scopes'] : ['tree:read'];
            $families = isset($_POST['family_codes']) && is_array($_POST['family_codes']) ? $_POST['family_codes'] : ['*'];

            $result = ApiKeyAuthService::generateKey(
                $name,
                $scopes,
                $families,
                120,
                null,
                null,
                (string)($_SESSION['id'] ?? 'admin')
            );

            msgSuccess(201, [
                'message' => 'API Key provisioned successfully. Save this raw key securely; it will not be displayed again.',
                'api_key' => $result['raw_key'],
                'key_id' => $result['id'],
                'name' => htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8'),
                'prefix' => $result['prefix'],
                'scopes' => $result['scopes']
            ]);

        } catch (\Throwable $th) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $th->getMessage()]);
        }
    }
}
