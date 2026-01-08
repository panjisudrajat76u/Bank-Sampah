<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi database Anda sudah benar

// --- 1. LOGIKA AUTH (LOGIN & LOGOUT) ---
if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek user di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['status'] = "login";
        $_SESSION['nama_user'] = $data['username'];
        header("Location: index.php"); // Refresh halaman
    } else {
        $error_login = "Username atau Password salah!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}

// --- 2. LOGIKA TRANSAKSI (JUAL SAMPAH) ---
$pesan_transaksi = "";
if (isset($_POST['simpan_transaksi'])) {
    // Ambil data dari form
    $nama_penyetor = htmlspecialchars($_POST['nama']);
    $harga_per_kg  = $_POST['jenis_sampah']; // Value dari option adalah harga
    $berat         = $_POST['berat'];
    $tanggal       = date('Y-m-d H:i:s'); // Tanggal otomatis saat ini

    // Tentukan Nama Jenis Sampah berdasarkan Harga
    $jenis_sampah = "Lainnya";
    if($harga_per_kg == 3500) $jenis_sampah = "Botol Plastik (PET)";
    elseif($harga_per_kg == 2000) $jenis_sampah = "Kardus / Karton";
    elseif($harga_per_kg == 14000) $jenis_sampah = "Kaleng Aluminium";
    elseif($harga_per_kg == 75000) $jenis_sampah = "Tembaga Kabel";
    elseif($harga_per_kg == 4500) $jenis_sampah = "Besi Tua";
    elseif($harga_per_kg == 5000) $jenis_sampah = "Minyak Jelantah";

    // Hitung Total Harga
    $total_harga = $harga_per_kg * $berat;

    // Query Insert ke Database (Sesuai request kolom Anda)
    $query_insert = "INSERT INTO transaksi (nama_penyetor, jenis_sampah, berat, total_harga, tanggal) 
                     VALUES ('$nama_penyetor', '$jenis_sampah', '$berat', '$total_harga', '$tanggal')";

    if (mysqli_query($conn, $query_insert)) {
        $pesan_transaksi = "<div class='alert success'><i class='fas fa-check-circle'></i> Transaksi Berhasil! Saldo +Rp " . number_format($total_harga,0,',','.') . "</div>";
    } else {
        $pesan_transaksi = "<div class='alert error'><i class='fas fa-times-circle'></i> Gagal: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoBank | Bank Sampah Digital</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --secondary: #1e293b;
            --bg-body: #f8fafc;
            --white: #ffffff;
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --gradient-main: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        body { background-color: var(--bg-body); color: var(--secondary); line-height: 1.6; }
        a { text-decoration: none; transition: 0.3s; color: inherit; }
        
        /* --- UTILITY CLASSES --- */
        .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
        .btn { padding: 12px 28px; border-radius: 50px; font-weight: 600; cursor: pointer; border: none; font-size: 1rem; transition: 0.3s; display: inline-block; }
        .btn-primary { background: var(--gradient-main); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.4); }
        .btn-outline { background: transparent; border: 2px solid #e2e8f0; color: var(--secondary); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        
        /* --- LOGIN PAGE STYLES --- */
        .login-wrapper {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 10% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 20%);
        }
        .login-card {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 24px;
            box-shadow: var(--shadow-lg);
            text-align: center;
        }
        .login-input {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            font-size: 1rem;
        }
        .login-input:focus { border-color: var(--primary); }

        /* --- DASHBOARD STYLES --- */
        /* Navbar */
        header { position: fixed; top: 0; width: 100%; z-index: 999; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); padding: 1rem 0; }
        .nav-flex { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: 800; color: var(--secondary); display: flex; align-items: center; gap: 8px; }
        .logo i { color: var(--primary); }
        
        /* Hero */
        .hero { padding: 140px 0 80px; text-align: center; }
        .hero h1 { font-size: 3rem; margin-bottom: 15px; line-height: 1.2; }
        .hero span { color: var(--primary); }
        
        /* Form Card */
        .form-card { background: white; padding: 40px; border-radius: 30px; box-shadow: var(--shadow-lg); margin-bottom: 50px; position: relative; z-index: 10; border: 1px solid rgba(0,0,0,0.02); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: center; margin-top: 20px; }
        
        input, select { width: 100%; padding: 15px; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; background: #f8fafc; font-size: 1rem; }
        input:focus, select:focus { border-color: var(--primary); background: white; }
        
        .price-display { font-size: 1.2rem; font-weight: 800; color: var(--primary); background: #ecfdf5; padding: 12px 20px; border-radius: 12px; white-space: nowrap; }

        /* History Table */
        .table-container { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 18px 25px; text-align: left; font-weight: 700; color: #64748b; font-size: 0.9rem; text-transform: uppercase; }
        td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; color: var(--secondary); }
        tr:last-child td { border-bottom: none; }
        
        /* Alerts */
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; text-align: center; }
        .success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* Responsive */
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 2.2rem; }
            .table-container { overflow-x: auto; }
            table { min-width: 600px; }
        }
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") { ?>
    
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo" style="justify-content: center; margin-bottom: 20px;">
                <i class="fas fa-recycle" style="font-size: 2rem;"></i> 
                <span style="font-size: 1.8rem;">EcoBank</span>
            </div>
            <h3 style="margin-bottom: 10px; color: var(--secondary);">Selamat Datang</h3>
            <p style="color: #64748b; margin-bottom: 30px;">Silakan login untuk mulai menyetor sampah.</p>
            
            <?php if(isset($error_login)) { echo "<div class='alert error'>$error_login</div>"; } ?>

            <form action="" method="POST">
                <input type="text" name="username" class="login-input" placeholder="Username" required>
                <input type="password" name="password" class="login-input" placeholder="Password" required>
                <button type="submit" name="login_btn" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    Masuk Sekarang
                </button>
            </form>
            <p style="margin-top: 20px; font-size: 0.9rem; color: #94a3b8;">Belum punya akun? Hubungi Admin.</p>
        </div>
    </div>

    <?php } else { ?>

    <header>
        <div class="container nav-flex">
            <a href="#" class="logo"><i class="fas fa-recycle"></i> EcoBank</a>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="font-weight: 600; color: var(--secondary); display: none; md:block;">
                    Hai, <?= $_SESSION['nama_user'] ?>
                </span>
                <a href="?logout=true" class="btn btn-outline" style="padding: 8px 20px; font-size: 0.9rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Ubah Sampah Jadi <br><span>Saldo Rupiah</span></h1>
            <p style="color: #64748b; max-width: 600px; margin: 0 auto;">
                Setorkan sampah anorganik Anda hari ini. Data tercatat otomatis, transparan, dan menguntungkan.
            </p>
        </div>
    </section>

    <section class="container" id="input-sampah">
        <div class="form-card">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--secondary); margin-bottom: 5px;">
                <i class="fas fa-plus-circle" style="color: var(--primary);"></i> Input Penyetoran
            </h2>
            <p style="color: #64748b; font-size: 0.95rem;">Masukkan data sampah yang diterima.</p>
            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #e2e8f0;">

            <?= $pesan_transaksi ?>

            <form action="" method="POST">
                <div class="form-grid">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">Nama Penyetor</label>
                        <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required>
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">Jenis Sampah</label>
                        <select name="jenis_sampah" id="hargaPerKg" onchange="hitungTotal()">
                            <option value="3500">Botol Plastik (Rp 3.500/kg)</option>
                            <option value="2000">Kardus / Karton (Rp 2.000/kg)</option>
                            <option value="14000">Kaleng Aluminium (Rp 14.000/kg)</option>
                            <option value="75000">Tembaga Kabel (Rp 75.000/kg)</option>
                            <option value="4500">Besi Tua (Rp 4.500/kg)</option>
                            <option value="5000">Minyak Jelantah (Rp 5.000/L)</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">Berat (Kg/L)</label>
                        <input type="number" name="berat" id="beratInput" placeholder="0" min="1" step="0.1" required onkeyup="hitungTotal()">
                    </div>

                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 5px; display: block;">Estimasi Total</label>
                        <div class="price-display" id="displayTotal">Rp 0</div>
                    </div>
                </div>

                <button type="submit" name="simpan_transaksi" class="btn btn-primary" style="width: 100%; margin-top: 25px; padding: 15px;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Data Transaksi
                </button>
            </form>
        </div>
    </section>

    <section class="container" style="padding-bottom: 80px;">
        <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 20px; color: var(--secondary);">
            Riwayat Transaksi
        </h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Penyetor</th>
                        <th>Jenis Sampah</th>
                        <th>Berat</th>
                        <th>Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // --- READ DATABASE ---
                    $no = 1;
                    $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id DESC");
                    
                    if(mysqli_num_rows($data) > 0) {
                        while($d = mysqli_fetch_array($data)) {
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($d['tanggal'])); ?></td>
                        <td style="font-weight: 600; color: var(--secondary);"><?= htmlspecialchars($d['nama_penyetor']); ?></td>
                        <td>
                            <span style="background: #f1f5f9; padding: 5px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: #475569;">
                                <?= $d['jenis_sampah']; ?>
                            </span>
                        </td>
                        <td><?= $d['berat']; ?> Kg</td>
                        <td style="color: var(--primary); font-weight: 700;">
                            Rp <?= number_format($d['total_harga'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color:#94a3b8;'>Belum ada data transaksi.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer style="background: var(--secondary); color: white; padding: 30px 0; text-align: center; margin-top: auto;">
        <div class="container">
            <p style="font-size: 0.9rem; opacity: 0.7;">&copy; 2025 EcoBank Digital. System by PHP Native.</p>
        </div>
    </footer>

    <script>
        function hitungTotal() {
            const harga = document.getElementById('hargaPerKg').value;
            const berat = document.getElementById('beratInput').value;
            const display = document.getElementById('displayTotal');

            if(berat > 0) {
                const total = harga * berat;
                // Format Rupiah
                const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
                display.innerText = formatted;
            } else {
                display.innerText = "Rp 0";
            }
        }
    </script>

    <?php } // End if session ?>

</body>
</html>