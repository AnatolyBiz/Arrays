SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `tree`
--

CREATE TABLE `tree` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `title` char(30) NOT NULL,
  `description` char(60) DEFAULT NULL,
  `hint` char(30) DEFAULT NULL,
  `link` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tree`
--

INSERT INTO `tree` (`id`, `parent_id`, `title`, `description`, `hint`, `link`) VALUES
(1, 0, '1. item', 'is 1 node', '', '/#1'),
(2, 1, '1.1 item', 'is 1.1 node', '', '/#1.1'),
(3, 1, '1.2 item', 'is 1.2 node', '', '/#1.2'),
(4, 1, '1.3 item', 'is 1.3 node', '', '/#1.3'),
(5, 2, '1.1.1 item', 'is 1.1.1 node', '', '/#1.1.1'),
(6, 5, '1.1.1.1 item', 'is 1.1.1.1 node', '', '/#1.1.1.1'),
(7, 6, '1.1.1.1.1 item', 'is 1.1.1.1.1 node', '', '/#1.1.1.1.1'),
(8, 3, '1.2.1 item', 'is 1.2.1 node', '', '/#1.2.1'),
(9, 8, '1.2.1.1 item', 'is 1.2.1.1 node', '', '/#1.2.1.1'),
(10, 8, '1.2.1.2 item', 'is 1.2.1.2 node', '', '/#1.2.1.2'),
(11, 4, '1.3.1 item', 'is 1.3.1 node', '', '/#1.3.1'),
(12, 0, '2. item', 'is 2 node', '', '/#2'),
(13, 12, '2.1 item', 'is 2.1 node', '', '/#2.1'),
(14, 13, '2.1.1 item', 'is 2.1.1 node', '', '/#2.1.1'),
(15, 13, '2.1.2 item', 'is 2.1.2 node', '', '/#2.1.2');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
