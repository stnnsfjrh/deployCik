<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil riwayat pembelian
$stmt2 = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt2->execute(['user_id' => $user_id]);
$transactions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Persiapkan data untuk API ML
$input = [
    "input" => [
        "avg_call_duration" => (float)$user['avg_call_duration'],
        "avg_data_usage_gb" => (float)$user['avg_data_usage_gb'],
        "complaint_count" => (int)$user['complaint_count'],
        "device_brand" => $user['device_brand'],
        "monthly_spend" => (float)$user['monthly_spend'],
        "pct_video_usage" => (float)$user['pct_video_usage'],
        "plan_type" => $user['plan_type'],
        "sms_freq" => (int)$user['sms_freq'],
        "topup_freq" => (int)$user['topup_freq'],
        "travel_score" => (float)$user['travel_score']
    ]
];

// Panggil API ML
$api_url = "https://api-insightx-production.up.railway.app/predict";
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$top3 = $result['top_3_recommendations'] ?? [];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
<h1>Selamat datang, <?= htmlspecialchars($user['username']) ?></h1>
<a href="logout.php">Logout</a>

<h2>Top 3 Rekomendasi Paket</h2>
<?php if(count($top3) > 0): ?>
<ul>
    <?php foreach($top3 as $rec): ?>
    <li><?= htmlspecialchars($rec['label']) ?> (Probabilitas: <?= round($rec['probability'],3) ?>)</li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p>Tidak ada rekomendasi.</p>
<?php endif; ?>

<h2>Riwayat Pembelian</h2>
<?php if(count($transactions) > 0): ?>
<table border="1" cellpadding="5">
<tr>
    <th>Produk ID</th>
    <th>Total Harga</th>
    <th>Metode Pembayaran</th>
    <th>Tanggal</th>
</tr>
<?php foreach($transactions as $trx): ?>
<tr>
    <td><?= htmlspecialchars($trx['produk_id']) ?></td>
    <td><?= number_format($trx['total_harga'],0,",",".") ?></td>
    <td><?= htmlspecialchars($trx['metode_pembayaran']) ?></td>
    <td><?= htmlspecialchars($trx['created_at'] ?? '-') ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
<p>Belum ada transaksi.</p>
<?php endif; ?>

</body>
</html>
