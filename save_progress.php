<?php
/**
 * SiApoteker — Save Progress
 * Menerima POST JSON dan menyimpan ke progress.json
 * 
 * Letakkan file ini di server PHP (bukan GitHub Pages statis).
 * Pastikan file progress.json ada di direktori yang sama dan writable.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Baca body request
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// Validasi data
if (!$data || !isset($data['checked']) || !is_array($data['checked'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Format data tidak valid']);
    exit;
}

// Bersihkan: pastikan semua elemen adalah integer
$checked = array_values(array_filter(array_map('intval', $data['checked'])));

// Susun payload
$payload = [
    'version' => 1,
    'updated' => date('c'),           // ISO 8601
    'checked' => $checked,
];

$file = __DIR__ . '/progress.json';

// Tulis ke file
$result = file_put_contents(
    $file,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Gagal menulis file. Periksa permission progress.json']);
    exit;
}

echo json_encode([
    'success' => true,
    'saved'   => count($checked),
    'updated' => $payload['updated'],
]);
