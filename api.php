<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    $content = [];
    foreach (db()->query('SELECT content_key, content_value FROM site_content') as $row) $content[$row['content_key']] = $row['content_value'];
    $skills = db()->query('SELECT id, name, percentage, description FROM skills ORDER BY sort_order, id')->fetchAll();
    $projects = db()->query('SELECT id, title, description, category, image_url, live_url, code_url FROM projects ORDER BY sort_order, id')->fetchAll();
    echo json_encode(['content'=>$content, 'skills'=>$skills, 'projects'=>$projects], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Content is temporarily unavailable.']);
}
