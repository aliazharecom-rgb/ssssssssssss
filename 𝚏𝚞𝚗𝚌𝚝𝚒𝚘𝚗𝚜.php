<?php
// functions.php
require_once 'config.php';

function callKeyAuthAPI($type, $params = []) {
    $url = KEYAUTH_API_URL;
    
    // Base parameters required by KeyAuth API
    $postData = array_merge([
        'type' => $type,
        'ownerid' => OWNER_ID,
        'name' => APP_NAME,
    ], $params);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['success' => false, 'message' => 'API request failed with HTTP ' . $httpCode];
    }
    
    $data = json_decode($response, true);
    if (!is_array($data)) {
        return ['success' => false, 'message' => 'Invalid API response'];
    }
    
    return $data;
}

function keyauth_init() {
    // Check if we already have a valid session
    if (isset($_SESSION['keyauth_sessionid']) && isset($_SESSION['keyauth_validated']) && $_SESSION['keyauth_validated'] === true) {
        // Verify session is still valid with 'check' endpoint
        $check = callKeyAuthAPI('check', [
            'sessionid' => $_SESSION['keyauth_sessionid']
        ]);
        if (isset($check['success']) && $check['success'] === true) {
            return $check;
        }
        // Session expired or invalid
        session_destroy();
        session_start();
    }
    
    // Initialize new session
    $init = callKeyAuthAPI('init', [
        'ver' => '1.0', // Your app version
        'hash' => '',   // Optional hash check
        'enckey' => ''  // Optional encryption key
    ]);
    
    if (isset($init['success']) && $init['success'] === true && isset($init['sessionid'])) {
        $_SESSION['keyauth_sessionid'] = $init['sessionid'];
        $_SESSION['keyauth_validated'] = false;
        $_SESSION['keyauth_nonce'] = $init['nonce'] ?? '';
        return $init;
    }
    
    return ['success' => false, 'message' => 'Init failed: ' . ($init['message'] ?? 'Unknown error')];
}

function keyauth_login($username, $password, $hwid = null) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    $params = [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'username' => $username,
        'pass' => $password,
    ];
    if ($hwid !== null) {
        $params['hwid'] = $hwid;
    }
    
    $result = callKeyAuthAPI('login', $params);
    
    if (isset($result['success']) && $result['success'] === true) {
        $_SESSION['keyauth_validated'] = true;
        $_SESSION['keyauth_username'] = $username;
        $_SESSION['keyauth_userinfo'] = $result['info'] ?? [];
        $_SESSION['keyauth_nonce'] = $result['nonce'] ?? '';
    }
    
    return $result;
}

function keyauth_register($username, $password, $key, $email = null, $hwid = null) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    $params = [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'username' => $username,
        'pass' => $password,
        'key' => $key,
    ];
    if ($email !== null) {
        $params['email'] = $email;
    }
    if ($hwid !== null) {
        $params['hwid'] = $hwid;
    }
    
    return callKeyAuthAPI('register', $params);
}

function keyauth_upgrade($username, $key) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('upgrade', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'username' => $username,
        'key' => $key,
    ]);
}

function keyauth_fetchStats() {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('fetchStats', [
        'sessionid' => $_SESSION['keyauth_sessionid']
    ]);
}

function keyauth_fetchOnline() {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('fetchOnline', [
        'sessionid' => $_SESSION['keyauth_sessionid']
    ]);
}

function keyauth_getVar($var) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('getvar', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'var' => $var
    ]);
}

function keyauth_setVar($var, $data) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('setvar', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'var' => $var,
        'data' => $data
    ]);
}

function keyauth_chatSend($channel, $message) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('chatsend', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'channel' => $channel,
        'message' => $message
    ]);
}

function keyauth_chatGet($channel) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('chatget', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'channel' => $channel
    ]);
}

function keyauth_file($fileid) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('file', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'fileid' => $fileid
    ]);
}

function keyauth_webhook($webid, $params = '', $body = '', $conttype = '') {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    $data = [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'webid' => $webid,
    ];
    if (!empty($params)) $data['params'] = $params;
    if (!empty($body)) $data['body'] = $body;
    if (!empty($conttype)) $data['conttype'] = $conttype;
    
    return callKeyAuthAPI('webhook', $data);
}

function keyauth_log($message, $pcuser = '') {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('log', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'message' => $message,
        'pcuser' => $pcuser
    ]);
}

function keyauth_ban($reason = '', $hwid = null) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    $data = [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'reason' => $reason
    ];
    if ($hwid !== null) {
        $data['hwid'] = $hwid;
    }
    
    return callKeyAuthAPI('ban', $data);
}

function keyauth_logout() {
    if (!isset($_SESSION['keyauth_sessionid'])) {
        return ['success' => true, 'message' => 'Already logged out'];
    }
    
    $result = callKeyAuthAPI('logout', [
        'sessionid' => $_SESSION['keyauth_sessionid']
    ]);
    
    session_destroy();
    return $result;
}

function keyauth_check() {
    if (!isset($_SESSION['keyauth_sessionid'])) {
        return ['success' => false, 'message' => 'No session'];
    }
    
    return callKeyAuthAPI('check', [
        'sessionid' => $_SESSION['keyauth_sessionid']
    ]);
}

function keyauth_changeUsername($newUsername) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('changeUsername', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'newUsername' => $newUsername
    ]);
}

function keyauth_2faEnable($code = null) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    $data = [
        'sessionid' => $_SESSION['keyauth_sessionid'],
    ];
    if ($code !== null) {
        $data['code'] = $code;
    }
    
    return callKeyAuthAPI('2faenable', $data);
}

function keyauth_2faDisable($code) {
    $init = keyauth_init();
    if (!$init['success']) {
        return $init;
    }
    
    return callKeyAuthAPI('2fadisable', [
        'sessionid' => $_SESSION['keyauth_sessionid'],
        'code' => $code
    ]);
}

// Helper functions
function redirectToLogin() {
    header('Location: index.php');
    exit;
}

function redirectToDashboard() {
    header('Location: dashboard.php');
    exit;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function displayFlash() {
    if (isset($_SESSION['flash'])) {
        echo '<div class="alert alert-' . $_SESSION['flash']['type'] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['flash']['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        unset($_SESSION['flash']);
    }
}

function getHwid() {
    // Simple HWID generation — you can use more complex method
    $components = [
        php_uname('n'),
        php_uname('s'),
        php_uname('r'),
        php_uname('m'),
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    return hash('sha256', implode('|', $components));
}