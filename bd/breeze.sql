-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 04:20 AM
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
-- Database: `breeze`
--

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(15) NOT NULL,
  `u_idusuarios` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`idcliente`, `cpf`, `telefone`, `u_idusuarios`) VALUES
(1, '353.835.932-83', '(85) 97279-1348', 2),
(3, '923.212.532-53', '(85) 95742-8812', 3);

-- --------------------------------------------------------

--
-- Table structure for table `hoteis`
--

CREATE TABLE `hoteis` (
  `idhotel` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `regiao` varchar(255) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `endereço` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hoteis`
--

INSERT INTO `hoteis` (`idhotel`, `nome`, `regiao`, `estado`, `cidade`, `endereço`) VALUES
(1, 'Casa das Brumas', 'Sudeste', 'ES', 'Vila Velha', 'R. Niemeyer, 230'),
(2, 'Vale da Aurora', 'Sul', 'RS', 'Canela', 'R. João Pessoa, 112'),
(3, 'Refúgio Nômade', 'Centro-Oeste', 'MT', 'Chapada dos Guimarães', 'R. das Mangabeiras, 19'),
(4, 'Cores de Trancoso', 'Nordeste', 'BA', 'Trancoso', 'R. do Bosque, 302'),
(5, 'Jardim do Éden', 'Sudeste', 'SP', 'Campos do Jordão', 'Av. das Hortênsias, 370'),
(6, 'Brisa do Oceano', 'Nordeste', 'RN', 'Natal', 'Av. Praia dos Artistas, 905'),
(7, 'Maré Serena', 'Sul', 'SC', 'Florianópolis', 'R. da Harmonia, 44'),
(8, 'Bosque do Arvo', 'Norte', 'PA', 'Alter do Chão', 'R. Bela Vista, 12'),
(9, 'Vila Vidrá', 'Sudeste', 'MG', 'Tiradentes', 'R. São José, 60'),
(10, 'Estúdio da Sombra', 'Sudeste', 'SP', 'São Paulo', 'R. Frei Caneca, 420'),
(11, 'Alma Essenza', 'Sul', 'PR', 'Curitiba', 'Av. Sete de Setembro, 3333'),
(12, 'Ventos da Orla', 'Nordeste', 'CE', 'Fortaleza', 'Av. Beira-Mar, 2900'),
(13, 'Casa da Alma', 'Sudeste', 'RJ', 'Petrópolis', 'R. Dom Pedro, 49'),
(14, 'Dunas do Norte', 'Nordeste', 'MA', 'Barreirinhas', 'R. Principal, 108'),
(15, 'Cantinho Serina', 'Centro-Oeste', 'GO', 'Pirenópolis', 'R. Aurora, 81'),
(16, 'Luz do Itaguá', 'Sudeste', 'SP', 'Ubatuba', 'Av. Leovigildo Dias Vieira, 501'),
(17, 'Sopro do Vento', 'Nordeste', 'PB', 'João Pessoa', 'Av. Cabo Branco, 1982'),
(18, 'Jardim Zéfiro', 'Norte', 'AM', 'Manaus', 'Av. Constantino Nery, 1092'),
(19, 'Villa Branca Flor', 'Sul', 'RS', 'Gramado', 'Av. Central, 330'),
(20, 'Casa Índigo Rosa', 'Sudeste', 'SP', 'Campinas', 'R. Ferreira Penteado, 711'),
(21, 'Praia Dourada', 'Nordeste', 'BA', 'Salvador', 'Blv. Oceânica, 1234'),
(22, 'Recanto Rural', 'Centro-Oeste', 'GO', 'Caldas Novas', 'R. das Termas, 789'),
(23, 'Suíço Brasileiro', 'Sul', 'RS', 'Gramado', 'Rod. RS-115, Km 35'),
(24, 'Palácio das Águas', 'Centro-Oeste', 'GO', 'Caldas Novas', 'Av. Fonte Termal, 1500');

-- --------------------------------------------------------

--
-- Table structure for table `imagens`
--

CREATE TABLE `imagens` (
  `idimagens` int(11) NOT NULL,
  `diretorio` varchar(255) NOT NULL,
  `q_idquarto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `imagens`
--

INSERT INTO `imagens` (`idimagens`, `diretorio`, `q_idquarto`) VALUES
(14, 'img_6842ed932db40.jpg', 7),
(15, 'img_6842f0e5966e0.jpg', 7),
(16, 'img_6842f86d23248.jpg', 7);

-- --------------------------------------------------------

--
-- Table structure for table `ocupacao`
--

CREATE TABLE `ocupacao` (
  `idocupacao` int(11) NOT NULL,
  `ocupacao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ocupacao`
--

INSERT INTO `ocupacao` (`idocupacao`, `ocupacao`) VALUES
(1, 'Individual'),
(2, 'Casal'),
(3, 'Twin'),
(4, 'Triplo'),
(5, 'Quádruplo'),
(6, 'Familiar'),
(10, 'Triple Twin');

-- --------------------------------------------------------

--
-- Table structure for table `quartos`
--

CREATE TABLE `quartos` (
  `idquarto` int(11) NOT NULL,
  `numero` int(3) NOT NULL,
  `valor` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `disponibilidade` tinyint(4) NOT NULL,
  `h_idhotel` int(11) NOT NULL,
  `tq_idtipo` int(11) NOT NULL,
  `o_idocupacao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quartos`
--

INSERT INTO `quartos` (`idquarto`, `numero`, `valor`, `descricao`, `disponibilidade`, `h_idhotel`, `tq_idtipo`, `o_idocupacao`) VALUES
(1, 202, 500, 'Otimo quarto, vista para a praia', 0, 7, 9, 6),
(2, 777, 121, 'Quarto standard equipado com cama confortável, Wi-Fi, banheiro privativo e mesa de trabalho. Ideal para ocupação quádruplo.', 1, 1, 1, 5),
(3, 490, 606, 'Cabana rústica com varanda, rede e clima acolhedor, ideal para casais. Ideal para ocupação casal.', 0, 1, 10, 2),
(5, 345, 348, 'Quarto superior com vista parcial, ar-condicionado, frigobar e decoração moderna. Ideal para ocupação triplo.', 0, 1, 3, 4),
(6, 349, 825, 'Cabana rústica com varanda, rede e clima acolhedor, ideal para casais. Ideal para ocupação twin.', 1, 1, 10, 3),
(7, 107, 320, 'Quarto superior com vista parcial, ar-condicionado, frigobar e decoração moderna. Ideal para ocupação individual.', 0, 2, 3, 1),
(8, 233, 1432, 'Luxuosa suíte presidencial com 3 ambientes, jacuzzi, serviço exclusivo e decoração refinada. Ideal para ocupação casal.', 1, 2, 8, 2),
(9, 795, 139, 'Quarto econômico com estrutura simples, ideal para estadias curtas ou viajantes econômicos. Ideal para ocupação casal.', 1, 2, 2, 2),
(10, 711, 392, 'Acomodação espaçosa com vista, cama queen, amenities premium e TV de tela plana. Ideal para ocupação triplo.', 1, 2, 4, 4),
(11, 385, 634, 'Chalé de madeira com lareira, vista para a natureza e total privacidade. Ideal para ocupação twin.', 1, 2, 9, 3),
(12, 501, 500, 'Suíte Presidencial com varanda privativa, piso aquecido, sistema de som integrado, cortinas blackout automatizadas e amenities de luxo da marca Bulgari. Experiência cinco estrelas!', 0, 5, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `reserva`
--

CREATE TABLE `reserva` (
  `idreserva` int(11) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `preco` int(11) NOT NULL,
  `c_idcliente` int(11) NOT NULL,
  `q_idquarto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reserva`
--

INSERT INTO `reserva` (`idreserva`, `checkin`, `checkout`, `preco`, `c_idcliente`, `q_idquarto`) VALUES
(1, '2025-04-11', '2025-04-04', 500, 1, 1),
(2, '2025-04-25', '2025-04-30', 800, 3, 7),
(3, '2025-06-25', '2025-06-27', 1000, 1, 12),
(5, '2025-06-12', '2025-06-12', 348, 1, 5),
(6, '2025-06-12', '2025-06-12', 348, 1, 5),
(11, '2025-06-30', '2025-07-04', 2424, 3, 3),
(12, '2025-06-30', '2025-07-04', 2424, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_quarto`
--

CREATE TABLE `tipo_quarto` (
  `idtipo_quarto` int(11) NOT NULL,
  `tipoQuarto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tipo_quarto`
--

INSERT INTO `tipo_quarto` (`idtipo_quarto`, `tipoQuarto`) VALUES
(1, 'Standart'),
(2, 'Econômico'),
(3, 'Superior'),
(4, 'Deluxe'),
(5, 'Executivo'),
(6, 'Suíte '),
(7, 'Suíte Master'),
(8, 'Suíte Presidencial'),
(9, 'Chalé'),
(10, 'Cabana'),
(11, 'Bangalô'),
(12, 'Loft'),
(13, 'teste');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuarios` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`idusuarios`, `nome`, `email`, `senha`) VALUES
(2, 'Samuel', 'samuel@gmail.com', '$2y$10$RutKmw5hXXaaI0EWOS95GOlnsdy7QMxN/0Kf32huCnI91LKgKMTEe'),
(3, 'Teste', 'teste@gmail.com', '$2y$10$5fL//GbSUYacb6jt5GvHVuKigUhqXdcJZssP6ZDGj6jw4iXiJchrC'),
(4, 'Marcus', 'marcus@gmail.com', '$2y$10$cYfyS0yCegtoGk7ZBWNBR.YJVUcyDUOt6Ev1eUhR39OtsUGR5taRS'),
(5, 'adm', 'adm@adm.com', '$2y$10$v0X0fLvdBVyZyOo1zvrUCOzH3bvJoZBTJTx9ekFwar1CsqoDN6XHq'),
(6, 'expedito', 'expedito@gmail.com', '$2y$10$bLlGfgveKNQZx99F8uVg/ucovayo9fMesDelHJXdY42e3doAGoH1S'),
(7, 'sam', 'sam@gmail.com', '$2y$10$EEuczbnbY6ko8VxwcPTVueCjRQVDMaZdqUtUtfOygCNC/DF0fCDY6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`,`u_idusuarios`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `fk_cliente_usuarios1_idx` (`u_idusuarios`);

--
-- Indexes for table `hoteis`
--
ALTER TABLE `hoteis`
  ADD PRIMARY KEY (`idhotel`);

--
-- Indexes for table `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`idimagens`,`q_idquarto`),
  ADD KEY `fk_imagens_quartos_idx` (`q_idquarto`);

--
-- Indexes for table `ocupacao`
--
ALTER TABLE `ocupacao`
  ADD PRIMARY KEY (`idocupacao`);

--
-- Indexes for table `quartos`
--
ALTER TABLE `quartos`
  ADD PRIMARY KEY (`idquarto`,`h_idhotel`,`tq_idtipo`,`o_idocupacao`),
  ADD KEY `fk_quartos_hoteis1_idx` (`h_idhotel`),
  ADD KEY `fk_quartos_tipo_quarto1_idx` (`tq_idtipo`),
  ADD KEY `fk_quartos_ocupacao1_idx` (`o_idocupacao`);

--
-- Indexes for table `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`idreserva`,`c_idcliente`,`q_idquarto`),
  ADD KEY `fk_reserva_cliente1_idx` (`c_idcliente`),
  ADD KEY `fk_reserva_quartos1_idx` (`q_idquarto`);

--
-- Indexes for table `tipo_quarto`
--
ALTER TABLE `tipo_quarto`
  ADD PRIMARY KEY (`idtipo_quarto`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuarios`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hoteis`
--
ALTER TABLE `hoteis`
  MODIFY `idhotel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `imagens`
--
ALTER TABLE `imagens`
  MODIFY `idimagens` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `ocupacao`
--
ALTER TABLE `ocupacao`
  MODIFY `idocupacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `quartos`
--
ALTER TABLE `quartos`
  MODIFY `idquarto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `reserva`
--
ALTER TABLE `reserva`
  MODIFY `idreserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tipo_quarto`
--
ALTER TABLE `tipo_quarto`
  MODIFY `idtipo_quarto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_usuarios1` FOREIGN KEY (`u_idusuarios`) REFERENCES `usuarios` (`idusuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `imagens`
--
ALTER TABLE `imagens`
  ADD CONSTRAINT `fk_imagens_quartos` FOREIGN KEY (`q_idquarto`) REFERENCES `quartos` (`idquarto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `quartos`
--
ALTER TABLE `quartos`
  ADD CONSTRAINT `fk_quartos_hoteis1` FOREIGN KEY (`h_idhotel`) REFERENCES `hoteis` (`idhotel`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_quartos_ocupacao1` FOREIGN KEY (`o_idocupacao`) REFERENCES `ocupacao` (`idocupacao`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_quartos_tipo_quarto1` FOREIGN KEY (`tq_idtipo`) REFERENCES `tipo_quarto` (`idtipo_quarto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `fk_reserva_cliente1` FOREIGN KEY (`c_idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_reserva_quartos1` FOREIGN KEY (`q_idquarto`) REFERENCES `quartos` (`idquarto`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
