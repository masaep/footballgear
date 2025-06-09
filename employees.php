<?php
// employees.php
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Hanya admin yang bisa mengakses halaman ini (untuk demo)
if ($_SESSION['role'] !== 'admin') {
    echo '<div class="container message error">Anda tidak memiliki izin untuk mengakses halaman ini.</div>';
    echo '<div class="container"><a href="dashboard.php">Kembali ke Dashboard</a></div>';
    exit;
}

$employees = [];

// Ambil data pegawai
$sql = "SELECT id, name, position, salary, hire_date FROM employees ORDER BY name ASC";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
    $result->free();
}

// Tambah Pegawai Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $salary = floatval($_POST['salary']);
    $hire_date = trim($_POST['hire_date']);

    if (!empty($name) && !empty($position) && $salary > 0 && !empty($hire_date)) {
        $sql_insert = "INSERT INTO employees (name, position, salary, hire_date) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param('ssds', $name, $position, $salary, $hire_date);
            if ($stmt->execute()) {
                header('Location: employees.php'); // Refresh halaman
                exit;
            } else {
                echo '<div class="message error">Gagal menambahkan pegawai.</div>';
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
    <title>Manajemen Pegawai - FootballGear</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <a href="logout.php" class="logout-button">Logout</a>
        <h1>Manajemen Data Pegawai</h1>
        <div class="nav">
            <a href="dashboard.php">Dashboard Keuangan</a>
            <a href="employees.php">Manajemen Pegawai</a>
            <a href="reports.php">Laporan Keuangan</a>
        </div>

        <h2>Tambah Pegawai Baru</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div>
                <label>Nama Pegawai</label>
                <input type="text" name="name" required>
            </div>
            <div>
                <label>Posisi</label>
                <input type="text" name="position" required>
            </div>
            <div>
                <label>Gaji</label>
                <input type="number" step="0.01" name="salary" required>
            </div>
            <div>
                <label>Tanggal Rekrut</label>
                <input type="date" name="hire_date" required>
            </div>
            <div>
                <input type="submit" name="add_employee" value="Tambah Pegawai">
            </div>
        </form>

        <h2>Daftar Pegawai</h2>
        <?php if (empty($employees)): ?>
            <p>Belum ada data pegawai.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Gaji</th>
                        <th>Tanggal Rekrut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['id']); ?></td>
                            <td><?php echo htmlspecialchars($employee['name']); ?></td>
                            <td><?php echo htmlspecialchars($employee['position']); ?></td>
                            <td>Rp <?php echo number_format($employee['salary'], 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($employee['hire_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>