-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-07-2025 a las 14:26:31
-- Versión del servidor: 10.4.22-MariaDB
-- Versión de PHP: 8.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_compras_zapatos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `proveedor_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `fecha`, `proveedor_id`, `usuario_id`, `total`) VALUES
(1, '2025-07-18 10:30:00', 1, 6, '0.00'),
(2, '2025-07-15 14:20:00', 2, 6, '550.00'),
(3, '2025-07-10 11:45:00', 3, 6, '999.99'),
(4, '2025-07-05 09:15:00', 1, 6, '0.00'),
(5, '2025-06-28 16:40:00', 2, 6, '660.00'),
(6, '2025-06-22 13:10:00', 3, 6, '325.00'),
(7, '2025-06-15 10:25:00', 1, 6, '999.99'),
(8, '2025-06-08 14:50:00', 2, 6, '0.00'),
(9, '2025-06-01 11:30:00', 3, 6, '715.00'),
(10, '2025-05-25 09:45:00', 1, 6, '390.00'),
(11, '2025-05-18 15:20:00', 2, 6, '0.00'),
(12, '2025-05-10 10:10:00', 3, 6, '525.00'),
(13, '2025-05-03 13:45:00', 1, 6, '630.00'),
(14, '2025-04-25 16:30:00', 2, 6, '640.00'),
(15, '2025-04-18 11:15:00', 3, 6, '595.00'),
(16, '2025-04-10 09:50:00', 1, 6, '390.00'),
(17, '2025-04-03 14:25:00', 2, 6, '0.00'),
(18, '2025-03-27 10:40:00', 3, 6, '0.00'),
(19, '2025-03-20 13:15:00', 1, 6, '999.99'),
(20, '2025-03-12 16:00:00', 2, 6, '0.00'),
(21, '2025-07-20 13:30:08', 1, 6, '360.00'),
(22, '2025-07-20 13:41:48', 1, 6, '170.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_compra`
--

CREATE TABLE `detalles_compra` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(3) NOT NULL,
  `precio_unitario` decimal(5,2) NOT NULL,
  `subtotal` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `detalles_compra`
--

INSERT INTO `detalles_compra` (`id`, `compra_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(27, 19, 13, 11, '85.00', '935.00'),
(28, 15, 18, 7, '85.00', '595.00'),
(29, 16, 21, 6, '65.00', '390.00'),
(30, 19, 12, 5, '65.00', '325.00'),
(31, 10, 39, 6, '65.00', '390.00'),
(32, 9, 26, 11, '65.00', '715.00'),
(33, 6, 31, 5, '65.00', '325.00'),
(34, 12, 8, 7, '75.00', '525.00'),
(35, 3, 15, 13, '80.00', '999.99'),
(36, 14, 37, 5, '80.00', '400.00'),
(37, 7, 7, 14, '70.00', '980.00'),
(38, 13, 12, 9, '70.00', '630.00'),
(39, 2, 6, 10, '55.00', '550.00'),
(40, 19, 6, 9, '55.00', '495.00'),
(41, 7, 12, 19, '55.00', '999.99'),
(42, 5, 26, 12, '55.00', '660.00'),
(43, 14, 31, 6, '40.00', '240.00'),
(44, 7, 11, 12, '35.00', '420.00'),
(58, 21, 42, 5, '55.00', '275.00'),
(59, 21, 1, 1, '85.00', '85.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_compras`
--

CREATE TABLE `facturas_compras` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `numero_factura` varchar(30) NOT NULL,
  `numero_control` varchar(30) NOT NULL,
  `fecha` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `facturas_compras`
--

INSERT INTO `facturas_compras` (`id`, `compra_id`, `numero_factura`, `numero_control`, `fecha`) VALUES
(1, 21, 'factura1', 'control1', '2025-07-20'),
(2, 1, 'factura2', 'control2', '2025-07-08'),
(3, 2, 'factura3', 'control3', '2025-07-15'),
(4, 22, 'factura 22', 'control22', '2025-07-20'),
(5, 3, 'FACT-20250720-0003', 'CTRL-20250720-0003', '2025-07-01'),
(6, 4, 'FACT-20250720-0004', 'CTRL-20250720-0004', '2025-07-02'),
(7, 5, 'FACT-20250720-0005', 'CTRL-20250720-0005', '2025-07-03'),
(8, 6, 'FACT-20250720-0006', 'CTRL-20250720-0006', '2025-07-04'),
(9, 7, 'FACT-20250720-0007', 'CTRL-20250720-0007', '2025-07-05'),
(10, 8, 'FACT-20250720-0008', 'CTRL-20250720-0008', '2025-07-06'),
(11, 9, 'FACT-20250720-0009', 'CTRL-20250720-0009', '2025-07-07'),
(12, 10, 'FACT-20250720-0010', 'CTRL-20250720-0010', '2025-07-08'),
(13, 11, 'FACT-20250720-0011', 'CTRL-20250720-0011', '2025-07-09'),
(14, 12, 'FACT-20250720-0012', 'CTRL-20250720-0012', '2025-07-10'),
(15, 13, 'FACT-20250720-0013', 'CTRL-20250720-0013', '2025-07-11'),
(16, 14, 'FACT-20250720-0014', 'CTRL-20250720-0014', '2025-07-12'),
(17, 15, 'FACT-20250720-0015', 'CTRL-20250720-0015', '2025-07-13'),
(18, 16, 'FACT-20250720-0016', 'CTRL-20250720-0016', '2025-07-14'),
(19, 17, 'FACT-20250720-0017', 'CTRL-20250720-0017', '2025-07-15'),
(20, 18, 'FACT-20250720-0018', 'CTRL-20250720-0018', '2025-07-16'),
(21, 19, 'FACT-20250720-0019', 'CTRL-20250720-0019', '2025-07-17'),
(22, 20, 'FACT-20250720-0020', 'CTRL-20250720-0020', '2025-07-18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_precios`
--

CREATE TABLE `historial_precios` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `precio_compra` decimal(5,2) NOT NULL,
  `precio_venta` decimal(5,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `historial_precios`
--

INSERT INTO `historial_precios` (`id`, `producto_id`, `precio_compra`, `precio_venta`, `fecha`) VALUES
(13, 1, '76.50', '126.00', '2025-04-06 12:45:49'),
(14, 2, '76.50', '126.00', '2025-05-02 12:45:49'),
(15, 3, '76.50', '126.00', '2025-05-01 12:45:49'),
(16, 4, '76.50', '126.00', '2025-02-06 12:45:49'),
(17, 5, '76.50', '126.00', '2025-06-10 12:45:49'),
(18, 6, '58.50', '99.00', '2025-05-11 12:45:49'),
(19, 7, '58.50', '99.00', '2025-05-31 12:45:49'),
(20, 8, '58.50', '99.00', '2025-06-10 12:45:49'),
(21, 9, '58.50', '99.00', '2025-05-30 12:45:49'),
(22, 10, '58.50', '99.00', '2025-03-06 12:45:49'),
(23, 11, '67.50', '117.00', '2025-02-03 12:45:49'),
(24, 12, '67.50', '117.00', '2025-05-14 12:45:49'),
(25, 13, '67.50', '117.00', '2025-07-04 12:45:49'),
(26, 14, '67.50', '117.00', '2025-05-24 12:45:49'),
(27, 15, '67.50', '117.00', '2025-05-20 12:45:49'),
(28, 16, '72.00', '135.00', '2025-03-11 12:45:49'),
(29, 17, '72.00', '135.00', '2025-03-27 12:45:49'),
(30, 18, '72.00', '135.00', '2025-07-20 12:45:49'),
(31, 19, '72.00', '135.00', '2025-07-01 12:45:49'),
(32, 20, '72.00', '135.00', '2025-04-18 12:45:49'),
(33, 21, '54.00', '90.00', '2025-06-02 12:45:49'),
(34, 22, '54.00', '90.00', '2025-03-02 12:45:49'),
(35, 23, '54.00', '90.00', '2025-07-01 12:45:49'),
(36, 24, '54.00', '90.00', '2025-06-16 12:45:49'),
(37, 25, '54.00', '90.00', '2025-03-27 12:45:49'),
(38, 26, '63.00', '108.00', '2025-03-30 12:45:49'),
(39, 27, '63.00', '108.00', '2025-06-12 12:45:49'),
(40, 28, '63.00', '108.00', '2025-06-18 12:45:49'),
(41, 29, '63.00', '108.00', '2025-06-04 12:45:49'),
(42, 30, '63.00', '108.00', '2025-03-06 12:45:49'),
(43, 31, '49.50', '85.50', '2025-07-18 12:45:49'),
(44, 32, '49.50', '85.50', '2025-03-01 12:45:49'),
(45, 33, '49.50', '85.50', '2025-02-09 12:45:49'),
(46, 34, '49.50', '85.50', '2025-06-27 12:45:49'),
(47, 35, '49.50', '85.50', '2025-01-31 12:45:49'),
(48, 36, '36.00', '63.00', '2025-05-18 12:45:49'),
(49, 37, '36.00', '63.00', '2025-02-05 12:45:49'),
(50, 38, '36.00', '63.00', '2025-04-16 12:45:49'),
(51, 39, '31.50', '58.50', '2025-02-09 12:45:49'),
(52, 40, '31.50', '58.50', '2025-02-08 12:45:49'),
(53, 41, '31.50', '58.50', '2025-02-21 12:45:49'),
(54, 1, '80.75', '133.00', '2025-06-11 12:45:49'),
(55, 2, '80.75', '133.00', '2025-05-21 12:45:49'),
(56, 3, '80.75', '133.00', '2025-07-13 12:45:49'),
(57, 4, '80.75', '133.00', '2025-06-15 12:45:49'),
(58, 5, '80.75', '133.00', '2025-05-17 12:45:49'),
(59, 6, '61.75', '104.50', '2025-06-14 12:45:49'),
(60, 7, '61.75', '104.50', '2025-05-03 12:45:49'),
(61, 8, '61.75', '104.50', '2025-07-06 12:45:49'),
(62, 9, '61.75', '104.50', '2025-07-04 12:45:49'),
(63, 10, '61.75', '104.50', '2025-06-11 12:45:49'),
(64, 11, '71.25', '123.50', '2025-05-24 12:45:49'),
(65, 12, '71.25', '123.50', '2025-05-01 12:45:49'),
(66, 13, '71.25', '123.50', '2025-05-31 12:45:49'),
(67, 14, '71.25', '123.50', '2025-07-12 12:45:49'),
(68, 15, '71.25', '123.50', '2025-05-10 12:45:49'),
(69, 16, '76.00', '142.50', '2025-05-19 12:45:49'),
(70, 17, '76.00', '142.50', '2025-07-12 12:45:49'),
(71, 18, '76.00', '142.50', '2025-06-18 12:45:49'),
(72, 19, '76.00', '142.50', '2025-06-02 12:45:49'),
(73, 20, '76.00', '142.50', '2025-05-26 12:45:49'),
(74, 21, '57.00', '95.00', '2025-06-09 12:45:49'),
(75, 22, '57.00', '95.00', '2025-06-09 12:45:49'),
(76, 23, '57.00', '95.00', '2025-04-28 12:45:49'),
(77, 24, '57.00', '95.00', '2025-06-27 12:45:49'),
(78, 25, '57.00', '95.00', '2025-06-05 12:45:49'),
(79, 26, '66.50', '114.00', '2025-05-15 12:45:49'),
(80, 27, '66.50', '114.00', '2025-07-03 12:45:49'),
(81, 28, '66.50', '114.00', '2025-05-15 12:45:49'),
(82, 29, '66.50', '114.00', '2025-07-10 12:45:49'),
(83, 30, '66.50', '114.00', '2025-06-18 12:45:49'),
(84, 31, '52.25', '90.25', '2025-06-11 12:45:49'),
(85, 32, '52.25', '90.25', '2025-07-08 12:45:49'),
(86, 33, '52.25', '90.25', '2025-06-16 12:45:49'),
(87, 34, '52.25', '90.25', '2025-06-07 12:45:49'),
(88, 35, '52.25', '90.25', '2025-06-26 12:45:49'),
(89, 36, '38.00', '66.50', '2025-04-29 12:45:49'),
(90, 37, '38.00', '66.50', '2025-05-11 12:45:49'),
(91, 38, '38.00', '66.50', '2025-07-07 12:45:49'),
(92, 39, '33.25', '61.75', '2025-06-15 12:45:49'),
(93, 40, '33.25', '61.75', '2025-06-05 12:45:49'),
(94, 41, '33.25', '61.75', '2025-06-18 12:45:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `genero` enum('Hombre','Mujer','Unisex','Niño','Niña') NOT NULL,
  `talla` decimal(2,1) NOT NULL,
  `color` varchar(20) DEFAULT NULL,
  `precio_compra` decimal(5,2) NOT NULL,
  `precio_venta` decimal(5,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `proveedor_id` int(11) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `genero`, `talla`, `color`, `precio_compra`, `precio_venta`, `stock`, `proveedor_id`, `stock_minimo`) VALUES
(1, 'Ultraboost 22', 'adidas', 'Hombre', '9.9', 'Negro/Rojo', '85.00', '140.00', 13, 1, 5),
(2, 'Ultraboost 22', 'adidas', 'Hombre', '9.9', 'Negro/Rojo', '85.00', '140.00', 15, 1, 5),
(3, 'Ultraboost 22', 'adidas', 'Hombre', '9.9', 'Negro/Rojo', '85.00', '140.00', 8, 1, 5),
(4, 'Ultraboost 22', 'adidas', 'Hombre', '9.9', 'Negro/Rojo', '85.00', '140.00', 10, 1, 5),
(5, 'Ultraboost 22', 'adidas', 'Hombre', '9.9', 'Negro/Rojo', '85.00', '140.00', 6, 1, 5),
(6, 'NMD_R1', 'adidas', 'Unisex', '9.9', 'Blanco/Negro', '65.00', '110.00', 10, 1, 4),
(7, 'NMD_R1', 'adidas', 'Unisex', '9.9', 'Blanco/Negro', '65.00', '110.00', 15, 1, 4),
(8, 'NMD_R1', 'adidas', 'Unisex', '9.9', 'Blanco/Negro', '65.00', '110.00', 20, 1, 4),
(9, 'NMD_R1', 'adidas', 'Unisex', '9.9', 'Blanco/Negro', '65.00', '110.00', 18, 1, 4),
(10, 'NMD_R1', 'adidas', 'Unisex', '9.9', 'Blanco/Negro', '65.00', '110.00', 12, 1, 4),
(11, 'Air Force 1', 'nike', 'Hombre', '9.9', 'Blanco', '75.00', '130.00', 18, 2, 6),
(12, 'Air Force 1', 'nike', 'Hombre', '9.9', 'Blanco', '75.00', '130.00', 22, 2, 6),
(13, 'Air Force 1', 'nike', 'Hombre', '9.9', 'Blanco', '75.00', '130.00', 15, 2, 6),
(14, 'Air Force 1', 'nike', 'Hombre', '9.9', 'Blanco', '75.00', '130.00', 10, 2, 6),
(15, 'Air Force 1', 'nike', 'Hombre', '9.9', 'Blanco', '75.00', '130.00', 8, 2, 6),
(16, 'Air Max 270', 'nike', 'Mujer', '9.9', 'Rosa', '80.00', '150.00', 5, 2, 3),
(17, 'Air Max 270', 'nike', 'Mujer', '9.9', 'Rosa', '80.00', '150.00', 8, 2, 3),
(18, 'Air Max 270', 'nike', 'Mujer', '9.9', 'Rosa', '80.00', '150.00', 12, 2, 3),
(19, 'Air Max 270', 'nike', 'Mujer', '9.9', 'Rosa', '80.00', '150.00', 15, 2, 3),
(20, 'Air Max 270', 'nike', 'Mujer', '9.9', 'Rosa', '80.00', '150.00', 10, 2, 3),
(21, 'Classic Leather', 'reebok', 'Hombre', '9.9', 'Blanco/Azul', '60.00', '100.00', 15, 3, 5),
(22, 'Classic Leather', 'reebok', 'Hombre', '9.9', 'Blanco/Azul', '60.00', '100.00', 20, 3, 5),
(23, 'Classic Leather', 'reebok', 'Hombre', '9.9', 'Blanco/Azul', '60.00', '100.00', 18, 3, 5),
(24, 'Classic Leather', 'reebok', 'Hombre', '9.9', 'Blanco/Azul', '60.00', '100.00', 12, 3, 5),
(25, 'Classic Leather', 'reebok', 'Hombre', '9.9', 'Blanco/Azul', '60.00', '100.00', 10, 3, 5),
(26, 'Nano X1', 'reebok', 'Unisex', '9.9', 'Negro/Rojo', '70.00', '120.00', 8, 3, 4),
(27, 'Nano X1', 'reebok', 'Unisex', '9.9', 'Negro/Rojo', '70.00', '120.00', 12, 3, 4),
(28, 'Nano X1', 'reebok', 'Unisex', '9.9', 'Negro/Rojo', '70.00', '120.00', 15, 3, 4),
(29, 'Nano X1', 'reebok', 'Unisex', '9.9', 'Negro/Rojo', '70.00', '120.00', 10, 3, 4),
(30, 'Nano X1', 'reebok', 'Unisex', '9.9', 'Negro/Rojo', '70.00', '120.00', 7, 3, 4),
(31, 'Cloudfoam Pure', 'adidas', 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 3, 1, 2),
(32, 'Cloudfoam Pure', 'adidas', 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 5, 1, 2),
(33, 'Cloudfoam Pure', 'adidas', 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 8, 1, 2),
(34, 'Cloudfoam Pure', 'adidas', 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 12, 1, 2),
(35, 'Cloudfoam Pure', 'adidas', 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 10, 1, 2),
(36, 'Revolution 6', 'nike', 'Niño', '9.9', 'Azul/Negro', '40.00', '70.00', 8, 2, 4),
(37, 'Revolution 6', 'nike', 'Niño', '9.9', 'Azul/Negro', '40.00', '70.00', 12, 2, 4),
(38, 'Revolution 6', 'nike', 'Niño', '9.9', 'Azul/Negro', '40.00', '70.00', 15, 2, 4),
(39, 'Princess', 'reebok', 'Niña', '9.9', 'Rosa/Dorado', '35.00', '65.00', 5, 3, 3),
(40, 'Princess', 'reebok', 'Niña', '9.9', 'Rosa/Dorado', '35.00', '65.00', 10, 3, 3),
(41, 'Princess', 'reebok', 'Niña', '9.9', 'Rosa/Dorado', '35.00', '65.00', 1, 3, 3),
(42, 'Cloudfoam Pure', NULL, 'Mujer', '9.9', 'Blanco', '55.00', '95.00', 5, 1, 2);

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `after_precio_update` AFTER UPDATE ON `productos` FOR EACH ROW BEGIN
    IF OLD.precio_compra <> NEW.precio_compra OR OLD.precio_venta <> NEW.precio_venta THEN
        INSERT INTO historial_precios (producto_id, precio_compra, precio_venta)
        VALUES (NEW.id, NEW.precio_compra, NEW.precio_venta);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `productos_stock_bajo`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `productos_stock_bajo` (
`id` int(11)
,`nombre` varchar(50)
,`stock` int(11)
,`stock_minimo` int(11)
,`genero` enum('Hombre','Mujer','Unisex','Niño','Niña')
,`proveedor_id` int(11)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `contacto` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `contacto`, `telefono`, `email`, `direccion`) VALUES
(1, 'adidas panama 2, c.a.', 'Carlos Rodríguez', '+507 6254-7890', 'ventas@adidaspma.com', 'Calle 50, Plaza Nueva, Panamá'),
(2, 'nike panama, c.a.', 'María Fernández', '+507 6985-1234', 'info@nikepanama.com', 'Vía España, Edificio Torre Global'),
(3, 'reebok usa, c.a.', 'James Wilson', '+1 800-555-1234', 'supply@reebokusa.com', '123 Sport Ave, Boston, MA'),
(4, 'JUMP USA II, C.A.', 'Laura Chen', '+1 213-555-6789', 'orders@jumpfootwear.com', '456 Fashion St, Los Angeles, CA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sincronizaciones`
--

CREATE TABLE `sincronizaciones` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `productos_actualizados` int(11) DEFAULT NULL,
  `estado` enum('exito','error','pendiente') DEFAULT NULL,
  `detalles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `sincronizaciones`
--

INSERT INTO `sincronizaciones` (`id`, `proveedor_id`, `fecha`, `productos_actualizados`, `estado`, `detalles`) VALUES
(5, 1, '2025-07-18 10:00:00', 25, 'exito', 'Sincronización completa - 25 productos actualizados'),
(6, 2, '2025-07-17 14:30:00', 18, 'exito', 'Actualización de precios y stock'),
(7, 3, '2025-07-16 11:20:00', 15, 'exito', 'Sincronización parcial - 15 productos actualizados'),
(8, 1, '2025-07-10 09:15:00', 8, 'error', 'Error de conexión con API - Reintentar'),
(9, 2, '2025-07-09 16:45:00', 22, 'exito', 'Sincronización completa - nuevos modelos'),
(10, 3, '2025-07-08 11:10:00', 12, 'pendiente', 'Sincronización en proceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tallas`
--

CREATE TABLE `tallas` (
  `id` int(11) NOT NULL,
  `valor` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tallas`
--

INSERT INTO `tallas` (`id`, `valor`) VALUES
(1, '36'),
(2, '37'),
(3, '38'),
(4, '39'),
(5, '40'),
(6, '41'),
(7, '42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('admin','comprador') DEFAULT 'comprador'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre`, `rol`) VALUES
(6, 'admin', '$2y$10$atDIOJWUJ0rO7WUOcW7OheWyTVCiseZVGZEGdHeeFT6e9xLRoZM8C', 'Administrador Principal ', 'admin');

-- --------------------------------------------------------

--
-- Estructura para la vista `productos_stock_bajo`
--
DROP TABLE IF EXISTS `productos_stock_bajo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `productos_stock_bajo`  AS SELECT `productos`.`id` AS `id`, `productos`.`nombre` AS `nombre`, `productos`.`stock` AS `stock`, `productos`.`stock_minimo` AS `stock_minimo`, `productos`.`genero` AS `genero`, `productos`.`proveedor_id` AS `proveedor_id` FROM `productos` WHERE `productos`.`stock` < `productos`.`stock_minimo` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`);

--
-- Indices de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sincronizaciones`
--
ALTER TABLE `sincronizaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `tallas`
--
ALTER TABLE `tallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `valor` (`valor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `sincronizaciones`
--
ALTER TABLE `sincronizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tallas`
--
ALTER TABLE `tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  ADD CONSTRAINT `detalles_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `detalles_compra_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  ADD CONSTRAINT `facturas_compras_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD CONSTRAINT `historial_precios_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `sincronizaciones`
--
ALTER TABLE `sincronizaciones`
  ADD CONSTRAINT `sincronizaciones_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
