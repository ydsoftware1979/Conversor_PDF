-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.1.30-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win32
-- HeidiSQL Versão:              9.2.0.4981
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Copiando estrutura do banco de dados para conversor
CREATE DATABASE IF NOT EXISTS `conversor` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `conversor`;


-- Copiando estrutura para tabela conversor.listaconversoes
CREATE TABLE IF NOT EXISTS `listaconversoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idRevenda` int(1) NOT NULL,
  `tipoConversao` int(1) NOT NULL,
  `nomeArquivo` varchar(255) NOT NULL,
  `nomeArquivoHASH` varchar(255) NOT NULL,
  `vlrFatura` float(11,5) NOT NULL,
  `vlrConvertido` float(11,5) NOT NULL,
  `nomeCliente` varchar(255) DEFAULT NULL,
  `numeroFatura` varchar(255) DEFAULT NULL,
  `dataVencimento` date DEFAULT NULL,
  `dataConversao` date DEFAULT NULL,
  `operadora` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela conversor.listaconversoes: ~45 rows (aproximadamente)
DELETE FROM `listaconversoes`;
/*!40000 ALTER TABLE `listaconversoes` DISABLE KEYS */;
INSERT INTO `listaconversoes` (`id`, `idRevenda`, `tipoConversao`, `nomeArquivo`, `nomeArquivoHASH`, `vlrFatura`, `vlrConvertido`, `nomeCliente`, `numeroFatura`, `dataVencimento`, `dataConversao`, `operadora`) VALUES
	(1, 1, 0, '899939081799.pdf', '46526bbeb9db8dc0b5abe5d37681a471', 106.83000, 106.83000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(2, 1, 0, '899939081803.pdf', '224bbe4b2e6a47e5d1e7ac65ac8553e3', 165.88000, 122.89000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(3, 1, 0, '899943021097.pdf', 'e337cd29629da064aab1587bfaa3fbed', 129.99001, 129.99001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(4, 1, 0, '899943050602.pdf', '234b214bb0f1c8e8c247597c93bd56ca', 129.99001, 129.99001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(5, 1, 0, '899943475706.pdf', '84cacb801f2d8319b71c2242250b236c', 124.37000, 136.61000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(6, 1, 0, '899944389307.pdf', 'b6c8e9952468612f29732af31f3911d0', 159.99001, 99.99000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(7, 1, 0, '899944744108.pdf', '6318b54329efb84a1fed58a9134402cd', 179.00999, 119.01000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(8, 1, 0, '899946203007.pdf', '3de8c4898f61f450e8c86f2a4fa2e6ae', 183.81000, 124.51000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(9, 1, 0, '899955469478.pdf', '760cea8bd110725d4ee7ce8c4bbc38c4', 200.09000, 60.09000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(10, 1, 0, '899958250170.pdf', '3b77d76cb8d3449e783862d9c157fedd', 159.99001, 159.99001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(11, 1, 0, '899959136051.pdf', '652e68107773b8ee03b8f0a04594e3c0', 159.89000, 159.89000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(12, 1, 0, '899960838531.pdf', 'b7adda35bee9eeba985d730ebdf1322d', 159.99001, 82.99000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(13, 1, 0, '899962550305.pdf', '2ab265649717404ed846787d38c2b651', 119.99000, 119.99000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(14, 1, 0, '899965150989.pdf', 'c082f9df21e8b63fea3f3061e01d5dbf', 184.32001, 184.32001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(15, 1, 0, '899965169113.pdf', 'a4d2a757f5eaede1b30643d387c38be9', 101.10000, 101.10000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(16, 1, 0, '899965184449.pdf', 'b43cd594774a8c49ca908df272b4f444', 102.85000, 102.85000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(17, 1, 0, '899968426821.pdf', '6df664be46eda879d45e126867b25a91', 152.17000, 155.14000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(18, 1, 0, '899971101450.pdf', 'b02f9e6d583a87341cc7c565d0f4b9c6', 177.58000, 93.38000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(19, 1, 0, '899971111229.pdf', '84e607d26aec97768a0039136f75e5e3', 51.59000, 25.25000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(20, 1, 0, '899971745154.pdf', '4a5eb3b863b16360b518588b42aff72e', 121.10000, 121.10000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(21, 1, 0, '899972014497.pdf', 'c1f8d7762d44c3b8be8cb5c6e21b9675', 214.82001, 160.85001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(22, 1, 0, '899972991492.pdf', 'aba6561e074fb5cd116cca002b326d08', 232.58000, 92.58000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(23, 1, 1, '899972994146.pdf', 'd40992dc630c9c05458e8fdc4f15781c', 0.00000, 0.00000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', NULL),
	(24, 1, 0, '899974065575.pdf', '8d0d59cd26d16f3ce29ab190c55ab5f2', 253.85001, 115.11000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(25, 1, 0, '899976956642.pdf', 'ea3c7b62e9c0c9b298eda716ed435be8', 288.16000, 283.56000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(26, 1, 0, '899976968690.pdf', '2d46cb51197d4f1005e787e38b695bf1', 306.01999, 115.50000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(27, 1, 0, '899976971767.pdf', '77a9b8897aa2906d0e9c39d048e58ac9', 163.50999, 94.07000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(28, 1, 0, '899977491672.pdf', '320e99e0483ae938fb8daddd7c221276', 228.28000, 89.54000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(29, 1, 0, '899978373638.pdf', '4bea325b1c4c9e2eb0bbb5b862d32367', 154.82001, 155.55000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(30, 1, 0, '899980943718.pdf', '4f9d8f553ce1dd1c769f9e73439ec8d1', 304.22000, 112.10000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(31, 1, 0, '899981618398.pdf', '90d312169832bb50b9d4125163bcab8c', 235.03999, 161.52000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(32, 1, 0, '899983270680.pdf', '47be9a692a1ac12efafbaa08fccb29fb', 287.45001, 287.45001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(33, 1, 0, '899984016565.pdf', '5b0792fc7f16b2c55e5d651c8c73227a', 99.99000, 99.99000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(34, 1, 0, '899984021059.pdf', '38a54a15273d3611b2e82ebe10e73c73', 214.82001, 142.28000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(35, 1, 0, '899984195403.pdf', 'db7751821662ab4343cf117887778ce1', 265.87000, 263.10999, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(36, 1, 0, '899984195479.pdf', 'c57fff9f5c900f66967bd29410c9dae6', 217.55000, 217.55000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(37, 1, 0, '899984200649.pdf', 'c860ebd7cdbef9f366c2b1d06a683e89', 274.76999, 274.76999, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(38, 1, 0, '899984201735.pdf', '53049237e96e33cdd098d63718e14046', 195.75000, 1537.60999, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(39, 1, 0, '899984202326.pdf', '66445f679cf4698d0494cfe7e6d22c19', 101.10000, 101.10000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(40, 1, 0, '899985165928.pdf', 'd2903ef1cc3396bf7df8dfe1a38e9063', 216.74001, 216.74001, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(41, 1, 0, '899985165967.pdf', 'c570ce4dd894c6f08d209f69657c12a9', 267.29999, 267.29999, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(42, 1, 0, '899985932945.pdf', '061f223932b5a8d1672b0318e499f687', 198.95000, 116.54000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(43, 1, 0, '899989953276.pdf', '4d82030d2f29554daa8083075451dfb9', 121.10000, 121.10000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(44, 1, 0, '899990231806.pdf', '6ab0af983e72e2f1a4c817f5bc13ec53', 109.08000, 109.08000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(45, 1, 0, '899990346915.pdf', 'c8eae7e087e1e5e611584f6f9c19dc80', 100.49000, 100.99000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO'),
	(46, 1, 0, '899990517931.pdf', 'df670163b8533ceb7d1a9698382c1994', 249.08000, 249.08000, 'SEM NOME', '0', '2019-01-01', '2019-11-18', 'VIVO');
/*!40000 ALTER TABLE `listaconversoes` ENABLE KEYS */;


-- Copiando estrutura para tabela conversor.revendas
CREATE TABLE IF NOT EXISTS `revendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomeRevenda` varchar(255) NOT NULL,
  `pastaRevenda` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Copiando dados para a tabela conversor.revendas: ~0 rows (aproximadamente)
DELETE FROM `revendas`;
/*!40000 ALTER TABLE `revendas` DISABLE KEYS */;
INSERT INTO `revendas` (`id`, `nomeRevenda`, `pastaRevenda`) VALUES
	(1, 'ATIKS TEM', 1);
/*!40000 ALTER TABLE `revendas` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
