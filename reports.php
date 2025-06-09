<?php
// reports.php
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Untuk tujuan UAS, kita akan menyederhanakan laporan ini.
// Di dunia nyata, ini akan melibatkan query kompleks, filter, dan mungkin pembuatan PDF/Excel.

$report_data = [];

// Contoh laporan: Semua transaksi terbaru (tidak memandang user, ini adalah "data sensitif" untuk pengujian)
// Dalam aplikasi nyata, akses ke laporan 'sensitif' ini mungkin hanya untuk peran 'admin' atau dengan otentikasi lebih ketat.
if ($_SESSION['role'] === 'admin') { // Hanya admin yang bisa melihat semua transaksi
    $sql = "SELECT u.username, ft.description, ft.amount, ft.type, ft.transaction_date 
            FROM financial_transactions ft JOIN users u ON ft.user_id = u.id 
            ORDER BY ft.transaction_date DESC LIMIT 50"; // Ambil 50 transaksi terakhir
    if ($result = $conn->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
        }
        $result->free();
    }
} else {
    // Jika bukan admin, mungkin bisa melihat laporannya sendiri, atau tidak sama sekali
    $sql = "SELECT description, amount, type, transaction_date 
            FROM financial_transactions WHERE user_id = ?
            ORDER BY transaction_date DESC LIMIT 20";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $_SESSION['id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;
            }
        }
        $stmt->close();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan Sensitif - FootballGear</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Logout</a>
        <h1>Modul Laporan Keuangan Sensitif</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard Keuangan</a>
            <a href="employees.php">Manajemen Pegawai</a>
            <a href="reports.php">Laporan Keuangan</a>
        </div>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <p class="message success">Anda masuk sebagai Administrator. Anda dapat melihat semua transaksi.</p>
        <?php else: ?>
            <p class="message">Anda masuk sebagai Pengguna Biasa. Anda hanya dapat melihat transaksi Anda sendiri.</p>
        <?php endif; ?>

        <h2>Detail Transaksi Terbaru</h2>
        <?php if (empty($report_data)): ?>
            <p>Tidak ada data laporan yang tersedia.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <th>Pengguna</th>
                        <?php endif; ?>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Tipe</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_data as $row): ?>
                        <tr>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td>Rp <?php echo number_format($row['amount'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['type'] == 'income' ? 'Pemasukan' : 'Pengeluaran'); ?></td>
                            <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>