<?php
// Ambil environment variables dari Railway
$host = getenv('SUPABASE_DB_HOST');        // Host Supabase
$port = getenv('SUPABASE_DB_PORT') ?: "5432"; // Port default PostgreSQL
$db   = getenv('SUPABASE_DB_NAME') ?: "postgres"; // Nama database
$user = getenv('SUPABASE_DB_USER');        // Username database Supabase
$pass = getenv('SUPABASE_DB_PASSWORD');    // Password database Supabase

// Buat DSN PostgreSQL
$dsn  = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    // Buat koneksi PDO
    $pdo = new PDO($dsn, $user, $pass);

    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
