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

// Ambil riwayat transaksi
$stmt2 = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt2->execute(['user_id' => $user_id]);
$transactions = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Persiapkan input untuk API ML
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
$ml_api_url = getenv('https://api-insightx-production.up.railway.app/predict');
$ch = curl_init($ml_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . getenv('ML_API_KEY')
]);
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
<h2>Dashboard</h2>
<p>Selamat datang, <?php echo htmlspecialchars($user['username']); ?></p>
<a href="logout.php">Logout</a>

<h3>Top 3 Rekomendasi</h3>
<ul>
<?php foreach($top3 as $rec): ?>
    <li><?php echo $rec['label'] . " (prob: " . $rec['probability'] . ")"; ?></li>
<?php endforeach; ?>
</ul>

<h3>Riwayat Transaksi</h3>
<ul>
<?php foreach($transactions as $t): ?>
    <li>Produk ID: <?php echo $t['produk_id']; ?> - Total: <?php echo $t['total_harga']; ?></li>
<?php endforeach; ?>
</ul>
</body>
</html>
