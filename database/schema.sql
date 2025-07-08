-- Klinik Management System Database Schema
-- Created for PHP + MySQL + Bootstrap 5

-- Create database
CREATE DATABASE IF NOT EXISTS db_klinik_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_klinik_management;

-- Users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'dokter', 'staff') DEFAULT 'staff',
    nama_lengkap VARCHAR(100) NOT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE pasien (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_rm VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    golongan_darah ENUM('A', 'B', 'AB', 'O') NULL,
    alergi TEXT,
    riwayat_penyakit TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Doctors table
CREATE TABLE dokter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    spesialisasi VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Rooms table
CREATE TABLE ruang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    lantai VARCHAR(10) NOT NULL,
    kapasitas INT DEFAULT 1,
    status ENUM('Tersedia', 'Terpakai', 'Maintenance', 'Tidak Tersedia') DEFAULT 'Tersedia',
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Diagnosis table
CREATE TABLE diagnosis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_icd VARCHAR(20) UNIQUE NOT NULL,
    nama_diagnosis VARCHAR(200) NOT NULL,
    kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Medical procedures table
CREATE TABLE tindakan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    ruang_id INT NOT NULL,
    jenis_tindakan VARCHAR(200) NOT NULL,
    diagnosis TEXT NOT NULL,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    catatan TEXT,
    biaya DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Selesai', 'Sedang Berlangsung', 'Dibatalkan') DEFAULT 'Sedang Berlangsung',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON DELETE CASCADE,
    FOREIGN KEY (ruang_id) REFERENCES ruang(id) ON DELETE CASCADE
);

-- Appointments table
CREATE TABLE janji_temu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    ruang_id INT NOT NULL,
    tanggal_janji DATE NOT NULL,
    waktu_janji TIME NOT NULL,
    keluhan TEXT,
    status ENUM('Dijadwalkan', 'Selesai', 'Dibatalkan', 'Tidak Hadir') DEFAULT 'Dijadwalkan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON DELETE CASCADE,
    FOREIGN KEY (ruang_id) REFERENCES ruang(id) ON DELETE CASCADE
);

-- Medical records table
CREATE TABLE rekam_medis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pasien_id INT NOT NULL,
    dokter_id INT NOT NULL,
    tanggal_pemeriksaan DATE NOT NULL,
    keluhan TEXT,
    pemeriksaan_fisik TEXT,
    diagnosis TEXT,
    rencana_pengobatan TEXT,
    obat_diberikan TEXT,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pasien_id) REFERENCES pasien(id) ON DELETE CASCADE,
    FOREIGN KEY (dokter_id) REFERENCES dokter(id) ON DELETE CASCADE
);

-- System settings table
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_klinik VARCHAR(200) NOT NULL,
    alamat_klinik TEXT,
    telepon_klinik VARCHAR(20),
    email_klinik VARCHAR(100),
    jam_operasional VARCHAR(100),
    logo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Activity log table
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aktivitas VARCHAR(200) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user
INSERT INTO users (username, password, email, role, nama_lengkap) VALUES 
('admin', MD5('admin123'), 'admin@klinik.com', 'admin', 'Administrator');

-- Insert sample data for testing
INSERT INTO pasien (no_rm, nama, tanggal_lahir, jenis_kelamin, alamat, telepon) VALUES
('RM20241201001', 'Ahmad Rizki', '1990-05-15', 'Laki-laki', 'Jl. Sudirman No. 123, Jakarta', '081234567890'),
('RM20241201002', 'Siti Nurhaliza', '1985-08-20', 'Perempuan', 'Jl. Thamrin No. 456, Jakarta', '081234567891'),
('RM20241201003', 'Budi Santoso', '1992-03-10', 'Laki-laki', 'Jl. Gatot Subroto No. 789, Jakarta', '081234567892');

INSERT INTO dokter (nip, nama, spesialisasi, alamat, telepon, email) VALUES
('DOK001', 'Dr. Sarah Johnson', 'Dokter Umum', 'Jl. Medan Merdeka No. 1, Jakarta', '081234567893', 'sarah.johnson@klinik.com'),
('DOK002', 'Dr. Michael Chen', 'Dokter Gigi', 'Jl. Sudirman No. 100, Jakarta', '081234567894', 'michael.chen@klinik.com'),
('DOK003', 'Dr. Lisa Wong', 'Dokter Mata', 'Jl. Thamrin No. 200, Jakarta', '081234567895', 'lisa.wong@klinik.com');

INSERT INTO ruang (nama, jenis, lantai, kapasitas, status) VALUES
('Ruang Konsultasi 1', 'Ruang Konsultasi', '1', 2, 'Tersedia'),
('Ruang Konsultasi 2', 'Ruang Konsultasi', '1', 2, 'Tersedia'),
('Ruang Pemeriksaan 1', 'Ruang Pemeriksaan', '2', 1, 'Tersedia'),
('Ruang Operasi', 'Ruang Operasi', '3', 5, 'Tersedia'),
('Ruang Rawat Inap 1', 'Ruang Rawat Inap', '4', 2, 'Tersedia');

INSERT INTO diagnosis (kode_icd, nama_diagnosis, kategori) VALUES
('A00.0', 'Kolera akibat Vibrio cholerae 01, biovar cholerae', 'Penyakit Infeksi'),
('I10', 'Hipertensi esensial (primer)', 'Penyakit Jantung'),
('J45.0', 'Asma dengan predominasi komponen alergi', 'Penyakit Paru-paru'),
('K29.0', 'Gastritis hemoragik akut', 'Penyakit Pencernaan'),
('G40.0', 'Epilepsi idiopatik dengan kejang parsial', 'Penyakit Saraf');

INSERT INTO tindakan (pasien_id, dokter_id, ruang_id, jenis_tindakan, diagnosis, tanggal, waktu, biaya) VALUES
(1, 1, 1, 'Konsultasi Umum', 'Pemeriksaan rutin', '2024-12-01', '09:00:00', 150000),
(2, 2, 3, 'Pemeriksaan Gigi', 'Karies gigi', '2024-12-01', '10:00:00', 200000),
(3, 3, 2, 'Pemeriksaan Mata', 'Mata minus', '2024-12-01', '11:00:00', 180000);

INSERT INTO pengaturan (nama_klinik, alamat_klinik, telepon_klinik, email_klinik, jam_operasional) VALUES
('Klinik Sejahtera', 'Jl. Sudirman No. 123, Jakarta Pusat', '021-1234567', 'info@kliniksejahtera.com', 'Senin - Jumat: 08:00 - 17:00, Sabtu: 08:00 - 12:00');

-- Create indexes for better performance
CREATE INDEX idx_pasien_no_rm ON pasien(no_rm);
CREATE INDEX idx_pasien_nama ON pasien(nama);
CREATE INDEX idx_dokter_nip ON dokter(nip);
CREATE INDEX idx_dokter_nama ON dokter(nama);
CREATE INDEX idx_tindakan_tanggal ON tindakan(tanggal);
CREATE INDEX idx_tindakan_pasien ON tindakan(pasien_id);
CREATE INDEX idx_tindakan_dokter ON tindakan(dokter_id);
CREATE INDEX idx_ruang_status ON ruang(status);
CREATE INDEX idx_diagnosis_kode ON diagnosis(kode_icd);
CREATE INDEX idx_users_username ON users(username);

-- Create views for common queries
CREATE VIEW v_tindakan_lengkap AS
SELECT 
    t.id,
    t.jenis_tindakan,
    t.diagnosis,
    t.tanggal,
    t.waktu,
    t.biaya,
    t.status as status_tindakan,
    p.nama as nama_pasien,
    p.no_rm,
    d.nama as nama_dokter,
    d.spesialisasi,
    r.nama as nama_ruang,
    r.jenis as jenis_ruang
FROM tindakan t
JOIN pasien p ON t.pasien_id = p.id
JOIN dokter d ON t.dokter_id = d.id
JOIN ruang r ON t.ruang_id = r.id
ORDER BY t.tanggal DESC, t.waktu DESC;

CREATE VIEW v_statistik_dashboard AS
SELECT 
    (SELECT COUNT(*) FROM pasien) as total_pasien,
    (SELECT COUNT(*) FROM dokter WHERE status = 'Aktif') as total_dokter,
    (SELECT COUNT(*) FROM ruang WHERE status = 'Tersedia') as ruang_tersedia,
    (SELECT COUNT(*) FROM tindakan WHERE DATE(tanggal) = CURDATE()) as tindakan_hari_ini,
    (SELECT COUNT(*) FROM tindakan WHERE DATE(tanggal) = CURDATE() - INTERVAL 1 DAY) as tindakan_kemarin;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE GetPasienByNoRM(IN no_rm_param VARCHAR(20))
BEGIN
    SELECT * FROM pasien WHERE no_rm = no_rm_param;
END //

CREATE PROCEDURE GetTindakanByDateRange(IN start_date DATE, IN end_date DATE)
BEGIN
    SELECT 
        t.*,
        p.nama as nama_pasien,
        d.nama as nama_dokter,
        r.nama as nama_ruang
    FROM tindakan t
    JOIN pasien p ON t.pasien_id = p.id
    JOIN dokter d ON t.dokter_id = d.id
    JOIN ruang r ON t.ruang_id = r.id
    WHERE t.tanggal BETWEEN start_date AND end_date
    ORDER BY t.tanggal DESC, t.waktu DESC;
END //

CREATE PROCEDURE GetRuangTersedia(IN tanggal_param DATE, IN waktu_param TIME)
BEGIN
    SELECT r.*
    FROM ruang r
    WHERE r.status = 'Tersedia'
    AND r.id NOT IN (
        SELECT DISTINCT ruang_id 
        FROM tindakan 
        WHERE tanggal = tanggal_param 
        AND waktu = waktu_param
    );
END //

DELIMITER ;

-- Grant permissions (adjust as needed)
-- GRANT ALL PRIVILEGES ON db_klinik_management.* TO 'klinik_user'@'localhost' IDENTIFIED BY 'your_password';
-- FLUSH PRIVILEGES; 