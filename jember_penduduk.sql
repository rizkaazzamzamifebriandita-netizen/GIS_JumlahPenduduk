-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 04:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jember_penduduk`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$LjqCZZhavUQtM2NJuGK8teTnsoI7.HZie2J2qrhiqhDu5rYLaZy3u');

-- --------------------------------------------------------

--
-- Table structure for table `kecamatan`
--

CREATE TABLE `kecamatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `jumlah_penduduk` int(11) NOT NULL,
  `laju_pertumbuhan` decimal(5,2) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kecamatan`
--

INSERT INTO `kecamatan` (`id`, `nama`, `jumlah_penduduk`, `laju_pertumbuhan`, `latitude`, `longitude`) VALUES
(1, 'Kencong', 71155, -0.42, -8.285510, 113.358112),
(2, 'Gumuk Mas', 90255, 0.01, -8.333965, 113.413900),
(3, 'Puger', 126660, 0.19, -8.328785, 113.477199),
(4, 'Wuluhan', 129414, 0.56, -8.351245, 113.542359),
(5, 'Ambulu', 150000, 0.64, -8.379440, 113.612737),
(6, 'Tempurejo', 82126, 0.38, -8.419075, 113.752272),
(7, 'Silo', 112043, 0.43, -8.258970, 113.867990),
(8, 'Mayang', 52840, 0.56, -8.206520, 113.811729),
(9, 'Mumbulsari', 70473, 0.53, -8.255755, 113.742095),
(10, 'Jenggawah', 91828, 0.58, -8.288960, 113.631917),
(11, 'Ajung', 86033, 0.62, -8.239380, 113.660642),
(12, 'Rambipuji', 88684, 0.15, -8.230265, 113.598181),
(13, 'Balung', 84749, 0.42, -8.273950, 113.519597),
(14, 'Umbulsari', 79411, -0.27, -8.243335, 113.416520),
(15, 'Semboro', 50011, -0.21, -8.179055, 113.432464),
(16, 'Jombang', 56241, -0.35, -8.224315, 113.355439),
(17, 'Sumberbaru', 116359, -0.33, -8.091140, 113.410436),
(18, 'Tanggul', 94169, -0.24, -8.100540, 113.501462),
(19, 'Bangsalsari', 128748, 0.46, -8.117990, 113.565351),
(20, 'Panti', 67654, 0.52, -8.076495, 113.618488),
(21, 'Sukorambi', 42929, 0.69, -8.129035, 113.662086),
(22, 'Arjasa', 43286, 0.77, -8.101945, 113.734209),
(23, 'Pakusari', 47131, 0.74, -8.154100, 113.774940),
(24, 'Kalisat', 80671, 0.28, -8.124300, 113.807580),
(25, 'Ledokombo', 70559, 0.36, -8.137980, 113.941442),
(26, 'Sumberjambe', 65112, 0.67, -8.069785, 113.932088),
(27, 'Sukowono', 62498, 0.59, -8.058165, 113.823701),
(28, 'Jelbuk', 33938, 0.86, -8.046210, 113.707806),
(29, 'Kaliwates', 127701, 0.60, -8.173680, 113.688720),
(30, 'Sumbersari', 137792, 0.92, -8.173635, 113.728461),
(31, 'Patrang', 103922, 0.51, -8.126835, 113.700827);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `kecamatan`
--
ALTER TABLE `kecamatan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kecamatan`
--
ALTER TABLE `kecamatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
