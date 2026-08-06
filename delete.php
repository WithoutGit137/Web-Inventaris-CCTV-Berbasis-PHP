<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT serial_number FROM cctv_inventory WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if ($row) {
        $del = $pdo->prepare("DELETE FROM cctv_inventory WHERE id = :id");
        $del->execute(['id' => $id]);
        setFlash('success', 'Data serial number ' . $row['serial_number'] . ' berhasil dihapus.');
    } else {
        setFlash('error', 'Data tidak ditemukan.');
    }
} else {
    setFlash('error', 'ID tidak valid.');
}

header('Location: index.php');
exit;
