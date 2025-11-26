-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: zeta
-- ------------------------------------------------------
-- Server version	10.4.27-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Bicicletas'),(2,'Accesorios'),(3,'Botellas de Agua'),(4,'Herramientas');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `cliente_id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `calle` varchar(50) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `estado` varchar(25) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'Juan','Pérez','79394898','juanP@gmail.com','Av. Valles 24','Santa Cruz','Scz','1761648345_perfilhombre2.jpeg'),(2,'Ana Marie','López','77799898','anamarie@gmail.com','Calle Bolívar 100','La Paz','Lpz','1761648228_perfilmujer.jpeg'),(3,'Mario','Suárez Scalante','723423423','suarez@gmail.com','Av. Banzer 6to anillo','Santa Cruz','Scz','1761648354_perfilhombre3.jpeg'),(4,'Lucíana','vazquez','76543212','lucianavazquez@gmail.com','Calle Pailas 42','Tarija','Tarija','1761648255_perfilmujer2.jpeg');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pedido`
--

DROP TABLE IF EXISTS `detalles_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_pedido` (
  `detalle_id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `descuento` decimal(4,2) DEFAULT 0.00,
  PRIMARY KEY (`detalle_id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`pedido_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pedido`
--

LOCK TABLES `detalles_pedido` WRITE;
/*!40000 ALTER TABLE `detalles_pedido` DISABLE KEYS */;
INSERT INTO `detalles_pedido` VALUES (2,1,7,2,350.00,0.10),(3,2,5,1,2100.00,0.00),(4,2,8,1,120.00,0.00),(7,5,3,3,2500.00,0.00),(8,6,3,6,2500.00,6.00),(9,7,3,8,2500.00,10.00),(10,8,3,4,2500.00,10.00),(12,10,6,4,1750.00,10.00),(13,11,6,5,1750.00,4.00),(14,12,9,1,200.00,10.00),(15,13,10,1,950.00,20.00),(16,14,6,4,1750.00,10.00),(17,15,8,5,120.00,10.00),(19,17,8,5,120.00,5.00),(20,18,10,5,950.00,50.00),(21,18,7,4,350.00,10.00),(22,18,6,5,1750.00,20.00),(23,19,7,1,350.00,5.00),(24,19,10,1,1950.00,5.00),(31,24,10,1,1950.00,5.00),(32,25,3,1,2500.00,10.00);
/*!40000 ALTER TABLE `detalles_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empleados` (
  `empleado_id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `tienda_id` int(11) NOT NULL,
  `gerente_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`empleado_id`),
  KEY `tienda_id` (`tienda_id`),
  KEY `gerente_id` (`gerente_id`),
  CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`tienda_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`gerente_id`) REFERENCES `empleados` (`empleado_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` VALUES (1,'Juan','Martínez','juan.martinez@bikestore.com','77720001',1,1,NULL),(2,'María','Gonzales','maria.gonzales@bikestore.com','77720002',1,1,1),(3,'Carlos','Rodríguez','carlos.rodriguez@bikestore.com','77720003',1,2,NULL),(4,'Ana','Silva','ana.silva@bikestore.com','77720004',1,2,3),(5,'Pedro','López','pedro.lopez@bikestore.com','77720005',1,3,NULL),(6,'Laura','Torrez','laura.torrez@bikestore.com','77720006',1,3,5),(7,'Jhoel','Mendoza','jhoel.mendoza@bikestore.com','77720007',1,5,NULL),(8,'Sofía','Rojas','sofia.rojas@bikestore.com','77720008',1,4,7);
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventario`
--

DROP TABLE IF EXISTS `inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventario` (
  `tienda_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `empleado_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tienda_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `inventario_ibfk_1` FOREIGN KEY (`tienda_id`) REFERENCES `tiendas` (`tienda_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventario_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `productos` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inventario_ibfk_3` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`empleado_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventario`
--

LOCK TABLES `inventario` WRITE;
/*!40000 ALTER TABLE `inventario` DISABLE KEYS */;
INSERT INTO `inventario` VALUES (1,2,10,2),(1,3,23,2),(1,5,2,2),(1,6,1,2),(1,7,10,2),(1,8,4,2),(1,9,6,2),(1,10,40,2),(2,2,2,4),(2,3,1,4),(2,5,2,4),(2,6,2,4),(2,7,8,4),(2,8,6,4),(2,9,4,4),(2,10,0,4),(3,2,3,6),(3,3,1,6),(3,5,3,6),(3,6,4,6),(3,7,12,6),(3,8,10,6),(3,9,8,6),(3,10,2,6),(4,2,10,8),(4,6,70,8),(5,9,50,7);
/*!40000 ALTER TABLE `inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pagos` (
  `pago_id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `monto_total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `estado` varchar(25) DEFAULT 'Completado',
  PRIMARY KEY (`pago_id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`pedido_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`cliente_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`empleado_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,19,1,7,'2025-10-28 07:03:26',2300.00,'Efectivo','Completado'),(6,24,4,3,'2025-10-28 09:47:34',1950.00,'Envío Gratuito','Completado'),(7,25,4,5,'2025-10-28 09:49:21',2500.00,'Envío Gratuito','Completado');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `after_pago_insert` AFTER INSERT ON `pagos` FOR EACH ROW BEGIN
    -- Declarar variables
    DECLARE done INT DEFAULT FALSE;
    DECLARE var_producto_id INT;
    DECLARE var_cantidad INT;
    DECLARE var_tienda_id INT;
    
    -- Cursor para obtener los detalles del pedido
    DECLARE cur_detalles CURSOR FOR 
    SELECT dp.producto_id, dp.cantidad, p.empleado_id
    FROM detalles_pedido dp
    INNER JOIN pedidos p ON dp.pedido_id = p.pedido_id
    WHERE dp.pedido_id = NEW.pedido_id;
    
    -- Declarar manejador para el cursor
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Obtener la tienda del empleado
    SELECT tienda_id INTO var_tienda_id 
    FROM empleados 
    WHERE empleado_id = NEW.empleado_id;
    
    -- Abrir cursor y procesar cada detalle
    OPEN cur_detalles;
    
    read_loop: LOOP
        FETCH cur_detalles INTO var_producto_id, var_cantidad, var_tienda_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Actualizar el inventario (restar la cantidad vendida)
        UPDATE inventario 
        SET cantidad = cantidad - var_cantidad
        WHERE tienda_id = var_tienda_id 
        AND product_id = var_producto_id;
        
    END LOOP;
    
    CLOSE cur_detalles;
    
    -- Actualizar el estado del pedido a "Entregado"
    UPDATE pedidos 
    SET estado = 'Entregado' 
    WHERE pedido_id = NEW.pedido_id;
    
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `pedido_id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `fecha_pedido` date NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `estado` varchar(25) DEFAULT 'Activo',
  PRIMARY KEY (`pedido_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`cliente_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`empleado_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,1,'2025-10-01',1,2,'Entregado'),(2,2,'2025-10-02',1,2,'Entregado'),(3,3,'2025-10-03',1,2,'Entregado'),(4,2,'2025-10-17',1,2,'Entregado'),(5,3,'2025-10-16',1,2,'Entregado'),(6,2,'2025-10-20',1,2,'Pendiente'),(7,2,'2025-10-20',1,2,'Pendiente'),(8,2,'2025-10-20',1,2,'Pendiente'),(10,1,'2025-10-23',1,2,'Pendiente'),(11,2,'2025-10-23',1,2,'Pendiente'),(12,2,'2025-10-27',1,2,'Pendiente'),(13,2,'2025-10-28',1,2,'Pendiente'),(14,2,'2025-10-30',1,2,'Pendiente'),(15,2,'2025-10-31',2,4,'Pendiente'),(17,4,'2025-10-21',1,2,'Entregado'),(18,2,'2025-10-24',1,2,'Entregado'),(19,1,'2025-10-28',1,7,'Entregado'),(24,4,'2025-10-28',1,3,'Entregado'),(25,4,'2025-10-28',1,5,'Entregado');
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `model_year` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categorias` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (2,'Trek Fuel EX 8 29 - 2017','1761648387_bikeMTB29.jpeg',2017,3800.00,1,'2025-10-20 16:41:52'),(3,'Bici Urbana - 2020','1761648415_bikurban700c.jpeg',2020,2500.00,1,'2025-10-20 16:41:52'),(5,'Scott Scale 980','1761648483_bici.jpeg',2023,2500.00,1,'2025-10-20 16:41:52'),(6,'Botella 700ml','1761648541_botella.jpeg',2022,120.00,3,'2025-10-20 16:41:52'),(7,'Casco Ruta','1761648568_cascoruta.jpeg',2023,350.00,2,'2025-10-20 16:41:52'),(8,'Guantes Gel','1761648588_guantesdegel.jpeg',2023,120.00,2,'2025-10-20 16:41:52'),(9,'Luz Delantera','1761648639_luz.jpeg',2024,200.00,2,'2025-10-20 16:41:52'),(10,'Kit Herramientas 200','1761648736_kit.jpeg',2025,1950.00,2,'2025-10-20 16:41:52');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiendas`
--

DROP TABLE IF EXISTS `tiendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiendas` (
  `tienda_id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tienda` varchar(100) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `calle` varchar(100) DEFAULT NULL,
  `ciudad` varchar(50) DEFAULT NULL,
  `estado` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`tienda_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiendas`
--

LOCK TABLES `tiendas` WRITE;
/*!40000 ALTER TABLE `tiendas` DISABLE KEYS */;
INSERT INTO `tiendas` VALUES (1,'Bike Store Centro','77710001','centro@bikestore.com','Av. Heroínas 123','Cochabamba','Cochabamba'),(2,'Bike Store Norte','77710002','norte@bikestore.com','Av. América 456','La Paz','La Paz'),(3,'Bike Store Sur','77710003','sur@bikestore.com','Av. Cañoto 789','Santa Cruz','Santa Cruz'),(4,'BikeStoreBanzer','78550666','bikebanzer@gmail.com','Av. Banzer 5to anillo','scz','scz'),(5,'bikeMall','78550667','groov3x99@gmail.com','Ventura Mall','Santa Cruz','Santa Cruz');
/*!40000 ALTER TABLE `tiendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'admin','123','admin@bikestore.com'),(2,'zeus','123','zeus@gmail.com');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-25 19:53:24
