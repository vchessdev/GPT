<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    case 'get':
        $userId = $_GET['user_id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'user_id required']);
            exit;
        }
        
        $profile = $db->find('profiles', 'user_id', $userId);
        if (!$profile) {
            $user = $db->find('users', 'id', $userId);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                exit;
            }
            
            // Create default profile
            $profile = [
                'user_id' => $userId,
                'bio' => '',
                'avatar' => null,
                'status' => 'online',
                'followers_count' => 0,
                'following_count' => 0,
                'posts_count' => 0,
                'achievements' => [],
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Add user data
        $user = $db->find('users', 'id', $userId);
        $profile['username'] = $user['username'] ?? '';
        $profile['email'] = $user['email'] ?? '';
        
        echo json_encode($profile);
        break;
        
    case 'update':
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $_SESSION['user_id'];
        
        $profile = $db->find('profiles', 'user_id', $userId);
        if ($profile) {
            $updated = $db->update('profiles', $profile['id'], $data);
            echo json_encode($updated);
        } else {
            $data['user_id'] = $userId;
            $created = $db->create('profiles', $data);
            echo json_encode($created);
        }
        break;
        
    case 'setStatus':
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $status = $_POST['status'] ?? 'online';
        $userId = $_SESSION['user_id'];
        
        $profile = $db->find('profiles', 'user_id', $userId);
        if ($profile) {
            $db->update('profiles', $profile['id'], ['status' => $status]);
        } else {
            $db->create('profiles', [
                'user_id' => $userId,
                'status' => $status,
                'bio' => '',
                'followers_count' => 0,
                'following_count' => 0,
                'posts_count' => 0,
                'achievements' => []
            ]);
        }
        
        echo json_encode(['success' => true, 'status' => $status]);
        break;
        
    case 'follow':
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $targetUserId = $_POST['target_user_id'] ?? null;
        if (!$targetUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'target_user_id required']);
            exit;
        }
        
        $currentUserId = $_SESSION['user_id'];
        $followers = $db->read('followers');
        
        // Check if already following
        $alreadyFollowing = array_filter($followers, function($f) use ($currentUserId, $targetUserId) {
            return $f['follower_id'] === $currentUserId && $f['following_id'] === $targetUserId;
        });
        
        if (empty($alreadyFollowing)) {
            $db->create('followers', [
                'follower_id' => $currentUserId,
                'following_id' => $targetUserId
            ]);
            echo json_encode(['success' => true, 'action' => 'followed']);
        } else {
            echo json_encode(['success' => true, 'action' => 'already_following']);
        }
        break;
        
    case 'unfollow':
        if (!isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $targetUserId = $_POST['target_user_id'] ?? null;
        if (!$targetUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'target_user_id required']);
            exit;
        }
        
        $currentUserId = $_SESSION['user_id'];
        $followers = $db->read('followers');
        
        foreach ($followers as $key => $f) {
            if ($f['follower_id'] === $currentUserId && $f['following_id'] === $targetUserId) {
                unset($followers[$key]);
                $db->write('followers', array_values($followers));
                break;
            }
        }
        
        echo json_encode(['success' => true, 'action' => 'unfollowed']);
        break;
        
    case 'getLeaderboard':
        $limit = $_GET['limit'] ?? 10;
        $profiles = $db->read('profiles');
        
        usort($profiles, function($a, $b) {
            $scoreA = ($a['posts_count'] ?? 0) * 10 + ($a['followers_count'] ?? 0) * 5;
            $scoreB = ($b['posts_count'] ?? 0) * 10 + ($b['followers_count'] ?? 0) * 5;
            return $scoreB <=> $scoreA;
        });
        
        $result = array_slice($profiles, 0, $limit);
        echo json_encode(['leaderboard' => $result]);
        break;
        
    case 'getAchievements':
        $userId = $_GET['user_id'] ?? null;
        $profile = $db->find('profiles', 'user_id', $userId);
        
        if (!$profile) {
            echo json_encode(['achievements' => []]);
            exit;
        }
        
        echo json_encode(['achievements' => $profile['achievements'] ?? []]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
?>
