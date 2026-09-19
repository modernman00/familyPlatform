<?php

declare(strict_types=1);

namespace App\controller\admin;

use App\controller\BaseController;
use Src\Db;
use Src\Utility;
use PDO;

final class AdminMembersController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $verifyJWT = \Src\functionality\SignIn::verify();
        if (empty($verifyJWT['id']) || $verifyJWT['role'] !== 'admin') {
            throw new \Src\Exceptions\UnauthorisedException("Unauthorized access to administrative member management.");
        }
    }

    /**
     * GET /admin/new-registrations
     * Display all pending / newly registered member applications.
     */
    public function newRegistrations(): void
    {
        try {
            $db = Db::connect2();
            $query = "SELECT a.id, a.email, a.status, a.created_at,
                             p.firstName, p.lastName, p.famCode, p.gender,
                             c.country, c.mobile,
                             ofm.father_name AS fatherName, ofm.mother_name AS motherName, ofm.spouse_name AS spouseName
                      FROM account AS a
                      LEFT JOIN personal AS p ON a.id = p.id
                      LEFT JOIN contact AS c ON a.id = c.id
                      LEFT JOIN otherFamily AS ofm ON a.id = ofm.id
                      WHERE LOWER(a.status) = 'new' OR LOWER(a.status) = 'pending'
                      ORDER BY a.created_at DESC";
            
            $stmt = $db->prepare($query);
            $stmt->execute();
            $newRegistrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            parent::viewWithCsp('admin/new_registrations', [
                'newRegistrations' => $newRegistrations,
                'adminPrefix' => '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/'),
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * GET /admin/members
     * Display all registered users with basic info, locations, and last time online.
     */
    public function registeredUsers(): void
    {
        try {
            $db = Db::connect2();
            $query = "SELECT a.id, a.email, a.status, a.type, a.created_at, a.updated_at,
                             p.firstName, p.lastName, p.famCode, p.gender,
                             c.country, c.mobile,
                             pp.img,
                             le.ip_address AS last_ip, le.created_at AS last_login_event
                      FROM account AS a
                      LEFT JOIN personal AS p ON a.id = p.id
                      LEFT JOIN contact AS c ON a.id = c.id
                      LEFT JOIN profilePics AS pp ON a.id = pp.id
                      LEFT JOIN (
                          SELECT user_id, ip_address, created_at,
                                 ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC) as rn
                          FROM login_events
                      ) AS le ON a.id = le.user_id AND le.rn = 1
                      WHERE LOWER(a.status) = 'active' OR LOWER(a.status) = 'approved' OR a.status IS NULL
                      ORDER BY a.id DESC";

            $stmt = $db->prepare($query);
            $stmt->execute();
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compute last online time and online status (active in last 15 mins = 900 secs)
            $now = time();
            foreach ($members as &$member) {
                $lastTimestamp = !empty($member['last_login_event']) ? strtotime((string)$member['last_login_event']) : (!empty($member['updated_at']) ? strtotime((string)$member['updated_at']) : 0);
                $member['is_online'] = ($lastTimestamp > 0 && ($now - $lastTimestamp) <= 900);
                $member['last_online_formatted'] = $lastTimestamp > 0 ? self::formatRelativeTime($lastTimestamp) : 'Never / Offline';
                $member['location'] = !empty($member['country']) ? $member['country'] : (!empty($member['last_ip']) ? $member['last_ip'] : 'Unknown');
            }
            unset($member);

            parent::viewWithCsp('admin/registered_users', [
                'members' => $members,
                'adminPrefix' => '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/'),
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    /**
     * GET /admin/online-users
     * Display users currently active online (activity within last 15 minutes).
     */
    public function onlineUsers(): void
    {
        try {
            $db = Db::connect2();
            $query = "SELECT a.id, a.email, a.status, a.created_at, a.updated_at,
                             p.firstName, p.lastName, p.famCode, p.gender,
                             c.country, c.mobile,
                             pp.img,
                             le.ip_address AS last_ip, le.created_at AS last_login_event
                      FROM account AS a
                      LEFT JOIN personal AS p ON a.id = p.id
                      LEFT JOIN contact AS c ON a.id = c.id
                      LEFT JOIN profilePics AS pp ON a.id = pp.id
                      LEFT JOIN (
                          SELECT user_id, ip_address, created_at,
                                 ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at DESC) as rn
                          FROM login_events
                      ) AS le ON a.id = le.user_id AND le.rn = 1
                      WHERE (LOWER(a.status) = 'active' OR LOWER(a.status) = 'approved' OR a.status IS NULL)
                      ORDER BY a.id DESC";

            $stmt = $db->prepare($query);
            $stmt->execute();
            $allMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $now = time();
            $onlineUsers = [];
            foreach ($allMembers as $member) {
                $lastTimestamp = !empty($member['last_login_event']) ? strtotime((string)$member['last_login_event']) : (!empty($member['updated_at']) ? strtotime((string)$member['updated_at']) : 0);
                if ($lastTimestamp > 0 && ($now - $lastTimestamp) <= 900) {
                    $member['is_online'] = true;
                    $member['last_online_formatted'] = self::formatRelativeTime($lastTimestamp);
                    $member['location'] = !empty($member['country']) ? $member['country'] : (!empty($member['last_ip']) ? $member['last_ip'] : 'Unknown');
                    $onlineUsers[] = $member;
                }
            }

            parent::viewWithCsp('admin/online_users', [
                'onlineUsers' => $onlineUsers,
                'totalOnline' => count($onlineUsers),
                'adminPrefix' => '/' . trim((string)($_ENV['ADMIN_SECRET_PATH'] ?? getenv('ADMIN_SECRET_PATH') ?: 'admin'), '/'),
            ]);
        } catch (\Throwable $th) {
            Utility::showError($th);
        }
    }

    private static function formatRelativeTime(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Just now';
        }
        if ($diff < 3600) {
            $mins = (int) floor($diff / 60);
            return $mins . ($mins === 1 ? ' minute ago' : ' minutes ago');
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours . ($hours === 1 ? ' hour ago' : ' hours ago');
        }
        $days = (int) floor($diff / 86400);
        return $days . ($days === 1 ? ' day ago' : ' days ago');
    }
}
