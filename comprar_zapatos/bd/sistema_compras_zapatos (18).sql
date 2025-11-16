-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-11-2025 a las 01:24:19
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `identificacion` varchar(20) NOT NULL,
  `direccion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `identificacion`, `direccion`, `creado_en`) VALUES
(1, 'prueba 1', '54564654', 'chacao', '2025-08-02 16:34:44'),
(2, 'prueba 2', '4564654', '', '2025-08-03 05:00:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_emails`
--

CREATE TABLE `clientes_emails` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes_emails`
--

INSERT INTO `clientes_emails` (`id`, `cliente_id`, `email`) VALUES
(3, 1, 'dapacheco@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_telefonos`
--

CREATE TABLE `clientes_telefonos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes_telefonos`
--

INSERT INTO `clientes_telefonos` (`id`, `cliente_id`, `telefono`) VALUES
(5, 1, '6454564654'),
(6, 1, '1112312');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `fecha`, `cliente_id`, `usuario_id`, `total`) VALUES
(10, '2025-11-09 11:03:24', NULL, 1, 40.00),
(11, '2025-11-09 11:17:06', NULL, 1, 50.00),
(12, '2025-11-09 11:21:24', NULL, 1, 60.00),
(13, '2025-11-09 11:21:50', NULL, 1, 60.00),
(14, '2025-11-09 11:24:56', NULL, 1, 40.00),
(15, '2025-11-09 11:27:34', NULL, 1, 220.00),
(16, '2025-11-09 11:29:50', NULL, 1, 60.00),
(17, '2025-11-09 11:37:43', NULL, 1, 40.00),
(18, '2025-11-09 11:38:28', NULL, 1, 60.00),
(19, '2025-11-09 12:01:33', NULL, 1, 300.00),
(20, '2025-11-09 12:11:14', NULL, 1, 200.00),
(21, '2025-11-09 12:49:40', 2, 1, 80.00),
(25, '2025-11-09 18:45:15', 2, 1, 91.00),
(26, '2025-11-09 18:46:16', 2, 1, 96.00),
(27, '2025-11-09 18:56:20', 2, 1, 150.00),
(28, '2025-11-09 19:53:39', 1, 1, 60.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_compra`
--

CREATE TABLE `detalles_compra` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `marca_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(3) NOT NULL,
  `precio_unitario` decimal(5,2) NOT NULL,
  `subtotal` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalles_compra`
--

INSERT INTO `detalles_compra` (`id`, `compra_id`, `marca_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 10, 0, 17, 1, 40.00, 40.00),
(2, 11, 0, 9, 1, 50.00, 50.00),
(3, 12, 0, 18, 1, 60.00, 60.00),
(4, 13, 0, 18, 1, 60.00, 60.00),
(5, 14, 0, 19, 1, 40.00, 40.00),
(6, 15, 0, 20, 4, 55.00, 220.00),
(7, 16, 0, 18, 1, 60.00, 60.00),
(8, 17, 0, 21, 1, 40.00, 40.00),
(9, 18, 0, 18, 1, 60.00, 60.00),
(10, 19, 0, 22, 4, 75.00, 300.00),
(11, 20, 0, 8, 4, 50.00, 200.00),
(12, 21, 0, 21, 1, 40.00, 40.00),
(13, 21, 0, 19, 1, 40.00, 40.00),
(14, 25, 3, 14, 1, 40.00, 36.00),
(15, 25, 4, 10, 1, 55.00, 55.00),
(16, 26, 2, 18, 1, 60.00, 60.00),
(17, 26, 3, 17, 1, 40.00, 36.00),
(18, 27, 2, 18, 1, 60.00, 60.00),
(19, 27, 1, 1, 1, 90.00, 90.00),
(20, 28, 2, 7, 1, 60.00, 60.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
  `oportunidad_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `oportunidad_id`, `cliente_id`, `usuario_id`, `fecha`, `total`) VALUES
(1, 1, 1, 6, '2025-08-02 18:50:35', 265.00),
(2, 6, 1, 5, '2025-08-03 09:50:09', 460.00),
(3, 7, 2, 6, '2025-08-03 09:52:08', 595.00),
(4, 5, 1, 5, '2025-08-03 09:53:57', 265.00),
(5, 4, 1, 5, '2025-08-03 12:58:04', 110.00),
(6, 3, 1, 6, '2025-08-03 12:58:16', 75.00),
(7, 2, 1, 5, '2025-08-03 12:58:20', 115.00),
(8, 8, 2, 6, '2025-08-03 13:32:41', 265.00),
(9, 2, 1, 5, '2025-08-03 13:33:17', 115.00),
(10, 1, 1, 6, '2025-08-03 13:33:21', 265.00),
(11, 1, 1, 6, '2025-08-03 13:33:22', 265.00),
(12, 4, 1, 5, '2025-08-03 13:49:35', 110.00),
(13, 5, 1, 5, '2025-08-03 13:49:38', 265.00),
(14, 4, 1, 5, '2025-08-03 15:00:57', 110.00),
(15, 4, 1, 5, '2025-08-03 15:01:02', 110.00),
(16, 4, 1, 5, '2025-08-03 15:01:08', 110.00),
(17, 4, 1, 5, '2025-08-10 15:23:34', 110.00),
(18, 9, 2, 5, '2025-08-10 20:32:05', 115.00),
(19, 10, 2, 7, '2025-08-10 20:33:19', 115.00),
(20, 12, 1, 6, '2025-08-11 14:06:28', 425.00),
(21, 7, 2, 6, '2025-08-11 19:37:41', 595.00),
(22, 11, 1, 6, '2025-08-28 17:50:54', 130.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `facturas_compras`
--

INSERT INTO `facturas_compras` (`id`, `compra_id`, `numero_factura`, `numero_control`, `fecha`) VALUES
(9, 10, '0000001', '00-0000001', '2025-11-09'),
(10, 11, '0000002', '00-0000002', '2025-11-09'),
(11, 12, '0000003', '00-0000003', '2025-11-09'),
(12, 13, '0000004', '00-0000004', '2025-11-09'),
(13, 14, '0000005', '00-0000005', '2025-11-09'),
(14, 15, '0000006', '00-0000006', '2025-11-09'),
(15, 16, '0000007', '00-0000007', '2025-11-09'),
(16, 17, '0000008', '00-0000008', '2025-11-09'),
(17, 18, '0000009', '00-0000009', '2025-11-09'),
(18, 19, '0000010', '00-0000010', '2025-11-09'),
(19, 20, '0000011', '00-0000011', '2025-11-09'),
(20, 21, '0000012', '00-0000012', '2025-11-09'),
(21, 25, '0000013', '00-0000013', '2025-11-09'),
(22, 26, '0000014', '00-0000014', '2025-11-09'),
(23, 27, '0000015', '00-0000015', '2025-11-09'),
(24, 28, '0000016', '00-0000016', '2025-11-10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_productos`
--

CREATE TABLE `fotos_productos` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fotos_productos`
--

INSERT INTO `fotos_productos` (`id`, `producto_id`, `url`) VALUES
(1, 4, 'Nike Air Max 901.png'),
(3, 12, 'Adidas Stan Smith.png'),
(5, 2, 'Adidas Ultraboost 232.png'),
(7, 6, 'nike air force 1 blanco.png'),
(9, 13, 'Nike Cortez blanco rojo.png'),
(11, 14, 'Puma Future Rider amarillo.png'),
(13, 8, 'Puma Suede Classic rojo.png'),
(15, 15, 'Reebok Classic Nylon.png'),
(17, 10, 'Reebok Club C 85 verde.png');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_precios`
--

INSERT INTO `historial_precios` (`id`, `producto_id`, `precio_compra`, `precio_venta`, `fecha`) VALUES
(1, 1, 85.00, 140.00, '2025-06-01 10:00:00'),
(2, 1, 90.00, 150.00, '2025-07-01 10:00:00'),
(3, 4, 70.00, 120.00, '2025-05-15 11:30:00'),
(4, 4, 75.00, 130.00, '2025-06-15 11:30:00'),
(5, 6, 55.00, 100.00, '2025-06-10 09:00:00'),
(6, 6, 60.00, 110.00, '2025-07-01 09:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id`, `nombre`, `foto`) VALUES
(1, 'Adidas Originals', 'assets/img/marcas/adidas originals.png'),
(2, 'Nike', 'assets/img/marcas/nike.png'),
(3, 'Puma', 'assets/img/marcas/puma.png'),
(4, 'Reebok', 'assets/img/marcas/reebok.jpg'),
(5, 'Adidas Performance', 'assets/img/marcas/adperfor.png'),
(9, 'New Balance', 'assets/img/marcas/New Balance.png'),
(10, 'Vans', 'assets/img/marcas/vans.jpg'),
(11, 'Converse', 'assets/img/marcas/Converse.jpg'),
(12, 'Skechers', 'assets/img/marcas/Skechers.png'),
(13, 'Lacoste', 'assets/img/marcas/lacoste-logo-png_seeklogo-81686.png'),
(34, 'pull&bear', 'assets/img/marcas/pullybear12.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `metodo` varchar(50) NOT NULL,
  `numero_referencia` varchar(4) NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id`, `compra_id`, `metodo`, `numero_referencia`, `monto`) VALUES
(1, 12, 'efectivo', 'efec', 0.00),
(2, 13, 'pago_movil', 'asda', 0.00),
(3, 14, 'pago_movil', 'adsa', 0.00),
(4, 18, 'Transferencia', 'sdas', 0.00),
(5, 19, 'Transferencia', '1231', 64500.00),
(6, 20, 'Cheque', 'dsad', 4200000.00),
(7, 21, 'Transferencia', 'asda', 172000.00),
(8, 25, 'Pago Móvil', '1234', 19566.00),
(9, 26, 'Punto de Venta', '1211', 20640.00),
(10, 27, 'Efectivo', 'efec', 150.00),
(11, 28, 'Punto de Venta', '1231', 12900.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oportunidades`
--

CREATE TABLE `oportunidades` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `estado` enum('pendiente','exitosa','pospuesta','cancelada','concretada') DEFAULT 'pendiente',
  `comentario` text DEFAULT NULL,
  `creado_en` datetime DEFAULT current_timestamp(),
  `actualizado_en` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revisada_por_gerente` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oportunidades`
--

INSERT INTO `oportunidades` (`id`, `cliente_id`, `usuario_id`, `estado`, `comentario`, `creado_en`, `actualizado_en`, `revisada_por_gerente`) VALUES
(1, 1, 6, 'cancelada', 'veta', '2025-08-02 18:16:02', '2025-08-03 14:59:06', 0),
(2, 1, 5, 'pendiente', 'venta nueva', '2025-08-02 18:17:03', '2025-10-11 16:17:07', 0),
(3, 1, 6, 'cancelada', 'veasasd', '2025-08-02 18:18:50', '2025-08-03 15:00:06', 0),
(4, 1, 5, 'concretada', '', '2025-08-10 18:54:44', '2025-08-10 15:36:41', 0),
(5, 1, 5, 'cancelada', 'venta', '2025-08-03 01:12:36', '2025-08-28 17:50:44', 0),
(6, 1, 5, 'concretada', 'prueba nueva a ver', '2025-08-03 01:14:12', '2025-08-03 14:11:28', 0),
(7, 2, 6, 'concretada', 'venta nuevo sistema', '2025-08-03 09:51:52', '2025-08-11 19:37:41', 0),
(8, 2, 6, 'cancelada', 'venta 03-08 13:22pm', '2025-08-03 13:22:58', '2025-08-03 14:24:07', 0),
(9, 2, 5, 'concretada', '', '2025-08-08 18:56:24', '2025-08-10 20:32:06', 0),
(10, 2, 7, 'concretada', 'MUESTRA', '2025-08-10 20:32:58', '2025-08-10 20:33:19', 0),
(11, 1, 6, 'concretada', '', '2025-08-10 22:20:25', '2025-08-28 17:50:55', 0),
(12, 1, 6, 'concretada', '', '2025-08-11 01:36:52', '2025-08-11 14:06:29', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `oportunidades_productos`
--

CREATE TABLE `oportunidades_productos` (
  `id` int(11) NOT NULL,
  `oportunidad_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `oportunidades_productos`
--

INSERT INTO `oportunidades_productos` (`id`, `oportunidad_id`, `producto_id`, `cantidad`) VALUES
(1, 1, 12, 1),
(2, 1, 3, 1),
(3, 2, 12, 1),
(4, 3, 14, 1),
(5, 4, 7, 1),
(6, 5, 12, 1),
(7, 5, 3, 1),
(8, 6, 12, 4),
(9, 7, 12, 1),
(10, 7, 3, 1),
(11, 7, 7, 3),
(12, 8, 12, 1),
(13, 8, 3, 1),
(14, 9, 12, 1),
(15, 10, 12, 1),
(16, 11, 4, 1),
(17, 12, 14, 1),
(18, 12, 9, 1),
(19, 12, 8, 1),
(20, 12, 10, 1),
(21, 12, 15, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `precio_compra` decimal(5,2) NOT NULL,
  `precio_venta` decimal(5,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `proveedor_id` int(11) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 2,
  `imagen_path` varchar(255) DEFAULT 'default_shoe.jpg',
  `marca_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `color`, `precio_compra`, `precio_venta`, `stock`, `proveedor_id`, `stock_minimo`, `imagen_path`, `marca_id`) VALUES
(1, 'Adidas Ultraboost 23', 'Zapatillas de running de alto rendimiento', 'Negro', 90.00, 150.00, 1, 1, 5, 'Adidas Ultraboost 232.png', 1),
(2, 'Adidas Ultraboost 23', 'Zapatillas de running de alto rendimiento', 'Negro', 90.00, 150.00, 14, 1, 5, 'Adidas Ultraboost 232.png', 1),
(3, 'Adidas Ultraboost 23', 'Zapatillas de running de alto rendimiento', 'Blanco', 90.00, 150.00, -1, 1, 5, 'Adidas Ultraboost 23 blanco.png', 1),
(4, 'Nike Air Max 90', 'Icono del streetwear', 'Gris', 75.00, 130.00, 24, 2, 8, 'Nike Air Max 901.png', 2),
(5, 'Nike Air Max 90', 'Icono del streetwear', 'Gris', 75.00, 130.00, 22, 2, 8, 'Nike Air Max 901.png', 2),
(6, 'Nike Air Force 1', 'Clásico atemporal', 'Blanco', 60.00, 110.00, 24, 2, 10, 'nike air force 1 blanco.png', 2),
(7, 'Nike Air Force 1', 'Clásico atemporal', 'Blanco', 60.00, 110.00, 17, 2, 10, 'nike air force 1 blanco.png', 2),
(8, 'Puma Suede Classic', 'Estilo retro y comodidad', 'Rojo', 50.00, 90.00, 15, 3, 4, 'Puma Suede Classic rojo.png', 3),
(9, 'Puma Suede Classic', 'Estilo retro y comodidad', 'Negro', 50.00, 90.00, 10, 3, 4, 'Puma Suede Classic negro.png', 3),
(10, 'Reebok Club C 85', 'Estilo tenis vintage', 'Verde', 55.00, 100.00, 18, 4, 6, 'Reebok Club C 85 verde.png', 4),
(11, 'Reebok Club C 85', 'Estilo tenis vintage', 'Verde', 55.00, 100.00, 14, 4, 6, 'Reebok Club C 85 verde.png', 4),
(12, 'Adidas Stan Smith', 'Clásico de la cancha', 'Blanco/Verde', 65.00, 115.00, -1, 1, 7, 'Adidas Stan Smith.png', 1),
(13, 'Nike Cortez', 'Primeras zapatillas de running de Nike', 'Blanco/Rojo', 58.00, 105.00, 15, 2, 5, 'Nike Cortez blanco rojo.png', 2),
(14, 'Puma Future Rider', 'Inspiración retro-futurista', 'Amarillo', 40.00, 75.00, 7, 3, 3, 'Puma Future Rider amarillo.png', 3),
(15, 'Reebok Classic Nylon', 'Comodidad y durabilidad', 'Rosa', 38.00, 70.00, 9, 4, 3, 'Reebok Classic Nylon.png', 4),
(17, 'Puma Future Rider', NULL, 'Amarillo', 40.00, 75.00, 2, 3, 3, 'default_shoe.jpg', 3),
(18, 'Nike Air Force 1', NULL, 'Blanco', 60.00, 110.00, 6, 2, 10, 'default_shoe.jpg', 2),
(19, 'Puma Future Rider', NULL, 'Amarillo', 40.00, 75.00, 2, 3, 3, 'default_shoe.jpg', 3),
(20, 'Reebok Club C 85', NULL, 'Verde', 55.00, 100.00, 4, 4, 6, 'default_shoe.jpg', 4),
(21, 'Puma Future Rider', NULL, 'Amarillo', 40.00, 75.00, 2, 3, 3, 'default_shoe.jpg', 3),
(22, 'Nike Air Max 90', NULL, 'Gris', 75.00, 130.00, 4, 2, 8, 'default_shoe.jpg', 2);

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
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`) VALUES
(1, 'Adidas'),
(2, 'Nike '),
(3, 'Puma '),
(4, 'Reebok ');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sincronizaciones`
--

INSERT INTO `sincronizaciones` (`id`, `proveedor_id`, `fecha`, `productos_actualizados`, `estado`, `detalles`) VALUES
(1, 1, '2025-07-24 08:00:00', 30, 'exito', 'Sincronización diaria completa con Adidas'),
(2, 2, '2025-07-23 15:00:00', 25, 'exito', 'Actualización de stock y precios de Nike'),
(3, 3, '2025-07-22 10:30:00', 10, 'error', 'Fallo de conexión con API de Puma'),
(4, 4, '2025-07-21 09:45:00', 18, 'exito', 'Sincronización con Reebok sin incidencias');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasa_diaria`
--

CREATE TABLE `tasa_diaria` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tasa` decimal(10,2) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tasa_diaria`
--

INSERT INTO `tasa_diaria` (`id`, `fecha`, `tasa`, `descripcion`) VALUES
(1, '2025-11-07', 212.00, 'tasa hoy'),
(7, '2025-11-08', 215.00, 'prueba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('gerente','admin','vendedor') DEFAULT 'vendedor',
  `email` varchar(255) DEFAULT NULL CHECK (`email` like '%_@_%._%'),
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre`, `rol`, `email`, `direccion`, `telefono`, `foto`) VALUES
(1, 'admin', '$2y$10$atDIOJWUJ0rO7WUOcW7OheWyTVCiseZVGZEGdHeeFT6e9xLRoZM8C', 'Luis Perez', 'admin', 'prueba@gmail.com', 'iutv', '056465465', '/shoe_shop/assets/usuarios/6891271d392dc_depositphotos_62525161-stock-illustration-footwear-print-in-a-pool.jpg'),
(5, 'vendedor1', '$2y$10$igxdmn9re44UJqEsejpOD.099rMTVson3QxuKZpXTDhQhvCvStUEK', 'vendedor prueba', 'vendedor', 'dapacheco1993@gmail.com', 'chacao', '0440404404', '/shoe_shop/assets/usuarios/688f771e6b1e5_688f74d1da1cc_prueba1.jpg'),
(6, 'gerente1', '$2y$10$i5UU50z4q3ZBGwokwhXn/e1bGLe9OSwIFf0v0R5vxGFOBO7VWy.cS', 'gerente prueba', 'gerente', 'admin3@empresa.com', 'direccion prueba', '+44540250364', '/shoe_shop/assets/usuarios/688f76ffe7ba5_images (1).jfif'),
(7, 'vendedor2', '$2y$10$OoGfNXuf2gooWbBHTQMMrOIF9jClzmBl9Ew5r.zxqSiEvyv23SYzi', 'prueba vendedor 2', 'vendedor', 'tomhil@empresa.com', 'CANDELARIA', '+0185258041', '/shoe_shop/assets/usuarios/688f8adaf118c_464928.webp'),
(8, 'gerente2', '$2y$10$gR0MNjUqz5tl0YW0IxiKxuGkbqpvwry/j9esA6dbJt3Ghthw2/w/O', 'prueba gerente 2', 'gerente', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `factura_id` int(11) DEFAULT NULL,
  `producto_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `talla` varchar(10) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `factura_id`, `producto_id`, `cliente_id`, `talla`, `cantidad`, `precio_unitario`, `usuario_id`, `fecha`) VALUES
(1, NULL, 6, NULL, '44', 2, 120.00, 5, '2025-08-02 01:34:02'),
(2, NULL, 12, NULL, '41', 1, 12.00, 5, '2025-08-02 12:08:07'),
(3, NULL, 3, NULL, '38.0', 2, 150.00, 5, '2025-08-02 12:12:29'),
(11, NULL, 3, NULL, '38.0', 2, 150.00, 5, '2025-08-02 12:45:14'),
(12, NULL, 3, NULL, '38.0', 1, 150.00, 7, '2025-08-02 18:42:51'),
(13, NULL, 12, NULL, '41.0', 2, 115.00, 7, '2025-08-02 18:43:04'),
(14, NULL, 2, NULL, '43.0', 1, 150.00, 5, '2025-08-02 18:43:39'),
(15, NULL, 12, NULL, '41.0', 1, 115.00, 5, '2025-08-02 18:43:39'),
(16, NULL, 12, NULL, '41.0', 1, 115.00, 6, '2025-08-02 18:50:35'),
(17, NULL, 3, NULL, '38.0', 1, 150.00, 6, '2025-08-02 18:50:35'),
(18, 2, 12, 1, '41.0', 4, 115.00, 5, '2025-08-03 09:50:09'),
(19, 3, 12, 2, '41.0', 1, 115.00, 6, '2025-08-03 09:52:08'),
(20, 3, 3, 2, '38.0', 1, 150.00, 6, '2025-08-03 09:52:08'),
(21, 3, 7, 2, '37.0', 3, 110.00, 6, '2025-08-03 09:52:09'),
(22, 4, 12, 1, '41.0', 1, 115.00, 5, '2025-08-03 09:53:57'),
(23, 4, 3, 1, '38.0', 1, 150.00, 5, '2025-08-03 09:53:57'),
(24, 5, 7, 1, '37.0', 1, 110.00, 5, '2025-08-03 12:58:04'),
(25, 6, 14, 1, '35.0', 1, 75.00, 6, '2025-08-03 12:58:16'),
(26, 7, 12, 1, '41.0', 1, 115.00, 5, '2025-08-03 12:58:20'),
(27, 8, 12, 2, '41.0', 1, 115.00, 6, '2025-08-03 13:32:41'),
(28, 8, 3, 2, '38.0', 1, 150.00, 6, '2025-08-03 13:32:41'),
(29, 9, 12, 1, '41.0', 1, 115.00, 5, '2025-08-03 13:33:17'),
(30, 10, 12, 1, '41.0', 1, 115.00, 6, '2025-08-03 13:33:22'),
(31, 10, 3, 1, '38.0', 1, 150.00, 6, '2025-08-03 13:33:22'),
(32, 11, 12, 1, '41.0', 1, 115.00, 6, '2025-08-03 13:33:22'),
(33, 11, 3, 1, '38.0', 1, 150.00, 6, '2025-08-03 13:33:22'),
(34, 12, 7, 1, '37.0', 1, 110.00, 5, '2025-08-03 13:49:35'),
(35, 13, 12, 1, '41.0', 1, 115.00, 5, '2025-08-03 13:49:38'),
(36, 13, 3, 1, '38.0', 1, 150.00, 5, '2025-08-03 13:49:38'),
(37, 14, 7, 1, '37.0', 1, 110.00, 5, '2025-08-03 15:00:57'),
(38, 15, 7, 1, '37.0', 1, 110.00, 5, '2025-08-03 15:01:02'),
(39, 16, 7, 1, '37.0', 1, 110.00, 5, '2025-08-03 15:01:08'),
(40, NULL, 12, NULL, '41.0', 1, 115.00, 5, '2025-08-03 17:06:42'),
(41, 17, 7, 1, '37.0', 1, 110.00, 5, '2025-08-10 15:23:34'),
(42, 18, 12, 2, '41.0', 1, 115.00, 5, '2025-08-10 20:32:06'),
(43, 19, 12, 2, '41.0', 1, 115.00, 7, '2025-08-10 20:33:19'),
(44, 20, 14, 1, '35.0', 1, 75.00, 6, '2025-08-11 14:06:28'),
(45, 20, 9, 1, '40.0', 1, 90.00, 6, '2025-08-11 14:06:28'),
(46, 20, 8, 1, '39.0', 1, 90.00, 6, '2025-08-11 14:06:28'),
(47, 20, 10, 1, '42.0', 1, 100.00, 6, '2025-08-11 14:06:29'),
(48, 20, 15, 1, '34.0', 1, 70.00, 6, '2025-08-11 14:06:29'),
(49, 21, 12, 2, '41.0', 1, 115.00, 6, '2025-08-11 19:37:41'),
(50, 21, 3, 2, '38.0', 1, 150.00, 6, '2025-08-11 19:37:41'),
(51, 21, 7, 2, '37.0', 3, 110.00, 6, '2025-08-11 19:37:41'),
(52, 22, 4, 1, '40.0', 1, 130.00, 6, '2025-08-28 17:50:54');

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
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identificacion` (`identificacion`);

--
-- Indices de la tabla `clientes_emails`
--
ALTER TABLE `clientes_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `clientes_telefonos`
--
ALTER TABLE `clientes_telefonos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oportunidad_id` (`oportunidad_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`);

--
-- Indices de la tabla `fotos_productos`
--
ALTER TABLE `fotos_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`);

--
-- Indices de la tabla `oportunidades`
--
ALTER TABLE `oportunidades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `oportunidades_productos`
--
ALTER TABLE `oportunidades_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oportunidad_id` (`oportunidad_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `marca_id` (`marca_id`);

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
-- Indices de la tabla `tasa_diaria`
--
ALTER TABLE `tasa_diaria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `factura_id` (`factura_id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `clientes_emails`
--
ALTER TABLE `clientes_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes_telefonos`
--
ALTER TABLE `clientes_telefonos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `fotos_productos`
--
ALTER TABLE `fotos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `oportunidades`
--
ALTER TABLE `oportunidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `oportunidades_productos`
--
ALTER TABLE `oportunidades_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sincronizaciones`
--
ALTER TABLE `sincronizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tasa_diaria`
--
ALTER TABLE `tasa_diaria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `clientes_emails`
--
ALTER TABLE `clientes_emails`
  ADD CONSTRAINT `clientes_emails_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes_telefonos`
--
ALTER TABLE `clientes_telefonos`
  ADD CONSTRAINT `clientes_telefonos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalles_compra`
--
ALTER TABLE `detalles_compra`
  ADD CONSTRAINT `detalles_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `detalles_compra_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_ibfk_1` FOREIGN KEY (`oportunidad_id`) REFERENCES `oportunidades` (`id`),
  ADD CONSTRAINT `facturas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `facturas_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `facturas_compras`
--
ALTER TABLE `facturas_compras`
  ADD CONSTRAINT `facturas_compras_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotos_productos`
--
ALTER TABLE `fotos_productos`
  ADD CONSTRAINT `fotos_productos_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD CONSTRAINT `historial_precios_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD CONSTRAINT `metodo_pago_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`);

--
-- Filtros para la tabla `oportunidades_productos`
--
ALTER TABLE `oportunidades_productos`
  ADD CONSTRAINT `oportunidades_productos_ibfk_1` FOREIGN KEY (`oportunidad_id`) REFERENCES `oportunidades` (`id`),
  ADD CONSTRAINT `oportunidades_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`);

--
-- Filtros para la tabla `sincronizaciones`
--
ALTER TABLE `sincronizaciones`
  ADD CONSTRAINT `sincronizaciones_ibfk_1` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`),
  ADD CONSTRAINT `ventas_ibfk_4` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
