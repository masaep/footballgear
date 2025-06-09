<?php
// dashboard.php
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['id'];
$transactions = [];
$total_income = 0;
$total_expense = 0;
$search_query = ''; // Inisialisasi variabel pencarian

// Ambil query pencarian jika ada
if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    $search_query = trim($_GET['search_query']);
}

// Bangun query SQL dasar
$sql = "SELECT description, amount, type, transaction_date FROM financial_transactions WHERE user_id = ?";
$params = [$user_id];
$types = 'i';

// Tambahkan kondisi pencarian jika ada query
if (!empty($search_query)) {
    $sql .= " AND description LIKE ?";
    $params[] = '%' . $search_query . '%';
    $types .= 's'; // 's' untuk string (deskripsi)
}

$sql .= " ORDER BY transaction_date DESC"; // Urutkan hasil

if ($stmt = $conn->prepare($sql)) {
    // Bind parameter secara dinamis
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
            // Hitung total pemasukan/pengeluaran hanya untuk data yang DITAMPILKAN
            // Jika Anda ingin total keseluruhan tanpa filter search, Anda perlu query terpisah.
            // Untuk kesederhanaan, ini dihitung dari hasil filter search.
            if ($row['type'] == 'income') {
                $total_income += $row['amount'];
            } else {
                $total_expense += $row['amount'];
            }
        }
    } else {
        echo '<div class="message error">Gagal mengambil transaksi: ' . $stmt->error . '</div>';
    }
    $stmt->close();
} else {
    echo '<div class="message error">Gagal mempersiapkan statement: ' . $conn->error . '</div>';
}

// Tambah transaksi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $type = trim($_POST['type']);

    if (!empty($description) && $amount > 0 && ($type == 'income' || $type == 'expense')) {
        $sql = "INSERT INTO financial_transactions (user_id, description, amount, type) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('isds', $user_id, $description, $amount, $type);
            if ($stmt->execute()) {
                header('Location: dashboard.php'); // Refresh halaman
                exit;
            } else {
                echo '<div class="message error">Gagal menambahkan transaksi.</div>';
            }
            $stmt->close();
        }
    } else {
        echo '<div class="message error">Harap lengkapi semua bidang dengan benar.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FootballGear</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Logout</a>
        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard Keuangan</a>
            <a href="employees.php">Manajemen Pegawai</a>
            <a href="reports.php">Laporan Keuangan</a>
        </div>

        <h2>Ringkasan Keuangan</h2>
        <p>Total Pemasukan: Rp <?php echo number_format($total_income, 2, ',', '.'); ?></p>
        <p>Total Pengeluaran: Rp <?php echo number_format($total_expense, 2, ',', '.'); ?></p>
        <p>Saldo Bersih: Rp <?php echo number_format($total_income - $total_expense, 2, ',', '.'); ?></p>

        <h2>Tambah Transaksi Baru</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div>
                <label>Deskripsi</label>
                <input type="text" name="description" required>
            </div>
            <div>
                <label>Jumlah</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            <div>
                <label>Tipe</label>
                <select name="type" required>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>
            <div>
                <input type="submit" name="add_transaction" value="Tambah Transaksi">
            </div>
        </form>

        <h2>Cari Transaksi</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get">
            <div>
                <label>Cari Deskripsi Transaksi:</label>
                <input type="text" name="search_query" value="<?php echo isset($_GET['search_query']) ? htmlspecialchars($_GET['search_query']) : ''; ?>" placeholder="Contoh: Pembelian bola">
            </div>
            <div>
                <input type="submit" value="Cari">
            </div>
        </form>

        <h2>Daftar Transaksi</h2>
        <?php if (empty($transactions)): ?>
            <p>Belum ada transaksi.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                            <td>Rp <?php echo number_format($transaction['amount'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>