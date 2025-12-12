<?php
// Ambil environment variables dari Railway
$host = getenv('db.hfqbgmomirkftodfwphi.supabase.co');        // Host Supabase
$port = getenv('5432') ?: "5432"; // Port default PostgreSQL
$db   = getenv('postgres') ?: "postgres"; // Nama database
$user = getenv('postgres');        // Username database Supabase
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

