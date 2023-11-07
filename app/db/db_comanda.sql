-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-11-2023 a las 22:25:51
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `db_comanda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `idPuesto` int(11) NOT NULL,
  `baja` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `nombre`, `apellido`, `dni`, `idPuesto`, `baja`) VALUES
(1, 'Juan', 'Doe', '35111222', 1, b'0'),
(2, 'Juan2', 'Esteban', '283338881', 5, b'0'),
(3, 'Pablo', 'Esteban', '285556668', 2, b'0'),
(4, 'Juan', 'Cocinero', '2211114442', 4, b'0'),
(5, 'eliminar', 'Esteban', '1111111', 2, b'1'),
(6, 'Juan', 'cervecero', '223335552', 3, b'0'),
(7, 'Juan', 'Socio', '251115489', 1, b'0'),
(8, 'Juan', 'Bartender', '220000558', 2, b'0'),
(9, 'Juan', 'Mozo', '357772564', 5, b'0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadosmesa`
--

CREATE TABLE `estadosmesa` (
  `id` int(11) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadosmesa`
--

INSERT INTO `estadosmesa` (`id`, `estado`) VALUES
(1, 'con cliente esperando pedido'),
(2, 'con cliente comiendo'),
(3, 'con cliente pagando'),
(4, 'cerrada'),
(5, 'libre');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadospedido`
--

CREATE TABLE `estadospedido` (
  `id` int(11) NOT NULL,
  `estado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estadospedido`
--

INSERT INTO `estadospedido` (`id`, `estado`) VALUES
(1, 'pendiente'),
(2, 'en preparación'),
(3, 'listo para servir');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `itemspedido`
--

CREATE TABLE `itemspedido` (
  `id` int(11) NOT NULL,
  `idPedido` int(11) NOT NULL,
  `idProducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precioUnitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `idEstado` int(11) NOT NULL,
  `baja` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `codigo`, `idEstado`, `baja`) VALUES
(1, '9N3R4', 5, b'0'),
(2, '221N8', 1, b'0'),
(3, 'C9ASY', 5, b'1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `nombreCliente` varchar(255) NOT NULL,
  `codigoMesa` varchar(6) NOT NULL,
  `idEstado` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `tiempoEstimado` datetime DEFAULT NULL,
  `cancelado` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `idTipoProducto` int(11) NOT NULL,
  `baja` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `stock`, `idTipoProducto`, `baja`) VALUES
(1, 'Pizza', 2000.00, 100, 2, b'0'),
(2, 'Coca Cola', 1000.00, 50, 1, b'0'),
(3, 'eliminar', 1000.00, 50, 2, b'1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

CREATE TABLE `puestos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `permisos` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`id`, `nombre`, `permisos`) VALUES
(1, 'socio', b'1'),
(2, 'bartender', b'0'),
(3, 'cervecero', b'0'),
(4, 'cocinero', b'0'),
(5, 'mozo', b'0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposdeproducto`
--

CREATE TABLE `tiposdeproducto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiposdeproducto`
--

INSERT INTO `tiposdeproducto` (`id`, `nombre`) VALUES
(1, 'bebida'),
(2, 'comida');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `idPuesto` (`idPuesto`);

--
-- Indices de la tabla `estadosmesa`
--
ALTER TABLE `estadosmesa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estadospedido`
--
ALTER TABLE `estadospedido`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `itemspedido`
--
ALTER TABLE `itemspedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idPedido` (`idPedido`),
  ADD KEY `idProducto` (`idProducto`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idEstado` (`idEstado`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idEstado` (`idEstado`),
  ADD KEY `codigoMesa` (`codigoMesa`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idTipoProducto` (`idTipoProducto`);

--
-- Indices de la tabla `puestos`
--
ALTER TABLE `puestos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tiposdeproducto`
--
ALTER TABLE `tiposdeproducto`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `estadosmesa`
--
ALTER TABLE `estadosmesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estadospedido`
--
ALTER TABLE `estadospedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `itemspedido`
--
ALTER TABLE `itemspedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `puestos`
--
ALTER TABLE `puestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tiposdeproducto`
--
ALTER TABLE `tiposdeproducto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`idPuesto`) REFERENCES `puestos` (`id`);

--
-- Filtros para la tabla `itemspedido`
--
ALTER TABLE `itemspedido`
  ADD CONSTRAINT `itemspedido_ibfk_1` FOREIGN KEY (`idPedido`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `itemspedido_ibfk_2` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_1` FOREIGN KEY (`idEstado`) REFERENCES `estadosmesa` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`idEstado`) REFERENCES `estadospedido` (`id`),
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`codigoMesa`) REFERENCES `mesas` (`codigo`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`idTipoProducto`) REFERENCES `tiposdeproducto` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
