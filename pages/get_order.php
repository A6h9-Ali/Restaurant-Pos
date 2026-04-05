<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error' => 'invalid']); exit; }

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) { echo json_encode(['error' => 'not found']); exit; }

$stmt2 = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt2->execute([$id]);
$items = $stmt2->fetchAll();

echo json_encode(['order' => $order, 'items' => $items]);
