/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abogados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abogados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_apellido` varchar(255) NOT NULL,
  `cargo` varchar(255) NOT NULL,
  `n_inprebogado` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `almacenamiento_impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `almacenamiento_impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` date NOT NULL,
  `colore_id` bigint(20) unsigned DEFAULT NULL,
  `cartucho_id` bigint(20) unsigned DEFAULT NULL,
  `impresora_id` bigint(20) unsigned DEFAULT NULL,
  `proveedore_id` bigint(20) unsigned NOT NULL,
  `fabricante_id` bigint(20) unsigned DEFAULT NULL,
  `n_factura` varchar(255) NOT NULL,
  `unidades` int(11) NOT NULL,
  `unidades_disponibles` int(11) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `almacenamiento_impresoras_user_id_foreign` (`user_id`),
  CONSTRAINT `almacenamiento_impresoras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `area_adscritas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `area_adscritas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `area_adscrita` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `area` varchar(255) NOT NULL,
  `gerencia_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `areas_gerencia_id_foreign` (`gerencia_id`),
  CONSTRAINT `areas_gerencia_id_foreign` FOREIGN KEY (`gerencia_id`) REFERENCES `gerencias` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articulos` (
  `id` int(11) NOT NULL,
  `articulo` varchar(255) NOT NULL,
  `stock_min` int(11) NOT NULL,
  `unidad_medida_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bienes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bienes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sede` varchar(255) NOT NULL,
  `n_interno_bien` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `forma_adquisicion_id` bigint(20) unsigned NOT NULL,
  `fecha_adquisicion` date NOT NULL,
  `n_factura` varchar(255) NOT NULL,
  `valor_factura` varchar(255) NOT NULL,
  `moneda` varchar(255) NOT NULL,
  `estado_del_uso_id` bigint(20) unsigned NOT NULL,
  `condicion_id` bigint(20) unsigned NOT NULL,
  `marca` varchar(255) NOT NULL,
  `modelo` varchar(255) NOT NULL,
  `color_id` bigint(20) unsigned NOT NULL,
  `unidad_administrativa_id` bigint(20) unsigned NOT NULL,
  `usuario_administrativo_id` bigint(20) unsigned NOT NULL,
  `usuario_de_uso_id` bigint(20) unsigned NOT NULL,
  `serial` varchar(255) NOT NULL,
  `qr_code` text DEFAULT NULL,
  `comentario` varchar(255) NOT NULL,
  `fecha_comentario` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cartuchos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cartuchos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cartucho` varchar(255) NOT NULL,
  `stock_min` int(11) NOT NULL,
  `colore_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `impresora_id` bigint(20) unsigned NOT NULL,
  `fabricante_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cdts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cdts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `colores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `colore` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `color` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `condicions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `condicions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `condicion` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contratados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contratados` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `n_contrato` varchar(255) NOT NULL,
  `nombre_apellido` varchar(255) NOT NULL,
  `cedula` varchar(255) NOT NULL,
  `estado_civil` varchar(255) NOT NULL,
  `nacionalidad` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gerencia_id` bigint(20) unsigned NOT NULL,
  `ocupacion` varchar(255) NOT NULL,
  `rif` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `digitalizacion_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digitalizacion_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `digitalizacione_id` bigint(20) unsigned NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tipo_documento_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digitalizacion_documentos_tipo_documento_id_foreign` (`tipo_documento_id`),
  CONSTRAINT `digitalizacion_documentos_tipo_documento_id_foreign` FOREIGN KEY (`tipo_documento_id`) REFERENCES `tipo_documentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `digitalizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digitalizaciones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_apellido` varchar(255) NOT NULL,
  `expediente` varchar(255) DEFAULT NULL,
  `salud` enum('Titular','Padre','Madre','Hijo','Sobrino','Nieto') NOT NULL,
  `area_adscrita_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cedula` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `directivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `directivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_apellido` varchar(255) NOT NULL,
  `cedula` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cargo` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `distribucion_almacenamiento_impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `distribucion_almacenamiento_impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `almacenamiento_impresora_id` bigint(20) unsigned NOT NULL,
  `gerencia_id` bigint(20) unsigned NOT NULL,
  `unidades_asignadas` int(11) NOT NULL,
  `unidades_asignadas_originales` int(11) DEFAULT NULL,
  `unidades_restantes` int(11) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `distribucion_almacenamiento_impresoras_user_id_foreign` (`user_id`),
  CONSTRAINT `distribucion_almacenamiento_impresoras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documento_archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documento_archivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `documento_id` bigint(20) unsigned NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `categoria_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documento_archivos_documento_id_foreign` (`documento_id`),
  KEY `documento_archivos_categoria_id_foreign` (`categoria_id`),
  KEY `documento_archivos_user_id_foreign` (`user_id`),
  CONSTRAINT `documento_archivos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documento_archivos_documento_id_foreign` FOREIGN KEY (`documento_id`) REFERENCES `documentos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documento_archivos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `estanteria_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentos_documento_unique` (`documento`),
  KEY `documentos_estanteria_id_foreign` (`estanteria_id`),
  CONSTRAINT `documentos_estanteria_id_foreign` FOREIGN KEY (`estanteria_id`) REFERENCES `estanterias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estado_del_usos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estado_del_usos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `estado_del_uso` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estanterias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estanterias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estanterias_user_id_foreign` (`user_id`),
  CONSTRAINT `estanterias_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fabricantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fabricantes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fabricante` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `forma_adquisicions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forma_adquisicions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forma_adquisicion` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `gerencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gerencias` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gerencia` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `historico_unidades_disponibles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_unidades_disponibles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `impresora` varchar(255) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventario_impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` date NOT NULL,
  `colore_id` bigint(20) unsigned NOT NULL,
  `cartucho_id` bigint(20) unsigned NOT NULL,
  `impresora_id` bigint(20) unsigned NOT NULL,
  `proveedore_id` bigint(20) unsigned NOT NULL,
  `fabricante_id` bigint(20) unsigned NOT NULL,
  `ubicacion_id` bigint(20) unsigned NOT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  `n_factura` varchar(255) NOT NULL,
  `unidades` int(11) NOT NULL,
  `unidades_disponibles` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gerencia_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_impresoras_user_id_foreign` (`user_id`),
  CONSTRAINT `inventario_impresoras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventario_oficinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventario_oficinas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_ingreso` date NOT NULL,
  `unidad_medida_id` bigint(20) unsigned NOT NULL,
  `articulo_id` bigint(20) unsigned NOT NULL,
  `proveedore_id` bigint(20) unsigned NOT NULL,
  `ubicacion_id` bigint(20) unsigned NOT NULL,
  `n_factura` varchar(255) NOT NULL,
  `unidades` int(11) NOT NULL,
  `unidades_disponibles` int(11) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventario_oficinas_user_id_foreign` (`user_id`),
  CONSTRAINT `inventario_oficinas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `presentacions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `presentacions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `presentacion` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `proveedor` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `registers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `salida_almacenamiento_impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salida_almacenamiento_impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_salida` date NOT NULL,
  `almacenamiento_impresora_id` bigint(20) unsigned NOT NULL,
  `distribucion_almacenamiento_impresora_id` bigint(20) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL,
  `historico_unidades_disponibles` int(11) NOT NULL,
  `historico_unidades_asignadas_originales` int(11) DEFAULT NULL,
  `historico_unidades_asignadas_restantes` int(11) DEFAULT NULL,
  `ajuste_inventario` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salida_almacenamiento_impresoras_user_id_foreign` (`user_id`),
  CONSTRAINT `salida_almacenamiento_impresoras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `salida_impresoras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salida_impresoras` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_salida` date NOT NULL,
  `cartucho_id` bigint(20) unsigned NOT NULL,
  `colore_id` bigint(20) unsigned NOT NULL,
  `impresora_id` bigint(20) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL,
  `inventario_impresora_id` bigint(20) unsigned NOT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  `ajuste_inventario` varchar(255) NOT NULL,
  `historico_unidades_disponibles` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gerencia_id` bigint(20) unsigned NOT NULL,
  `fabricante_id` bigint(20) unsigned NOT NULL,
  `tipo_consumible` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `salida_oficinas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salida_oficinas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fecha_salida` date NOT NULL,
  `unidad_medida_id` bigint(20) unsigned NOT NULL,
  `articulo_id` bigint(20) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL,
  `inventario_oficina_id` bigint(20) unsigned NOT NULL,
  `area_id` bigint(20) unsigned NOT NULL,
  `ajuste_inventario` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `historico_unidades_disponibles` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gerencia_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `salida_oficinas_user_id_foreign` (`user_id`),
  CONSTRAINT `salida_oficinas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_documentos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ubicacions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ubicacions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unidad_administrativas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidad_administrativas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unidad_medidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidad_medidas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unidad_medida` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `cedula` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `departamento` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_cedula_unique` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuario_administrativos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_administrativos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_administrativo` varchar(255) NOT NULL,
  `unidad_administrativa_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuario_de_usos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_de_usos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_de_uso` varchar(255) NOT NULL,
  `unidad_administrativa_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2024_04_05_150830_create_equipos_ingresos_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2014_10_12_000000_create_users_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2014_10_12_100000_create_password_reset_tokens_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2019_08_19_000000_create_failed_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2019_12_14_000001_create_personal_access_tokens_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2023_03_22_201051_create_categorias_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2023_03_22_201052_create_documentos_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2023_03_24_145724_create_permission_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2023_04_20_154740_create_bienes_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2023_04_20_155424_create_unidad_administrativas_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2023_04_20_155726_create_forma_adquisicions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2023_04_20_164645_create_estado_del_usos_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2024_02_26_143848_create_colors_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2024_02_26_144204_create_condicions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2024_04_03_130613_create_usuario_de_usos_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2024_04_04_155650_create_presentacions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2024_04_26_140838_create_usuario_administrativos_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2024_08_28_112819_create_eventos_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2024_08_28_115406_add_field_to_eventos_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2024_09_02_113734_create_events_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2024_09_05_145319_create_directivos_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2024_09_05_153353_add_field_to_directivos_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2024_09_05_155304_create_cdts_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2024_09_06_084527_create_contratados_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2024_09_06_091432_create_abogados_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2024_09_10_135401_add_field_to_directivos_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2024_09_16_114636_create_articulos_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2024_09_16_114706_create_unidad_medidas_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2024_09_16_114720_create_areas_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2024_09_16_114744_create_inventario_oficinas_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2024_09_16_114755_create_salida_oficinas_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2024_09_16_114939_create_proveedores_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2024_09_16_115630_create_ubicacions_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2024_09_20_150828_add_field_to_salida_oficinas_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2024_09_23_090904_create_cartuchos_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2024_09_23_090929_create_impresoras_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2024_09_23_091007_create_fabricantes_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2024_09_23_091037_create_inventario_impresoras_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2024_09_23_091050_create_salida_impresoras_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2024_09_23_091203_create_colores_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2024_09_23_113733_create_gerencias_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2024_09_23_122455_add_field_to_gerencias_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2024_09_23_122754_add_field_to_inventario_oficinas_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2024_09_23_142608_add_field_to_salida_oficinas_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2024_09_24_123417_add_field_to_inventario_impresoras_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2024_09_30_110730_add_field_to_salida_impresoras_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2024_10_03_121255_create_registers_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2024_11_06_143903_create_digitalizaciones_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2024_11_08_111436_create_tipo_documentos_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2024_11_08_113544_create_area_adscritas_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2024_11_12_094728_add_field_to_digitalizaciones_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2025_02_12_102850_modify_id_format_in_articulos',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2025_02_12_100524_add_field_to_articulos_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2025_02_12_110527_update_ids_in_articulos_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2025_02_12_120719_update_articulo_id_in_inventario_oficinas',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2025_02_12_122122_update_articulo_id_in_inventario_oficinas',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2025_02_12_122836_update_articulo_id_in_inventario_oficinas',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2025_02_12_141511_update_inventario_oficinas_ids',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2025_02_12_141650_update_inventario_oficinas_ids',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2025_02_12_142816_update_inventario_oficinas_ids',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2025_02_12_154347_revertir_ids_articulos',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2025_02_28_145830_remove_descripcion_from_inventario_oficinas',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2025_03_19_100620_add_field_to_contratados_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2025_03_19_154426_add_field_to_contratados_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2025_09_25_104126_add_impresora_and_fabricante_to_cartuchos_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2025_09_25_110448_add_imagen_to_impresoras_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2025_09_25_115652_add_fabricante_to_salida_impresoras_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2025_09_25_143209_drop_ubicacion_id_from_salida_impresoras_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2025_09_25_144041_add_tipo_consumible_to_salida_impresoras_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2025_09_25_153939_create_historico_unidades_disponibles_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2025_10_02_144400_add_gerencia_id_to_areas_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2025_10_17_092753_create_sessions_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2025_10_29_122829_add_gerencia_area_to_almacenamiento_impresoras_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2025_10_28_142454_create_almacenamiento_impresoras_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2025_10_30_114249_create_distribucion_almacenamiento_impresoras_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2025_10_30_144323_drop_columns_from_distribucion_almacenamiento_impresoras_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2025_01_01_000000_create_salida_almacenamiento_impresoras_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2025_10_31_102956_add_unidades_asignadas_originales_to_distribucion_almacenamiento_impresoras_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2025_10_31_103617_add_unidades_restantes_to_distribucion_almacenamiento_impresoras_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2025_10_31_104404_add_historico_unidades_asignadas_originales_to_salida_almacenamiento_impresoras_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2025_10_31_105004_add_historico_unidades_asignadas_restantes_to_salida_almacenamiento_impresoras_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2025_11_05_103857_update_ubicacion_id_in_salida_oficinas_table',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2025_11_18_000000_add_user_id_to_salida_almacenamiento_impresoras_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2025_11_18_000001_add_user_id_to_salida_oficinas_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2025_11_18_000002_add_user_id_to_inventario_impresoras_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2025_11_18_000003_add_user_id_to_inventario_oficinas_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2025_11_18_000004_add_user_id_to_almacenamiento_impresoras_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2025_11_18_000005_add_user_id_to_distribucion_almacenamiento_impresoras_table',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2025_11_18_100454_create_activity_log_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2025_11_18_100455_add_event_column_to_activity_log_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2025_11_18_100456_add_batch_uuid_column_to_activity_log_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2025_11_27_111400_drop_ubicacion_id_from_inventario_oficinas_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2025_11_27_111506_drop_ubicacions_table',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2025_11_27_111628_drop_ubicacion_id_from_inventario_impresoras_table',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2025_11_28_160522_drop_gerencia_area_from_inventario_oficinas',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2025_12_09_000001_add_user_id_to_impresoras_table',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2025_11_18_165000_remove_ubicacion_id_from_salida_oficinas_table',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2024_12_20_000001_create_digitalizacion_documentos_table',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2024_12_20_000002_add_tipo_documento_id_to_digitalizacion_documentos_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2024_12_20_000003_remove_tipo_documento_id_from_digitalizaciones_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2025_12_20_000001_create_documento_archivos_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2025_12_20_000002_make_documento_nullable_in_documentos_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2025_12_20_000004_create_estanterias_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2025_01_23_000001_add_estanteria_id_to_documentos_table',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2025_01_01_000001_add_qr_code_to_bienes_table',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2025_01_02_000001_change_qr_code_column_type',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2025_01_18_000001_add_fields_to_users_table',67);
