<?php
require 'config.php'; // Contains $igdb_key and DB connection
header('Content-Type: application/json');

if (!isset($_GET['query'])) {
    echo json_encode(['error' => 'Query required']);
    exit;
}

$query = trim($_GET['query']);
$url = 'https://api.igdb.com/v4/games';
$data = "fields name,cover.url,first_release_date,genres.name,platforms.name,summary; search \"$query\"; limit 10;";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Client-ID: ' . $igdb_client_id, // From config
    'Authorization: Bearer ' . $igdb_key,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$games = json_decode($response, true);
// Cache in DB and return
foreach ($games as $game) {
    // Insert/update games table
}
echo json_encode($games);
?>
