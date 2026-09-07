<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT kondisi, changed_at FROM cctv_kondisi_log WHERE cctv_id = :id ORDER BY changed_at DESC");
    $stmt->execute(['id' => $id]);
    $logs = $stmt->fetchAll();

    if (count($logs) > 0) {
        $bulan_indo = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 
            'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 
            'November' => 'November', 'December' => 'Desember'
        ];

        foreach ($logs as $log) {
            $date = date('d F Y', strtotime($log['changed_at']));
            $time = date('H:i', strtotime($log['changed_at']));
            $date_indo = strtr($date, $bulan_indo);
            
            // PERUBAHAN DI SINI: Menggunakan class 'timeline-item' bukan style inline
            echo "<li class='timeline-item'><strong>" . htmlspecialchars($log['kondisi']) . "</strong> pada " . $date_indo . " pukul " . $time . " WIB</li>";
        }
    } else {
        echo "<li class='timeline-item'>Belum ada riwayat kondisi.</li>";
    }
}