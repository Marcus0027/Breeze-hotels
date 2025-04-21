-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21/04/2025 às 22:03
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `breeze`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(255) NOT NULL,
  `q_idquarto` int(11) NOT NULL,
  `qh_idhotel` int(11) NOT NULL,
  `qtq_idtipo` int(11) NOT NULL,
  `u_idusuarios` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`idcliente`, `cpf`, `telefone`, `q_idquarto`, `qh_idhotel`, `qtq_idtipo`, `u_idusuarios`) VALUES
(1, '353.835.932-83', '(85) 97279-1345', 1, 7, 9, 2),
(3, '923.212.532-53', '(85) 95742-8812', 7, 2, 3, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `hoteis`
--

CREATE TABLE `hoteis` (
  `idhotel` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `regiao` varchar(255) NOT NULL,
  `estado` varchar(255) NOT NULL,
  `cidade` varchar(255) NOT NULL,
  `endereço` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `hoteis`
--

INSERT INTO `hoteis` (`idhotel`, `nome`, `regiao`, `estado`, `cidade`, `endereço`) VALUES
(1, 'Casa das Brumas', 'Sudeste', 'RJ', 'Rio de Janeiro', 'Av. Niemeyer, 210'),
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
(20, 'Casa Índigo Rosa', 'Sudeste', 'SP', 'Campinas', 'R. Ferreira Penteado, 711');

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagens`
--

CREATE TABLE `imagens` (
  `idimagens` int(11) NOT NULL,
  `diretorio` varchar(255) NOT NULL,
  `q_idquarto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocupacao`
--

CREATE TABLE `ocupacao` (
  `idocupacao` int(11) NOT NULL,
  `ocupacao` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ocupacao`
--

INSERT INTO `ocupacao` (`idocupacao`, `ocupacao`) VALUES
(1, 'Individual'),
(2, 'Casal'),
(3, 'Twin'),
(4, 'Triplo'),
(5, 'Quádruplo'),
(6, 'Familiar');

-- --------------------------------------------------------

--
-- Estrutura para tabela `quartos`
--

CREATE TABLE `quartos` (
  `idquarto` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `valor` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `disponibilidade` tinyint(4) NOT NULL,
  `h_idhotel` int(11) NOT NULL,
  `tq_idtipo` int(11) NOT NULL,
  `o_idocupacao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `quartos`
--

INSERT INTO `quartos` (`idquarto`, `numero`, `valor`, `descricao`, `disponibilidade`, `h_idhotel`, `tq_idtipo`, `o_idocupacao`) VALUES
(1, 202, 500, 'Otimo quarto, vista para a praia', 1, 7, 9, 6),
(2, 777, 121, 'Quarto standard equipado com cama confortável, Wi-Fi, banheiro privativo e mesa de trabalho. Ideal para ocupação quádruplo.', 1, 1, 1, 5),
(3, 490, 606, 'Cabana rústica com varanda, rede e clima acolhedor, ideal para casais. Ideal para ocupação casal.', 1, 1, 10, 2),
(4, 688, 176, 'Quarto econômico com estrutura simples, ideal para estadias curtas ou viajantes econômicos. Ideal para ocupação individual.', 1, 1, 2, 1),
(5, 345, 348, 'Quarto superior com vista parcial, ar-condicionado, frigobar e decoração moderna. Ideal para ocupação triplo.', 1, 1, 3, 4),
(6, 349, 825, 'Cabana rústica com varanda, rede e clima acolhedor, ideal para casais. Ideal para ocupação twin.', 1, 1, 10, 3),
(7, 107, 311, 'Quarto superior com vista parcial, ar-condicionado, frigobar e decoração moderna. Ideal para ocupação individual.', 1, 2, 3, 1),
(8, 233, 1432, 'Luxuosa suíte presidencial com 3 ambientes, jacuzzi, serviço exclusivo e decoração refinada. Ideal para ocupação casal.', 1, 2, 8, 2),
(9, 795, 139, 'Quarto econômico com estrutura simples, ideal para estadias curtas ou viajantes econômicos. Ideal para ocupação casal.', 1, 2, 2, 2),
(10, 711, 392, 'Acomodação espaçosa com vista, cama queen, amenities premium e TV de tela plana. Ideal para ocupação triplo.', 1, 2, 4, 4),
(11, 385, 634, 'Chalé de madeira com lareira, vista para a natureza e total privacidade. Ideal para ocupação twin.', 1, 2, 9, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `reserva`
--

CREATE TABLE `reserva` (
  `idreserva` int(11) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `preco` int(11) NOT NULL,
  `c_idcliente` int(11) NOT NULL,
  `cq_idquarto` int(11) NOT NULL,
  `cqh_idhotel` int(11) NOT NULL,
  `cqtq_idtipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `reserva`
--

INSERT INTO `reserva` (`idreserva`, `checkin`, `checkout`, `preco`, `c_idcliente`, `cq_idquarto`, `cqh_idhotel`, `cqtq_idtipo`) VALUES
(1, '2025-04-01', '2025-04-09', 500, 1, 1, 7, 9),
(2, '2025-04-25', '2025-04-30', 800, 3, 7, 2, 3);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo_quarto`
--

CREATE TABLE `tipo_quarto` (
  `idtipo_quarto` int(11) NOT NULL,
  `tipoQuarto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipo_quarto`
--

INSERT INTO `tipo_quarto` (`idtipo_quarto`, `tipoQuarto`) VALUES
(1, 'Standard'),
(2, 'Econômico'),
(3, 'Superior'),
(4, 'Deluxe'),
(5, 'Executivo'),
(6, 'Suíte '),
(7, 'Suíte Master'),
(8, 'Suíte Presidencial'),
(9, 'Chalé'),
(10, 'Cabana'),
(11, 'Bangalô');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuarios` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`idusuarios`, `nome`, `email`, `senha`) VALUES
(2, 'Samuel', 'samuel@gmail.com', '$2y$10$RutKmw5hXXaaI0EWOS95GOlnsdy7QMxN/0Kf32huCnI91LKgKMTEe'),
(3, 'Teste', 'teste@gmail.com', '$2y$10$5fL//GbSUYacb6jt5GvHVuKigUhqXdcJZssP6ZDGj6jw4iXiJchrC');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`,`q_idquarto`,`qh_idhotel`,`qtq_idtipo`,`u_idusuarios`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `fk_cliente_quartos1_idx` (`q_idquarto`,`qh_idhotel`,`qtq_idtipo`),
  ADD KEY `fk_cliente_usuarios1_idx` (`u_idusuarios`);

--
-- Índices de tabela `hoteis`
--
ALTER TABLE `hoteis`
  ADD PRIMARY KEY (`idhotel`);

--
-- Índices de tabela `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`idimagens`,`q_idquarto`),
  ADD KEY `fk_imagens_quartos_idx` (`q_idquarto`);

--
-- Índices de tabela `ocupacao`
--
ALTER TABLE `ocupacao`
  ADD PRIMARY KEY (`idocupacao`);

--
-- Índices de tabela `quartos`
--
ALTER TABLE `quartos`
  ADD PRIMARY KEY (`idquarto`,`h_idhotel`,`tq_idtipo`,`o_idocupacao`),
  ADD KEY `fk_quartos_hoteis1_idx` (`h_idhotel`),
  ADD KEY `fk_quartos_tipo_quarto1_idx` (`tq_idtipo`),
  ADD KEY `fk_quartos_ocupacao1_idx` (`o_idocupacao`);

--
-- Índices de tabela `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`idreserva`,`c_idcliente`,`cq_idquarto`,`cqh_idhotel`,`cqtq_idtipo`),
  ADD KEY `fk_reserva_cliente1_idx` (`c_idcliente`,`cq_idquarto`,`cqh_idhotel`,`cqtq_idtipo`);

--
-- Índices de tabela `tipo_quarto`
--
ALTER TABLE `tipo_quarto`
  ADD PRIMARY KEY (`idtipo_quarto`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuarios`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `hoteis`
--
ALTER TABLE `hoteis`
  MODIFY `idhotel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `imagens`
--
ALTER TABLE `imagens`
  MODIFY `idimagens` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ocupacao`
--
ALTER TABLE `ocupacao`
  MODIFY `idocupacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `quartos`
--
ALTER TABLE `quartos`
  MODIFY `idquarto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `reserva`
--
ALTER TABLE `reserva`
  MODIFY `idreserva` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tipo_quarto`
--
ALTER TABLE `tipo_quarto`
  MODIFY `idtipo_quarto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_quartos1` FOREIGN KEY (`q_idquarto`,`qh_idhotel`,`qtq_idtipo`) REFERENCES `quartos` (`idquarto`, `h_idhotel`, `tq_idtipo`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cliente_usuarios1` FOREIGN KEY (`u_idusuarios`) REFERENCES `usuarios` (`idusuarios`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `imagens`
--
ALTER TABLE `imagens`
  ADD CONSTRAINT `fk_imagens_quartos` FOREIGN KEY (`q_idquarto`) REFERENCES `quartos` (`idquarto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `quartos`
--
ALTER TABLE `quartos`
  ADD CONSTRAINT `fk_quartos_hoteis1` FOREIGN KEY (`h_idhotel`) REFERENCES `hoteis` (`idhotel`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_quartos_ocupacao1` FOREIGN KEY (`o_idocupacao`) REFERENCES `ocupacao` (`idocupacao`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_quartos_tipo_quarto1` FOREIGN KEY (`tq_idtipo`) REFERENCES `tipo_quarto` (`idtipo_quarto`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `fk_reserva_cliente1` FOREIGN KEY (`c_idcliente`,`cq_idquarto`,`cqh_idhotel`,`cqtq_idtipo`) REFERENCES `cliente` (`idcliente`, `q_idquarto`, `qh_idhotel`, `qtq_idtipo`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
