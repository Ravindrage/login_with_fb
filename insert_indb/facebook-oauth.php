<?php
include("connection.php");

// Initialize the session
session_start();
// Update the following variables
// Update the following variables
$facebook_oauth_app_id = '227996444620144';
$facebook_oauth_app_secret = '028cd5e6448c3857d714829f5dda96b1';
// Must be the direct URL to the facebook-oauth.php file
$facebook_oauth_redirect_uri = 'http://localhost/lgFb/insert_indb/facebook-oauth.php';
$facebook_oauth_version = 'v18.0';


// If the captured code param exists and is valid
if (isset($_GET['code']) && !empty($_GET['code'])) {
    // Execute cURL request to retrieve the access token
    
} else {
    // Define params and redirect to Facebook OAuth page
    $params = [
        'client_id' => $facebook_oauth_app_id,
        'redirect_uri' => $facebook_oauth_redirect_uri,
        'response_type' => 'code',
        'scope' => 'email'
    ];
    header('Location: https://www.facebook.com/dialog/oauth?' . http_build_query($params));
    exit;
}

$params = [
    'client_id' => $facebook_oauth_app_id,
    'client_secret' => $facebook_oauth_app_secret,
    'redirect_uri' => $facebook_oauth_redirect_uri,
    'code' => $_GET['code']
];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$response = json_decode($response, true);

if (isset($response['access_token']) && !empty($response['access_token'])) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/' . $facebook_oauth_version . '/me?fields=name,email,picture');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
    $response = curl_exec($ch);
    curl_close($ch);
    $profile = json_decode($response, true);
        // Make sure the profile data exists
    if (isset($profile['email'])) {
        // Check if the account exists in the database
        $stmt = $pdo->prepare('SELECT * FROM accounts WHERE email = ?');
        $stmt->execute([ $profile['email'] ]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
        // If the account does not exist in the database, insert the account into the database
        if (!$account) {
            $stmt = $pdo->prepare('INSERT INTO accounts (email, name, picture, registered, method) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([ $profile['email'], $profile['name'], $profile['picture']['data']['url'], date('Y-m-d H:i:s'), 'facebook' ]);
            $id = $pdo->lastInsertId();
        } else {
            $id = $account['id'];
        }
        // Authenticate the account
        session_regenerate_id();
        $_SESSION['facebook_loggedin'] = TRUE;
        $_SESSION['facebook_id'] = $id;
        // Redirect to profile page
        header('Location: profile.php');
        exit;
    } else {
        exit('Could not retrieve profile information! Please try again later!');
    }
    
} else {
    exit('Invalid access token! Please try again later!');
}