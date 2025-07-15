-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 10:44 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_klinik_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `iddokter` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `spesialisasi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`iddokter`, `nama`, `jenis_kelamin`, `telpon`, `alamat`, `spesialisasi`) VALUES
(1, 'Toni', 'L', '081762538264', 'Jl. Mekar No. 9', 'Dokter Umum'),
(2, 'sadasda', 'L', '131411', 'adadad', 'adada');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `idpasien` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `tmp_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `telpon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tgl_daftar` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`idpasien`, `nama`, `jenis_kelamin`, `pekerjaan`, `tmp_lahir`, `tgl_lahir`, `telpon`, `alamat`, `tgl_daftar`) VALUES
(1, 'Tono', 'L', 'Chef', 'Singapore', '2000-01-18', '081131313131', 'Jl. Merdeka no 13', '2025-07-06'),
(18, 'Fanny', 'P', 'Web Developer', 'Bali', '2002-06-20', '0817625382639', 'Jl. Mawar No. 2', '2025-07-11'),
(22, 'Dede', 'L', 'Data Scientist', 'Mataram', '2002-01-05', '081625372638', 'Jl. Belimbing No. 1', '2025-07-11'),
(24, 'Pores', 'P', 'Perawat', 'Sumatra Utara', '2010-06-24', '081525232523', 'Jl. Mekar Sari No. 78', '2025-07-12'),
(25, 'Jarwo', 'L', 'Anggota Ormas', 'Jawa', '1980-09-23', '085917236471', 'Jl. Melayu No. 99', '2025-07-12');

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `idrm` int(11) NOT NULL,
  `idpasien` int(11) NOT NULL,
  `iddokter` int(11) NOT NULL,
  `idruang` int(11) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `idtindakan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ruang`
--

CREATE TABLE `ruang` (
  `idruang` int(11) NOT NULL,
  `nama_ruang` varchar(100) DEFAULT NULL,
  `status` enum('Kosong','Terisi') DEFAULT 'Kosong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruang`
--

INSERT INTO `ruang` (`idruang`, `nama_ruang`, `status`) VALUES
(3, 'Ekonomi', 'Kosong'),
(4, 'Bussines', 'Kosong'),
(5, 'Ekonomi Plus', 'Kosong');

-- --------------------------------------------------------

--
-- Table structure for table `tindakan`
--

CREATE TABLE `tindakan` (
  `idtindakan` int(11) NOT NULL,
  `idpasien` int(11) NOT NULL,
  `kriteria` text DEFAULT NULL,
  `tindakan` text DEFAULT NULL,
  `iddokter` int(11) DEFAULT NULL,
  `fasilitas` text DEFAULT NULL,
  `keputusan_keluarga` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tindakan`
--

INSERT INTO `tindakan` (`idtindakan`, `idpasien`, `kriteria`, `tindakan`, `iddokter`, `fasilitas`, `keputusan_keluarga`) VALUES
(1, 22, 'ad', 'sada', 1, 'ICU', 'sadasd'),
(2, 18, 'sadda', 'adad', 1, 'Laboratory', 'adada'),
(3, 1, 'sasa', 'dfdfd', 1, 'ICU', 'setuju'),
(4, 24, 'Sakit Hati', 'Tidur', 1, 'Pharmacy', 'Setuju');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(8, 'admin', '0192023a7bbd73250516f069df18b500');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`iddokter`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`idpasien`);

--
-- Indexes for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`idrm`),
  ADD KEY `idpasien` (`idpasien`),
  ADD KEY `iddokter` (`iddokter`),
  ADD KEY `idruang` (`idruang`),
  ADD KEY `idtindakan` (`idtindakan`);

--
-- Indexes for table `ruang`
--
ALTER TABLE `ruang`
  ADD PRIMARY KEY (`idruang`);

--
-- Indexes for table `tindakan`
--
ALTER TABLE `tindakan`
  ADD PRIMARY KEY (`idtindakan`),
  ADD KEY `idpasien` (`idpasien`),
  ADD KEY `fk_tindakan_dokter` (`iddokter`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `iddokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `idpasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `idrm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ruang`
--
ALTER TABLE `ruang`
  MODIFY `idruang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tindakan`
--
ALTER TABLE `tindakan`
  MODIFY `idtindakan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`idpasien`) REFERENCES `pasien` (`idpasien`),
  ADD CONSTRAINT `rekam_medis_ibfk_2` FOREIGN KEY (`iddokter`) REFERENCES `dokter` (`iddokter`),
  ADD CONSTRAINT `rekam_medis_ibfk_3` FOREIGN KEY (`idruang`) REFERENCES `ruang` (`idruang`),
  ADD CONSTRAINT `rekam_medis_ibfk_4` FOREIGN KEY (`idtindakan`) REFERENCES `tindakan` (`idtindakan`);

--
-- Constraints for table `tindakan`
--
ALTER TABLE `tindakan`
  ADD CONSTRAINT `fk_tindakan_dokter` FOREIGN KEY (`iddokter`) REFERENCES `dokter` (`iddokter`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tindakan_ibfk_1` FOREIGN KEY (`idpasien`) REFERENCES `pasien` (`idpasien`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
