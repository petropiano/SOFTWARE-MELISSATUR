<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Remove in production
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost'; 
$dbname = 'pixelteca_db';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// IGDB credentials (replace with real ones)
$clientId = 'YOUR_CLIENT_ID';
$clientSecret = 'YOUR_CLIENT_SECRET';

// Function to get IGDB access token (unchanged)
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

// Handle GET: Fetch games (unchanged, but now with DB connection available)
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

// Handle POST: Save rating/review to DB
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['game'], $input['rating'], $input['review'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO game_reviews (game_name, rating, review) VALUES (?, ?, ?)");
        $stmt->execute([$input['game'], $input['rating'], $input['review']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Failed to save review: ' . $e->getMessage()]);
    }
}
?>
