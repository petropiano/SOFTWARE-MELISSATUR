<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin for local testing; remove in production
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// IGDB credentials (replace with your real ones from igdb.com and twitch.tv)
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Function to get access token
function getAccessToken($clientId, $clientSecret) {
    $url = 'https://id.twitch.tv/oauth2/token';
    $data = [
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
    ];
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        return null;
    }
    $json = json_decode($result, true);
    return $json['access_token'] ?? null;
}

// Handle GET: Fetch games
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = getAccessToken($clientId, $clientSecret);
    if (!$token) {
        echo json_encode(['error' => 'Failed to get access token']);
        exit;
    }

    $url = 'https://api.igdb.com/v4/games';
    $data = 'fields name, cover.image_id, rating; sort rating desc; limit 10; where rating != null;';
    $options = [
        'http' => [
            'header' => "Client-ID: $clientId\r\nAuthorization: Bearer $token\r\nContent-Type: text/plain\r\n",
            'method' => 'POST',
            'content' => $data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        echo json_encode(['error' => 'Failed to fetch games']);
        exit;
    }
    echo $result;
}

// Handle POST: Save rating/review
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['game'], $input['rating'], $input['review'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $reviewsFile = 'reviews.json';
    $reviews = file_exists($reviewsFile) ? json_decode(file_get_contents($reviewsFile), true) : [];
    $reviews[] = [
        'game' => $input['game'],
        'rating' => $input['rating'],
        'review' => $input['review'],
        'date' => date('Y-m-d H:i:s')
    ];
    file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
}
?>