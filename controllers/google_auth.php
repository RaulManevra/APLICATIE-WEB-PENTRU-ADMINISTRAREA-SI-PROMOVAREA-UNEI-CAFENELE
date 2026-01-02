<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/SessionManager.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/oauth_config.php';

// Start session if not already started
SessionManager::start();

// Helper to make API requests
function make_curl_request($url, $postData = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    // SSL Verification (should be true in production, maybe false for local dev if certs are missing)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

$action = $_GET['action'] ?? '';

// 1. Redirect to Google Login
if ($action === 'login') {
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => GOOGLE_SCOPE,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    header('Location: ' . $url);
    exit;
}

// 2. Handle Callback
if (isset($_GET['code'])) {
    // Exchange code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postData = [
        'code' => $_GET['code'],
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $tokenResponse = make_curl_request($tokenUrl, $postData);
    
    if ($tokenResponse['code'] !== 200 || empty($tokenResponse['data']['access_token'])) {
        die("Error fetching access token. Details: " . json_encode($tokenResponse['data']));
    }
    
    $accessToken = $tokenResponse['data']['access_token'];
    
    // Get User Info
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $userResponse = make_curl_request($userInfoUrl, null, ["Authorization: Bearer $accessToken"]);
    
    if ($userResponse['code'] !== 200) {
        die("Error fetching user profile.");
    }
    
    $googleUser = $userResponse['data'];
    $oauth_uid = $googleUser['id'];
    $email = $googleUser['email'];
    $name = $googleUser['name'];
    $picture = $googleUser['picture'] ?? null;
    
    // Check if user exists in DB
    $stmt = $conn->prepare("SELECT id, email, username, role, PuncteFidelitate, PPicture FROM users WHERE oauth_uid = ? OR email = ? LIMIT 1");
    // We check email too, to link accounts if they signed up regularly
    $stmt->bind_param("ss", $oauth_uid, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $existingUser = $result->fetch_assoc();
    $stmt->close();
    
    if ($existingUser) {
        // User exists -> Login
        // Update OAuth UID if missing (linking case)
        if (empty($existingUser['oauth_uid'])) {
            $updateStmt = $conn->prepare("UPDATE users SET oauth_uid = ?, oauth_provider = 'google' WHERE id = ?");
            $updateStmt->bind_param("si", $oauth_uid, $existingUser['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }

        // Always update profile picture from Google as requested
        // Only update profile picture if it's strictly the default one or empty
        $currentPic = $existingUser['PPicture'] ?? '';
        $isDefault = ($currentPic === 'assets/public/default.png' || $currentPic === '' || $currentPic === null);
        
        if ($picture && $isDefault) {
             $updatePicStmt = $conn->prepare("UPDATE users SET PPicture = ? WHERE id = ?");
             $updatePicStmt->bind_param("si", $picture, $existingUser['id']);
             $updatePicStmt->execute();
             $updatePicStmt->close();
             $existingUser['PPicture'] = $picture; // Update local array for session
        }
        
        SessionManager::login($existingUser);
        header('Location: ../index.php'); // Redirect to home/dashboard
        exit;
        
    } else {
        // New User -> Create
        $role = 'user';
        $points = 0;
        // Username defaults to name, sanitize if needed
        // Assuming database allows duplicates on username? Or unique? 
        // Existing schema usually has UNIQUE on username. We might need logic here.
        // For now, let's try to use the name or email prefix.
        
        $baseUsername = str_replace(' ', '', $name);
        // If empty or strange, fallback to email prefix
        if (empty($baseUsername)) {
            $parts = explode('@', $email);
            $baseUsername = $parts[0];
        }
        
        // Ensure unique username loop (basic)
        $finalUsername = $baseUsername;
        $counter = 1;
        while (true) {
             $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
             $checkStmt->bind_param("s", $finalUsername);
             $checkStmt->execute();
             if ($checkStmt->get_result()->num_rows === 0) {
                 $checkStmt->close();
                 break;
             }
             $checkStmt->close();
             $finalUsername = $baseUsername . $counter;
             $counter++;
        }
        
        $insertStmt = $conn->prepare("INSERT INTO users (username, email, role, PuncteFidelitate, PPicture, oauth_provider, oauth_uid) VALUES (?, ?, ?, ?, ?, 'google', ?)");
        // PPicture can be the google url
        $insertStmt->bind_param("sssiss", $finalUsername, $email, $role, $points, $picture, $oauth_uid);
        
        if ($insertStmt->execute()) {
            $newUserId = $insertStmt->insert_id;
            $insertStmt->close();
            
            // Prepare session data
            $newUser = [
                'id' => $newUserId,
                'email' => $email,
                'username' => $finalUsername,
                'role' => $role,
                'PuncteFidelitate' => $points,
                'PPicture' => $picture
            ];
            
            SessionManager::login($newUser);
            header('Location: ../index.php');
            exit;
        } else {
            die("Error creating user: " . $conn->error);
        }
    }
}

// Verify params if needed or default error
if (!isset($_GET['code']) && $action !== 'login') {
    echo "Invalid request.";
}
