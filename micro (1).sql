-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 08, 2024 at 06:29 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `micro`
--

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `id` int(11) NOT NULL,
  `bname` varchar(255) NOT NULL,
  `bcode` varchar(255) NOT NULL,
  `center` varchar(255) NOT NULL,
  `ccode` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`id`, `bname`, `bcode`, `center`, `ccode`, `group`) VALUES
(6, 'Warakapola', 'G06', 'Mahena', '', '05'),
(7, 'kegalle', 'G01', 'Molagoda', '', '002'),
(9, 'Rikillagaskada', 'G03', 'Rl', '', '002'),
(11, 'kandy', 'g012', 'nu', '', '12'),
(12, 'Kurunagala', 'G03', 'Rl', '', ''),
(13, 'Kurunagala', 'G03', 'Rl', '', '12'),
(14, 'Kurunagala', 'G03', 'Rl', '', '13'),
(15, 'Kurunagala', 'G03', 'Rl', '', '122'),
(16, 'kandy', 'g012', 'nui', '', '12'),
(18, 'Kurunagala', 'G03', 'Rl', '', '67'),
(19, 'Kurunagala', 'G03', 'Rl', '', '222'),
(20, 'Kegalle', 'G01', 'Karadupana', '', '12'),
(21, 'Kegalle', 'G01', 'Molagoda', '', '122'),
(22, 'Kegalle', 'G01', 'Molagoda', '', '123'),
(24, 'Kaduwela', 'K001', 'Walivita', 'V002', '02'),
(25, 'Kaduwela', 'K001', 'Walivita', '', '03'),
(26, 'Kaduwela', 'K001', 'Walivita', '', '11'),
(27, 'Kaduwela', 'K001', 'Walivita', 'V01', '02'),
(28, 'Kaduwela', 'K001', 'Malabe', 'V02', '01'),
(30, 'Kaduwela', 'K001', 'kelaniya', 'KE1', '01'),
(31, 'Kaduwela', 'K001', 'kelaniya', 'KE1', '11'),
(32, 'Kaduwela', 'K001', 'kelaniya', 'KE1', '766'),
(33, 'Kaduwela', 'K001', 'Walivita', 'V01', '116'),
(34, 'Kaduwela', 'K001', 'Malabe', 'V02', '15'),
(35, 'Kaduwela', 'K001', 'Malabe', 'V02', '02'),
(36, 'Kaduwela', 'K001', 'Malabe', 'V02', '03'),
(37, 'Kaduwela', 'K001', 'Kothalawala', 'KO01', '01');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `bname` varchar(255) NOT NULL,
  `bcode` varchar(255) NOT NULL,
  `center` varchar(255) NOT NULL,
  `ccode` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `nic` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone1` varchar(255) NOT NULL,
  `phone2` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `loan_amount` varchar(255) NOT NULL,
  `period` varchar(255) NOT NULL,
  `interest` varchar(255) NOT NULL,
  `customer_code` varchar(255) NOT NULL,
  `loan_date` varchar(255) NOT NULL,
  `loan_code` varchar(255) NOT NULL,
  `due_date` varchar(255) NOT NULL,
  `payment` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` varchar(255) NOT NULL,
  `loan_balance` decimal(10,2) DEFAULT 0.00,
  `full_loan` varchar(255) NOT NULL,
  `week_payment` varchar(255) NOT NULL,
  `document_fee` varchar(255) NOT NULL,
  `insurance_fee` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`bname`, `bcode`, `center`, `ccode`, `group`, `name`, `cname`, `nic`, `address`, `phone1`, `phone2`, `image`, `loan_amount`, `period`, `interest`, `customer_code`, `loan_date`, `loan_code`, `due_date`, `payment`, `payment_date`, `loan_balance`, `full_loan`, `week_payment`, `document_fee`, `insurance_fee`) VALUES
('Kaduwela', 'K001', 'kelaniya', 'KE1', '01', 'Ravidu', 'Pathum Himantha', '69', 'Kegalle', '0719158514', '', '', '12332', '12%', '12 Weeks', 'K001/KE1/01/01', '2024-03-27', 'K001/KE1/01/20240327', '', '0.00', '2024-03-27', '-10668.00', '', '', '', ''),
('Kaduwela', 'K001', 'kelaniya', 'KE1', '11', 'Ravidu', 'Pathum Himantha', '993213v', 'Kegalle', '0719158514', '', 'WhatsApp Image 2024-03-31 at 21.38.32_1696c65c.jpg', '15000', '12', '12%', 'K001/KE1/11/02', '2024-04-01', 'K001/KE1/11/02/20240401', '2024-06-24', '7500.00', '2024-04-01', '7500.00', '', '', '', ''),
('Kaduwela', 'K001', 'kelaniya', 'KE1', '11', 'Ravidu', 'Pathum Himantha', '545654', 'Kegalle', '0719158514', '', 'logo.png', '78000', '12', '12%', 'K001/KE1/11/03', '2024-04-02', 'K001/KE1/11/03/20240402', '2024-06-25', '0.00', '', '0.00', '', '', '', ''),
('Kaduwela', 'K001', 'kelaniya', 'KE1', '11', 'Ravidu', 'Pathum Himantha', '4', 'Kegalle', '0719158514', '', '', '25000', '13', '30%%', 'K001/KE1/11/04', '2024-04-03', 'K001/KE1/11/04/20240403', '2024-07-03', '0.00', '', '0.00', '32500', '2500', '250', '250'),
('Kaduwela', 'K001', 'kelaniya', 'KE1', '766', 'Ravidu', 'Pathum Himantha', '5', 'Kegalle', '0719158514', '', '', '27000', '13', '30%%', 'K001/KE1/766/01', '2024-04-03', 'K001/KE1/766/01/20240403', '2024-07-03', '18000.00', '2024-04-03', '9000.00', '35100', '2700', '270', '270'),
('Kaduwela', 'K001', 'Kothalawala', 'KO01', '01', 'Ravidu', 'Pathum Himantha', '99321309', 'Kegalle', '0719158514', '', 'Screenshot 2024-03-30 151233.png', '15000', '12', '12%', 'K001/KO01/01/03', '2024-04-01', 'K001/KO01/01/03/20240401', '2024-06-24', '0.00', '', '0.00', '', '', '', ''),
('Kaduwela', 'K001', 'Kothalawala', 'KO01', '01', 'Ravidu', 'Pathum Himantha', '6', 'Kegalle', '0719158514', '', '', '6000', '13', '30%%', 'K001/KO01/01/04', '2024-04-03', 'K001/KO01/01/04/20240403', '2024-07-03', '6000.00', '2024-04-03', '0.00', '7800', '600', '60', '60'),
('Kaduwela', 'K001', 'Kothalawala', 'KO01', '01', 'Ravidu', 'Pathum Himantha', '7', 'Kegalle', '0719158514', '', '', '30000', '13', '30%%', 'K001/KO01/01/05', '2024-04-03', 'K001/KO01/01/05/20240403', '2024-07-03', '28020.00', '2024-04-03', '1980.00', '39000', '3000', '300', '300'),
('Kaduwela', 'K001', 'Malabe', 'V02', '01', 'Ravidu', 'Pathum Himantha', '44', 'Kegalle', '0719158514', '', '', '123556', '12%', '12 Weeks', 'K001/V02/01/04', '2024-03-27', 'K001/V02/01/04/2024-03-27', '', '1000.00', '2024-03-30', '122556.00', '', '', '', ''),
('Kaduwela', 'K001', 'Malabe', 'V02', '02', 'Ravidu', 'Pathum Himantha', '412', 'Kegalle', '0719158514', '', '', '21222', '12%', '12 Weeks', 'K001/V02/02/01', '2024-03-27', 'K001/V02/02/01/20240327', '', '100.00', '2024-03-30', '21122.00', '', '', '', ''),
('Kaduwela', 'K001', 'Malabe', 'V02', '02', 'Ravidu', 'Pathum Himanthaasas', '87872', 'Kegalle', '0719158514', '', '', '222', '12', '12%', 'K001/V02/02/02', '2024-03-27', 'K001/V02/02/02/20240327', '2024-06-19', '0.00', '', '0.00', '', '', '', ''),
('Kaduwela', 'K001', 'Malabe', 'V02', '03', 'Ravidu', 'Pathum Himantha', '41223', 'Kegalle', '0719158514', '', '', '232', '12%', '12 Weeks', 'K001/V02/03/01', '2024-03-27', 'K001/V02/03/01/20240327', '', '200.00', '2024-03-30', '32.00', '', '', '', ''),
('Kaduwela', 'K001', 'Malabe', 'V02', '03', 'Ravidu', 'Pathum Himanthaasas', '8787', 'Kegalle', '0719158514', '', '', '67676', '12', '12%', 'K001/V02/03/02', '2024-03-27', 'K001/V02/03/02/20240327', '2024-03-27', '1000.00', '2024-03-30', '66676.00', '', '', '', ''),
('Kaduwela', 'K001', 'Malabe', 'V02', '03', 'Ravidu', 'Pathum Himantha', '41223', 'Kegalle', '0719158514', '', '', '1000', '12%', '12 Weeks', 'K001/V02/03/03', '2024-04-04', 'K001/V02/03/03/20240404', '2024-06-27', '0.00', '', '0.00', '1300', '100', '10', '10'),
('Kaduwela', 'K001', 'Malabe', 'V02', '03', 'Ravidu', 'Pathum Himantha', '41223', 'Kegalle', '0719158514', '', '', '1000', '12%', '12 Weeks', 'K001/V02/03/04', '2024-04-04', 'K001/V02/03/04/20240404', '2024-06-27', '0.00', '', '0.00', '1300', '100', '10', '10'),
('Kaduwela', 'K001', 'Malabe', 'V02', '03', 'Ravidu', 'Pathum Himantha', '41223', 'Kegalle', '0719158514', '', '', '350', '12%', '12 Weeks', 'K001/V02/03/05', '2024-04-04', 'K001/V02/03/05/20240404', '2024-06-27', '0.00', '', '0.00', '455', '35', '3.5', '3.5');

-- --------------------------------------------------------

--
-- Table structure for table `garantor`
--

CREATE TABLE `garantor` (
  `cNic` varchar(255) NOT NULL,
  `gname` varchar(255) NOT NULL,
  `gNic` varchar(255) NOT NULL,
  `gAddress` varchar(255) NOT NULL,
  `gPhone` varchar(255) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `garantor`
--

INSERT INTO `garantor` (`cNic`, `gname`, `gNic`, `gAddress`, `gPhone`, `id`) VALUES
('99', 'Pathum Himantha', '09', 'Kegalle', '0719158514', 1),
('99', 'Pathum Himantha', '09', 'Kegalle', '0719158514', 2),
('99', 'Pathum Himantha', '09', 'Kegalle', '0719158514', 3),
('99', 'Pathum Himantha', '09', 'Kegalle', '0719158514', 4),
('99', 'Pathum Himantha', '09', 'Kegalle', '0719158514', 5),
('11', 'Pathum Himantha', '092', 'Kegalle', '0719158514', 6),
('12', 'Pathum Himantha', '093', 'Kegalle', '0719158514', 7),
('12', 'Pathum Himantha', '093', 'Kegalle', '0719158514', 8),
('12', 'Pathum Himantha', '093', 'Kegalle', '0719158514', 9),
('12', 'Pathum Himantha', '093', 'Kegalle', '0719158514', 10),
('12', 'Pathum Himantha', '093', 'Kegalle', '0719158514', 11);

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE `image` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `image` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `image`
--

INSERT INTO `image` (`id`, `name`, `image`) VALUES
(1, 'logo', 'uploads/WhatsApp Image 2024-03-31 at 21.38.32_1696c65c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `paymentdates`
--

CREATE TABLE `paymentdates` (
  `id` int(11) NOT NULL,
  `loan_code` varchar(255) NOT NULL,
  `week_number` varchar(255) NOT NULL,
  `due_date_weekly` varchar(255) NOT NULL,
  `bname` varchar(255) NOT NULL,
  `center` varchar(255) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `nic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `paymentdates`
--

INSERT INTO `paymentdates` (`id`, `loan_code`, `week_number`, `due_date_weekly`, `bname`, `center`, `payment`, `nic`) VALUES
(116, 'K001/KE1/766/01/20240403', '12', '2024-06-26', 'Kaduwela', 'kelaniya', '', '5'),
(117, 'K001/KE1/766/01/20240403', '13', '2024-07-03', 'Kaduwela', 'kelaniya', '', '5'),
(118, 'K001/KO01/01/04/20240403', '1', '2024-04-10', 'Kaduwela', 'Kothalawala', '1000', '6'),
(119, 'K001/KO01/01/04/20240403', '2', '2024-04-17', 'Kaduwela', 'Kothalawala', '', '6'),
(120, 'K001/KO01/01/04/20240403', '3', '2024-04-24', 'Kaduwela', 'Kothalawala', '', '6'),
(121, 'K001/KO01/01/04/20240403', '4', '2024-05-01', 'Kaduwela', 'Kothalawala', '', '6'),
(122, 'K001/KO01/01/04/20240403', '5', '2024-05-08', 'Kaduwela', 'Kothalawala', '', '6'),
(123, 'K001/KO01/01/04/20240403', '6', '2024-05-15', 'Kaduwela', 'Kothalawala', '', '6'),
(124, 'K001/KO01/01/04/20240403', '7', '2024-05-22', 'Kaduwela', 'Kothalawala', '', '6'),
(125, 'K001/KO01/01/04/20240403', '8', '2024-05-29', 'Kaduwela', 'Kothalawala', '', '6'),
(126, 'K001/KO01/01/04/20240403', '9', '2024-06-05', 'Kaduwela', 'Kothalawala', '', '6'),
(127, 'K001/KO01/01/04/20240403', '10', '2024-06-12', 'Kaduwela', 'Kothalawala', '', '6'),
(128, 'K001/KO01/01/04/20240403', '11', '2024-06-19', 'Kaduwela', 'Kothalawala', '', '6'),
(129, 'K001/KO01/01/04/20240403', '12', '2024-06-26', 'Kaduwela', 'Kothalawala', '', '6'),
(130, 'K001/KO01/01/04/20240403', '13', '2024-07-03', 'Kaduwela', 'Kothalawala', '', '6'),
(131, 'K001/KO01/01/05/20240403', '1', '2024-04-10', 'Kaduwela', 'Kothalawala', '20', '7'),
(132, 'K001/KO01/01/05/20240403', '2', '2024-04-17', 'Kaduwela', 'Kothalawala', '', '7'),
(133, 'K001/KO01/01/05/20240403', '3', '2024-04-24', 'Kaduwela', 'Kothalawala', '1000', '7'),
(134, 'K001/KO01/01/05/20240403', '4', '2024-05-01', 'Kaduwela', 'Kothalawala', '', '7'),
(135, 'K001/KO01/01/05/20240403', '5', '2024-05-08', 'Kaduwela', 'Kothalawala', '', '7'),
(136, 'K001/KO01/01/05/20240403', '6', '2024-05-15', 'Kaduwela', 'Kothalawala', '', '7'),
(137, 'K001/KO01/01/05/20240403', '7', '2024-05-22', 'Kaduwela', 'Kothalawala', '', '7'),
(138, 'K001/KO01/01/05/20240403', '8', '2024-05-29', 'Kaduwela', 'Kothalawala', '', '7'),
(139, 'K001/KO01/01/05/20240403', '9', '2024-06-05', 'Kaduwela', 'Kothalawala', '', '7'),
(140, 'K001/KO01/01/05/20240403', '10', '2024-06-12', 'Kaduwela', 'Kothalawala', '', '7'),
(141, 'K001/KO01/01/05/20240403', '11', '2024-06-19', 'Kaduwela', 'Kothalawala', '', '7'),
(142, 'K001/KO01/01/05/20240403', '12', '2024-06-26', 'Kaduwela', 'Kothalawala', '', '7'),
(143, 'K001/KO01/01/05/20240403', '13', '2024-07-03', 'Kaduwela', 'Kothalawala', '', '7');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `loan_code` varchar(255) NOT NULL,
  `bname` varchar(255) NOT NULL,
  `center` varchar(255) NOT NULL,
  `payment_date` varchar(255) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `nic` varchar(255) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`loan_code`, `bname`, `center`, `payment_date`, `payment`, `nic`, `id`) VALUES
('K001/V02/15/01/20240327', '', '', '2024-03-30', '1000', '', 64),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '1000', '', 65),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '1000', '', 66),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '1000', '', 71),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '3000', '', 72),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '2500', '', 73),
('K001/V02/15/01/20240327', '', '', '2024-03-30', '1000', '', 74),
('K001/KO01/01/01/20240327', '', '', '2024-03-30', '1000', '11111', 75),
('K001/V02/15/01/20240327', '', '', '2024-03-30', '1000', '', 76),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '2500', '100', 77),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '6000', '100', 78),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '2000', '100', 79),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '1288', '100', 80),
('K001/V02/15/02/20240329', '', '', '2024-03-30', '4000', '100', 81),
('K001/V02/01/20240327', '', '', '2024-03-30', '8', '19999', 82),
('K001/V02/01/20240327', '', '', '2024-03-30', '10', '19999', 83),
('K001/V02/02/01/20240327', '', '', '2024-03-30', '100', '412', 84),
('K001/V02/03/01/20240327', '', '', '2024-03-30', '200', '41223', 85),
('K001/V02/01/04/2024-03-27', '', '', '2024-03-30', '1000', '44', 86),
('K001/V02/03/02/20240327', '', '', '2024-03-30', '1000', '8787', 87),
('K001/KE1/11/02/20240401', '', '', '2024-04-01', '1000', '993213v', 88),
('K001/KE1/11/02/20240401', '', '', '2024-04-01', '5000', '993213v', 89),
('K001/KE1/11/02/20240401', '', '', '2024-04-01', '1500', '993213v', 90),
('K001/V02/15/02/20240329', '', '', '2024', '2400', '100', 91),
('K001/V02/15/02/20240329', '', '', '2024', '2900', '100', 92),
('K001/V02/15/02/20240329', '', '', '2024-04-02', '5400', '100', 93),
('K001/V02/15/01/20240327', '', '', '2024', '600', '1111', 94),
('K001/V02/15/01/20240327', '', '', '2024', '1000', '1111', 95),
('K001/V02/15/02/20240329', '', '', '2024', '70', '100', 96),
('K001/V02/15/01/20240327', '', '', '2024-04-02', '250', '1111', 97),
('K001/V02/15/02/20240329', '', '', '2024-04-02', '90', '100', 98),
('K001/KE1/766/01/20240329', '', '', '2024-04-02', '50', '1122', 99),
('K001/KE1/01/03/20240327', '', '', '2024-04-02', '60', '1126', 100),
('K001/KE1/01/04/20240327', '', '', '2024-04-02', '70', '11266', 101),
('K001/V02/15/02/20240329', '', '', '2024-04-02', '7000', '100', 102),
('K001/V02/15/01/20240327', '', '', '2024-04-02', '560', '1111', 103),
('K001/V02/15/01/20240327', 'Kaduwela', 'Malabe', '2024', '890', '1111', 104),
('K001/V02/15/02/20240329', 'Kaduwela', 'Malabe', '2024-04-02', '980', '100', 105),
('100', 'K001/V02/15/02/20240329', '80', '2024-04-02', '0', 'Malabe', 106),
('100', 'K001/V02/15/02/20240329', '200', '2024-04-02', '0', 'Malabe', 107),
('K001/V02/15/02/20240329', 'Kaduwela', 'Malabe', '2024-04-02', '680', '100', 108),
('K001/KO01/01/01/20240327', 'Kaduwela', 'Kothalawala', '2024-04-02', '600', '11111', 109),
('K001/KO01/01/04/20240402', 'Kaduwela', 'Kothalawala', '2024-04-02', '2000', '21', 110),
('K001/KO01/01/04/20240402', 'Kaduwela', 'Kothalawala', '2024-04-02', '18000', '21', 111),
('K001/KE1/766/01/20240403', '', '', '2024-04-03', '1000', '5', 112),
('K001/KO01/01/04/20240403', '', '', '2024-04-03', '600', '6', 113);

-- --------------------------------------------------------

--
-- Table structure for table `usertable`
--

CREATE TABLE `usertable` (
  `bname` varchar(255) NOT NULL,
  `bcode` varchar(255) NOT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `code` mediumint(50) NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `usertable`
--

INSERT INTO `usertable` (`bname`, `bcode`, `id`, `name`, `email`, `password`, `code`, `status`) VALUES
('Kegalle', 'G01', 24, 'Himantha', 'pathumh074@gmail.com', '$2y$10$0qF6HvpZV1XTAdvEGxMHJOYEq13Hh7Q2k/1s6z.3y.w33vDimSQ4i', 0, ''),
('Kaduwela', 'K001', 30, 'Ravidu', 'pathumn071@gmail.com', '$2y$10$F6G5kqgAvBbZXmgS17YfDuSeJ6WKe0BZdtqMrZBalNO7s3VM/XbOW', 0, ''),
('', '', 31, 'Rashmi', 'rashmi.sewwandi1995@gmail.com', '$2y$10$EHFIZ8LhVBoct444Hbdo1eGVl42Bo/CusaYGsO.C7c4HOMVWpg/Z.', 0, 'verified');

-- --------------------------------------------------------

--
-- Table structure for table `weeks`
--

CREATE TABLE `weeks` (
  `id` int(11) NOT NULL,
  `nic` varchar(255) NOT NULL,
  `1st` date NOT NULL,
  `2nd` date NOT NULL,
  `3rd` date NOT NULL,
  `4th` date NOT NULL,
  `5th` date NOT NULL,
  `6th` date NOT NULL,
  `7th` date NOT NULL,
  `8th` date NOT NULL,
  `9th` date NOT NULL,
  `10th` date NOT NULL,
  `11th` date NOT NULL,
  `12th` date NOT NULL,
  `13th` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `weeks`
--

INSERT INTO `weeks` (`id`, `nic`, `1st`, `2nd`, `3rd`, `4th`, `5th`, `6th`, `7th`, `8th`, `9th`, `10th`, `11th`, `12th`, `13th`) VALUES
(1, '12345', '2024-04-03', '2024-04-10', '2024-04-17', '2024-04-24', '2024-05-01', '2024-05-08', '2024-05-15', '2024-05-22', '2024-05-29', '2024-06-05', '2024-06-12', '2024-06-19', '2024-06-26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`loan_code`);

--
-- Indexes for table `garantor`
--
ALTER TABLE `garantor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paymentdates`
--
ALTER TABLE `paymentdates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usertable`
--
ALTER TABLE `usertable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weeks`
--
ALTER TABLE `weeks`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `garantor`
--
ALTER TABLE `garantor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `paymentdates`
--
ALTER TABLE `paymentdates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `usertable`
--
ALTER TABLE `usertable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `weeks`
--
ALTER TABLE `weeks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
