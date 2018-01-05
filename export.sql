-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия на сървъра:            5.7.20-log - MySQL Community Server (GPL)
-- ОС на сървъра:                Win64
-- HeidiSQL Версия:              9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for onlinegame
CREATE DATABASE IF NOT EXISTS `onlinegame` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `onlinegame`;

-- Дъмп структура за събитие onlinegame.AddResEachTwoMin
DELIMITER //
CREATE DEFINER=`root`@`localhost` EVENT `AddResEachTwoMin` ON SCHEDULE EVERY 2 MINUTE STARTS '2018-01-01 20:00:00' ENDS '2018-12-31 20:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
	set @x = 0;
	REPEAT SET @x = @x + 1;
		CALL AddResInKngdm((select count(*) from resources), @x);
	UNTIL @x >= (select count(*) from kingdom) END REPEAT;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.AddResInKngdm
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddResInKngdm`(
	IN `v1` INT





,
	IN `kingdom_id` INT









)
BEGIN
	SET @x = 0;
	REPEAT SET @x = @x + 1;
		select sum(group_res.value * kngdm_bldng.`level`) into @add_value
		from group_res inner join building inner join kngdm_bldng
		where group_res.group_name=building.id_resGivePH
		and building.id=kngdm_bldng.id_building 
		and group_res.id_resourse=@x
		and kngdm_bldng.id_kingdom=kingdom_id;

		update kngdm_res inner join resources inner join kingdom 
		set kngdm_res.value_double=(kngdm_res.value_double + (@add_value / 30))
		where kngdm_res.id_kingdom=kingdom.id 
		and kngdm_res.id_resourse=resources.id
		and kingdom.id=kingdom_id
		and resources.id=@x;

		select value_double into @y from kngdm_res where kngdm_res.id_kingdom = kingdom_id and kngdm_res.id_resourse = @x;
		if (@y >= 1) then
			set @max_value = -1;
			call GetMaxResForKingdom(kingdom_id, @x);
			repeat set @y = (@y - 1);
				if (@max_value > (select value from kngdm_res 
				  where kngdm_res.id_kingdom = kingdom_id and kngdm_res.id_resourse = @x)) then 
					update kngdm_res 
					set kngdm_res.value = (kngdm_res.value + 1) 
					where kngdm_res.id_kingdom = kingdom_id 
					and kngdm_res.id_resourse = @x;
				end if;
			UNTIL @y < 1 END REPEAT;
			
			update kngdm_res 
			set kngdm_res.value_double = @y
			where kngdm_res.id_kingdom = kingdom_id
			and kngdm_res.id_resourse = @x;
		elseif (@y < 0) then
			update kngdm_res set kngdm_res.value_double = 0 where kngdm_res.id_kingdom = kingdom_id and kngdm_res.id_resourse = @x;
		end if;
	
	UNTIL @x >= v1 END REPEAT;
END//
DELIMITER ;

-- Дъмп структура за таблица onlinegame.attack
CREATE TABLE IF NOT EXISTS `attack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_att` int(11) NOT NULL,
  `id_def` int(11) NOT NULL,
  `id_gUnit_att` int(11) NOT NULL,
  `id_gUnit_def` int(11) NOT NULL,
  `result` set('true','false') DEFAULT NULL,
  `madeOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_attack_users` (`id_att`),
  KEY `FK_attack_users_2` (`id_def`),
  KEY `FK_attack_group_unit` (`id_gUnit_att`),
  KEY `FK_attack_group_unit_2` (`id_gUnit_def`),
  CONSTRAINT `FK_attack_group_unit` FOREIGN KEY (`id_gUnit_att`) REFERENCES `group_unit` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_attack_group_unit_2` FOREIGN KEY (`id_gUnit_def`) REFERENCES `group_unit` (`id_group`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_attack_users` FOREIGN KEY (`id_att`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_attack_users_2` FOREIGN KEY (`id_def`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.attack: ~0 rows (approximately)
DELETE FROM `attack`;
/*!40000 ALTER TABLE `attack` DISABLE KEYS */;
/*!40000 ALTER TABLE `attack` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.building
CREATE TABLE IF NOT EXISTS `building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `id_resGivePH` varchar(20) DEFAULT NULL,
  `id_needBldngL` varchar(20) DEFAULT NULL,
  `id_resNeedPL` varchar(20) DEFAULT NULL,
  `timeNeedPL` int(11) DEFAULT NULL,
  `action` varchar(20) DEFAULT NULL,
  `action_label` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `FK_building_group_res` (`id_resGivePH`),
  KEY `FK_building_group_res_2` (`id_resNeedPL`),
  KEY `FK_building_group_bldng` (`id_needBldngL`),
  CONSTRAINT `FK_building_group_bldng` FOREIGN KEY (`id_needBldngL`) REFERENCES `group_bldng` (`group_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_building_group_res` FOREIGN KEY (`id_resGivePH`) REFERENCES `group_res` (`group_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_building_group_res_2` FOREIGN KEY (`id_resNeedPL`) REFERENCES `group_res` (`group_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.building: ~13 rows (approximately)
DELETE FROM `building`;
/*!40000 ALTER TABLE `building` DISABLE KEYS */;
INSERT INTO `building` (`id`, `name`, `id_resGivePH`, `id_needBldngL`, `id_resNeedPL`, `timeNeedPL`, `action`, `action_label`) VALUES
	(1, 'square', NULL, NULL, 'NPL_square', 75, NULL, 'My kingdom'),
	(2, 'house', 'GPH_house', NULL, 'NPL_house', 15, NULL, NULL),
	(3, 'wall', NULL, 'NBL_B-wall', 'NPL_wall', 30, NULL, NULL),
	(4, 'barrack', NULL, 'NBL_B-barrack', 'NPL_barrack', 45, '/unit/order', 'Order unit'),
	(5, 'lumberjack', 'GPH_lumberjack', 'NBL_B-lumberjack', 'NPL_lumberjack', 25, NULL, NULL),
	(6, 'farm', 'GPH_farm', 'NBL_B-farm', 'NPL_farm', 30, NULL, NULL),
	(7, 'stoneMine', 'GPH_stoneMine', 'NBL_B-stoneMine', 'NPL_stoneMine', 35, NULL, NULL),
	(8, 'goldMine', 'GPH_goldMine', 'NBL_B-goldMine', 'NPL_goldMine', 40, NULL, NULL),
	(9, 'storehouse', 'GPH_storehouse', 'NBL_B-storehouse', 'NPL_storehouse', 55, NULL, NULL),
	(10, 'market', NULL, 'NBL_B-market', 'NPL_market', 88, NULL, 'Trade'),
	(11, 'castle', 'GPH_castle', 'NBL_B-castle', 'NPL_castle', 3630, NULL, NULL),
	(12, 'palace', NULL, 'NBL_B-palace', 'NPL_palace', 32100, '/attack', 'Attack'),
	(13, 'monument', 'GPH_monument', 'NBL_B-monument', 'NPL_monument', 86400, NULL, NULL);
/*!40000 ALTER TABLE `building` ENABLE KEYS */;

-- Дъмп структура за procedure onlinegame.CreateEvent
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateEvent`()
BEGIN
	set @sql_query = concat('CREATE EVENT `testingg3` ON SCHEDULE AT "2018-01-02 12:10:19" DO	select * from resources;');
	do @sql_query;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetBuildingsInKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetBuildingsInKingdom`(
	IN `id` INT





)
BEGIN
select building.id, building.name, building.id_resGivePH, building.id_needBldngL, building.id_resNeedPL, 
building.timeNeedPL, building.`action`, building.action_label, kngdm_bldng.`level`, kngdm_bldng.ready_on
from kngdm_bldng 
inner join building
where kngdm_bldng.id_kingdom=id
and kngdm_bldng.id_building=building.id
order by building.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetGPHOfBuilding
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetGPHOfBuilding`(
	IN `building_name` VARCHAR(20)


)
BEGIN
select resources.name, group_res.value
from group_res 
inner join resources
where group_res.group_name=CONCAT('GPH_', building_name)
and group_res.id_resourse=resources.id
order by resources.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetMaxResForKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMaxResForKingdom`(
	IN `kingdom_id` INT


,
	IN `resource_id` INT


)
BEGIN
	set @storehouseId = 9;
	set @houseId = 2;
	set @populationId = 7;
	set @GPH_house_Gname = 'GPH_house';
	set @hoursNeedToReachMaxPopulation = 25;
	
	select `level` into @storehouseLevel from kngdm_bldng where kngdm_bldng.id_building=@storehouseId and kngdm_bldng.id_kingdom=kingdom_id;
	select `level` into @houseLevel from kngdm_bldng where kngdm_bldng.id_building=@houseId and kngdm_bldng.id_kingdom=kingdom_id;
	select `value` into @GPH_house_value from group_res where group_name=@GPH_house_Gname and id_resourse=@populationId;
	
	set @addValue = @houseLevel * @GPH_house_value * @hoursNeedToReachMaxPopulation;
	set @storehouseLevel = @storehouseLevel + 1;
	if (resource_id = 0) then
		select ((default_value * @storehouseLevel) + if(id=@populationId, @addValue, 0)) as 'max_value', name from resources order by resources.id;
	else
		select ((default_value * @storehouseLevel) + if(id=@populationId, @addValue, 0)) into @max_value from resources where id=resource_id;
	end if;
	
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetNBLOfBuilding
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNBLOfBuilding`(
	IN `building_name` VARCHAR(20)
)
BEGIN
select building.name, group_bldng.`level`
from group_bldng 
inner join building
where group_bldng.group_name=CONCAT('NBL_B-', building_name)
and group_bldng.id_building=building.id
order by building.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetNBLOfUnit
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNBLOfUnit`(
	IN `building_name` VARCHAR(20)
)
BEGIN
	select building.name, group_bldng.`level`
	from group_bldng 
	inner join building
	where group_bldng.group_name=CONCAT('NBL_U-', building_name)
	and group_bldng.id_building=building.id
	order by building.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetNPLOfBuilding
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNPLOfBuilding`(
	IN `building_name` VARCHAR(20)
)
BEGIN
select resources.name, group_res.value
from group_res 
inner join resources
where group_res.group_name=CONCAT('NPL_', building_name)
and group_res.id_resourse=resources.id
order by resources.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetNPUOfUnit
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetNPUOfUnit`(
	IN `unit_name` VARCHAR(20)
)
BEGIN
	select resources.name, group_res.value
	from group_res 
	inner join resources
	where group_res.group_name=CONCAT('NPU_', unit_name)
	and group_res.id_resourse=resources.id
	order by resources.id;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetResourcesForKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetResourcesForKingdom`(
	IN `id` INT



)
BEGIN
	select resources.name, kngdm_res.value
	from kngdm_res 
	inner join resources
	where kngdm_res.id_kingdom=id
	and kngdm_res.id_resourse=resources.id
	order by resources.id;
	call StartAllEvents();
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.GetUnitsInKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUnitsInKingdom`(
	IN `kingdom_id` INT

)
BEGIN
	select unit.id, unit.name, unit.`attack`, unit.live, unit.id_needBldngL, unit.id_resNeedPU, unit.timePU, kngdm_unit.`count`, kngdm_unit.order_count
	from kngdm_unit 
	inner join unit
	where kngdm_unit.id_kingdom=kingdom_id
	and kngdm_unit.id_unit=unit.id
	order by unit.id;
END//
DELIMITER ;

-- Дъмп структура за таблица onlinegame.group_bldng
CREATE TABLE IF NOT EXISTS `group_bldng` (
  `group_name` varchar(20) NOT NULL,
  `id_building` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_group_id_building` (`group_name`,`id_building`),
  KEY `FK_group_bldng_building` (`id_building`),
  CONSTRAINT `FK_group_bldng_building` FOREIGN KEY (`id_building`) REFERENCES `building` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.group_bldng: ~75 rows (approximately)
DELETE FROM `group_bldng`;
/*!40000 ALTER TABLE `group_bldng` DISABLE KEYS */;
INSERT INTO `group_bldng` (`group_name`, `id_building`, `level`) VALUES
	('NBL_B-barrack', 1, 7),
	('NBL_B-barrack', 3, 2),
	('NBL_B-barrack', 9, 5),
	('NBL_B-castle', 1, 8),
	('NBL_B-castle', 2, 10),
	('NBL_B-castle', 3, 5),
	('NBL_B-castle', 9, 7),
	('NBL_B-farm', 1, 1),
	('NBL_B-farm', 2, 2),
	('NBL_B-farm', 5, 1),
	('NBL_B-goldMine', 2, 3),
	('NBL_B-goldMine', 5, 2),
	('NBL_B-goldMine', 7, 1),
	('NBL_B-lumberjack', 1, 1),
	('NBL_B-lumberjack', 2, 1),
	('NBL_B-market', 1, 5),
	('NBL_B-market', 2, 11),
	('NBL_B-market', 3, 4),
	('NBL_B-market', 9, 7),
	('NBL_B-monument', 1, 40),
	('NBL_B-monument', 2, 48),
	('NBL_B-monument', 3, 37),
	('NBL_B-monument', 4, 25),
	('NBL_B-monument', 5, 30),
	('NBL_B-monument', 6, 32),
	('NBL_B-monument', 7, 30),
	('NBL_B-monument', 8, 30),
	('NBL_B-monument', 9, 25),
	('NBL_B-monument', 11, 28),
	('NBL_B-monument', 12, 12),
	('NBL_B-palace', 1, 21),
	('NBL_B-palace', 2, 20),
	('NBL_B-palace', 3, 10),
	('NBL_B-palace', 4, 13),
	('NBL_B-palace', 5, 15),
	('NBL_B-palace', 6, 15),
	('NBL_B-palace', 7, 15),
	('NBL_B-palace', 8, 15),
	('NBL_B-palace', 9, 17),
	('NBL_B-palace', 11, 7),
	('NBL_B-stoneMine', 2, 2),
	('NBL_B-stoneMine', 6, 1),
	('NBL_B-storehouse', 5, 3),
	('NBL_B-storehouse', 6, 2),
	('NBL_B-storehouse', 7, 2),
	('NBL_B-storehouse', 8, 2),
	('NBL_B-wall', 1, 5),
	('NBL_B-wall', 2, 4),
	('NBL_B-wall', 9, 2),
	('NBL_U-archer', 1, 5),
	('NBL_U-archer', 4, 7),
	('NBL_U-archer', 11, 1),
	('NBL_U-catapult', 1, 15),
	('NBL_U-catapult', 4, 13),
	('NBL_U-catapult', 7, 12),
	('NBL_U-extra_unit', 1, 24),
	('NBL_U-extra_unit', 2, 30),
	('NBL_U-extra_unit', 3, 10),
	('NBL_U-extra_unit', 4, 20),
	('NBL_U-extra_unit', 8, 18),
	('NBL_U-extra_unit', 9, 15),
	('NBL_U-extra_unit', 11, 7),
	('NBL_U-extra_unit', 12, 3),
	('NBL_U-horseman', 1, 8),
	('NBL_U-horseman', 4, 9),
	('NBL_U-horseman', 11, 3),
	('NBL_U-medic', 1, 12),
	('NBL_U-medic', 4, 11),
	('NBL_U-medic', 8, 10),
	('NBL_U-pikeman', 3, 2),
	('NBL_U-pikeman', 4, 3),
	('NBL_U-spear_thrower', 1, 3),
	('NBL_U-spear_thrower', 4, 5),
	('NBL_U-spear_thrower', 9, 2),
	('NBL_U-swordsman', 4, 1);
/*!40000 ALTER TABLE `group_bldng` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.group_res
CREATE TABLE IF NOT EXISTS `group_res` (
  `group_name` varchar(20) NOT NULL,
  `id_resourse` int(11) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_group_id_resourse` (`group_name`,`id_resourse`),
  KEY `FK_group_res_resourses` (`id_resourse`),
  CONSTRAINT `FK_group_res_resourses` FOREIGN KEY (`id_resourse`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.group_res: ~114 rows (approximately)
DELETE FROM `group_res`;
/*!40000 ALTER TABLE `group_res` DISABLE KEYS */;
INSERT INTO `group_res` (`group_name`, `id_resourse`, `value`) VALUES
	('GPH_castle', 7, 3),
	('GPH_farm', 4, 15),
	('GPH_goldMine', 2, 15),
	('GPH_house', 1, 25),
	('GPH_house', 7, 1),
	('GPH_lumberjack', 5, 15),
	('GPH_monument', 1, 300),
	('GPH_monument', 2, 100),
	('GPH_monument', 3, 150),
	('GPH_monument', 4, 200),
	('GPH_monument', 5, 250),
	('GPH_monument', 6, 100),
	('GPH_stoneMine', 3, 15),
	('GPH_storehouse', 4, -2),
	('GPH_storehouse', 6, 1),
	('NPL_barrack', 1, 80),
	('NPL_barrack', 3, 30),
	('NPL_barrack', 5, 70),
	('NPL_castle', 1, 400),
	('NPL_castle', 3, 450),
	('NPL_castle', 5, 560),
	('NPL_castle', 6, 320),
	('NPL_castle', 7, 7),
	('NPL_farm', 1, 73),
	('NPL_farm', 4, 55),
	('NPL_farm', 5, 80),
	('NPL_goldMine', 1, 35),
	('NPL_goldMine', 3, 48),
	('NPL_goldMine', 4, 39),
	('NPL_goldMine', 5, 60),
	('NPL_house', 1, 30),
	('NPL_house', 3, 27),
	('NPL_house', 4, 30),
	('NPL_house', 5, 50),
	('NPL_lumberjack', 1, 60),
	('NPL_lumberjack', 5, 70),
	('NPL_market', 1, 180),
	('NPL_market', 2, 30),
	('NPL_market', 3, 50),
	('NPL_market', 4, 75),
	('NPL_market', 5, 100),
	('NPL_market', 7, 15),
	('NPL_monument', 1, 1278),
	('NPL_monument', 2, 467),
	('NPL_monument', 3, 1789),
	('NPL_monument', 4, 1143),
	('NPL_monument', 5, 2350),
	('NPL_monument', 6, 1086),
	('NPL_monument', 7, 47),
	('NPL_palace', 1, 550),
	('NPL_palace', 2, 498),
	('NPL_palace', 3, 545),
	('NPL_palace', 4, 532),
	('NPL_palace', 5, 570),
	('NPL_palace', 6, 467),
	('NPL_palace', 7, 19),
	('NPL_square', 1, 150),
	('NPL_square', 2, 80),
	('NPL_square', 3, 85),
	('NPL_square', 4, 90),
	('NPL_square', 5, 110),
	('NPL_square', 6, 75),
	('NPL_stoneMine', 1, 30),
	('NPL_stoneMine', 5, 60),
	('NPL_storehouse', 1, 120),
	('NPL_storehouse', 2, 50),
	('NPL_storehouse', 3, 65),
	('NPL_storehouse', 4, 90),
	('NPL_storehouse', 5, 70),
	('NPL_wall', 1, 100),
	('NPL_wall', 3, 70),
	('NPL_wall', 5, 50),
	('NPU_archer', 1, 57),
	('NPU_archer', 4, 70),
	('NPU_archer', 5, 6),
	('NPU_archer', 6, 2),
	('NPU_archer', 7, 1),
	('NPU_catapult', 1, 86),
	('NPU_catapult', 2, 35),
	('NPU_catapult', 3, 55),
	('NPU_catapult', 4, 130),
	('NPU_catapult', 5, 40),
	('NPU_catapult', 6, 15),
	('NPU_catapult', 7, 10),
	('NPU_extra_unit', 1, 99),
	('NPU_extra_unit', 2, 47),
	('NPU_extra_unit', 4, 69),
	('NPU_extra_unit', 6, 35),
	('NPU_extra_unit', 7, 1),
	('NPU_horseman', 1, 65),
	('NPU_horseman', 2, 5),
	('NPU_horseman', 4, 100),
	('NPU_horseman', 6, 25),
	('NPU_horseman', 7, 2),
	('NPU_medic', 1, 78),
	('NPU_medic', 2, 34),
	('NPU_medic', 4, 90),
	('NPU_medic', 6, 18),
	('NPU_medic', 7, 1),
	('NPU_pikeman', 1, 38),
	('NPU_pikeman', 4, 55),
	('NPU_pikeman', 5, 7),
	('NPU_pikeman', 6, 3),
	('NPU_pikeman', 7, 1),
	('NPU_spear_thrower', 1, 46),
	('NPU_spear_thrower', 4, 64),
	('NPU_spear_thrower', 5, 7),
	('NPU_spear_thrower', 6, 3),
	('NPU_spear_thrower', 7, 1),
	('NPU_swordsman', 1, 25),
	('NPU_swordsman', 4, 50),
	('NPU_swordsman', 5, 5),
	('NPU_swordsman', 6, 10),
	('NPU_swordsman', 7, 1);
/*!40000 ALTER TABLE `group_res` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.group_unit
CREATE TABLE IF NOT EXISTS `group_unit` (
  `id_group` int(11) NOT NULL,
  `id_unit` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_group_id_unit` (`id_group`,`id_unit`),
  KEY `FK_group_unit_unit` (`id_unit`),
  CONSTRAINT `FK_group_unit_unit` FOREIGN KEY (`id_unit`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.group_unit: ~0 rows (approximately)
DELETE FROM `group_unit`;
/*!40000 ALTER TABLE `group_unit` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_unit` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.kingdom
CREATE TABLE IF NOT EXISTS `kingdom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `coordinateX` int(11) DEFAULT NULL,
  `coordinateY` int(11) DEFAULT NULL,
  `pop_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `cordinateX_cordinateY` (`coordinateX`,`coordinateY`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.kingdom: ~2 rows (approximately)
DELETE FROM `kingdom`;
/*!40000 ALTER TABLE `kingdom` DISABLE KEYS */;
INSERT INTO `kingdom` (`id`, `name`, `coordinateX`, `coordinateY`, `pop_count`) VALUES
	(1, 'Kingdom of Test', 18, -51, 0),
	(2, 'Kingdom of User2', 14, 59, 0);
/*!40000 ALTER TABLE `kingdom` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.kngdm_bldng
CREATE TABLE IF NOT EXISTS `kngdm_bldng` (
  `id_kingdom` int(11) NOT NULL,
  `id_building` int(11) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `ready_on` datetime DEFAULT NULL,
  UNIQUE KEY `id_kingdom_id_building` (`id_kingdom`,`id_building`),
  KEY `FK_kngdm_bldng_building` (`id_building`),
  CONSTRAINT `FK_kngdm_bldng_building` FOREIGN KEY (`id_building`) REFERENCES `building` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_kngdm_bldng_kingdom` FOREIGN KEY (`id_kingdom`) REFERENCES `kingdom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.kngdm_bldng: ~26 rows (approximately)
DELETE FROM `kngdm_bldng`;
/*!40000 ALTER TABLE `kngdm_bldng` DISABLE KEYS */;
INSERT INTO `kngdm_bldng` (`id_kingdom`, `id_building`, `level`, `ready_on`) VALUES
	(1, 1, 1, NULL),
	(1, 2, 5, NULL),
	(1, 3, 0, NULL),
	(1, 4, 0, NULL),
	(1, 5, 2, NULL),
	(1, 6, 0, NULL),
	(1, 7, 0, NULL),
	(1, 8, 0, NULL),
	(1, 9, 0, NULL),
	(1, 10, 0, NULL),
	(1, 11, 0, NULL),
	(1, 12, 0, NULL),
	(1, 13, 0, NULL),
	(2, 1, 0, NULL),
	(2, 2, 0, NULL),
	(2, 3, 0, NULL),
	(2, 4, 0, NULL),
	(2, 5, 0, NULL),
	(2, 6, 0, NULL),
	(2, 7, 0, NULL),
	(2, 8, 0, NULL),
	(2, 9, 0, NULL),
	(2, 10, 0, NULL),
	(2, 11, 0, NULL),
	(2, 12, 0, NULL),
	(2, 13, 0, NULL);
/*!40000 ALTER TABLE `kngdm_bldng` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.kngdm_res
CREATE TABLE IF NOT EXISTS `kngdm_res` (
  `id_kingdom` int(11) NOT NULL,
  `id_resourse` int(11) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `value_double` double NOT NULL DEFAULT '0',
  UNIQUE KEY `id_kingdom_id_resourse` (`id_kingdom`,`id_resourse`),
  KEY `FK_kngdm_res_resourses` (`id_resourse`),
  CONSTRAINT `FK_kngdm_res_kingdom` FOREIGN KEY (`id_kingdom`) REFERENCES `kingdom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_kngdm_res_resourses` FOREIGN KEY (`id_resourse`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.kngdm_res: ~14 rows (approximately)
DELETE FROM `kngdm_res`;
/*!40000 ALTER TABLE `kngdm_res` DISABLE KEYS */;
INSERT INTO `kngdm_res` (`id_kingdom`, `id_resourse`, `value`, `value_double`) VALUES
	(1, 1, 160, 0.8333333290000011),
	(1, 2, 920, 0),
	(1, 3, 760, 0),
	(1, 4, 680, 0),
	(1, 5, 451, 0.5),
	(1, 6, 915, 0),
	(1, 7, 1, 0.03333332899999997),
	(2, 1, 1000, 0),
	(2, 2, 1000, 0),
	(2, 3, 1000, 0),
	(2, 4, 1000, 0),
	(2, 5, 1000, 0),
	(2, 6, 1000, 0),
	(2, 7, 0, 0);
/*!40000 ALTER TABLE `kngdm_res` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.kngdm_unit
CREATE TABLE IF NOT EXISTS `kngdm_unit` (
  `id_kingdom` int(11) NOT NULL,
  `id_unit` int(11) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `order_count` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `id_kingdom_id_unit` (`id_kingdom`,`id_unit`),
  KEY `FK_kngdm_unit_unit` (`id_unit`),
  CONSTRAINT `FK_kngdm_unit_kingdom` FOREIGN KEY (`id_kingdom`) REFERENCES `kingdom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_kngdm_unit_unit` FOREIGN KEY (`id_unit`) REFERENCES `unit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.kngdm_unit: ~16 rows (approximately)
DELETE FROM `kngdm_unit`;
/*!40000 ALTER TABLE `kngdm_unit` DISABLE KEYS */;
INSERT INTO `kngdm_unit` (`id_kingdom`, `id_unit`, `count`, `order_count`) VALUES
	(1, 1, 0, 0),
	(1, 2, 0, 0),
	(1, 3, 0, 0),
	(1, 4, 0, 0),
	(1, 5, 0, 0),
	(1, 6, 0, 0),
	(1, 7, 0, 0),
	(1, 8, 0, 0),
	(2, 1, 0, 0),
	(2, 2, 0, 0),
	(2, 3, 0, 0),
	(2, 4, 0, 0),
	(2, 5, 0, 0),
	(2, 6, 0, 0),
	(2, 7, 0, 0),
	(2, 8, 0, 0);
/*!40000 ALTER TABLE `kngdm_unit` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.resources
CREATE TABLE IF NOT EXISTS `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `default_value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.resources: ~7 rows (approximately)
DELETE FROM `resources`;
/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` (`id`, `name`, `default_value`) VALUES
	(1, 'coins', 1000),
	(2, 'food', 1000),
	(3, 'wood', 1000),
	(4, 'stone', 1000),
	(5, 'gold', 1000),
	(6, 'iron', 1000),
	(7, 'population', 0);
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;

-- Дъмп структура за procedure onlinegame.SetBuildingsInKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `SetBuildingsInKingdom`(
	IN `kingdom_id` INT,
	IN `building_name` VARCHAR(20),
	IN `level` INT


)
BEGIN
insert into kngdm_bldng (kngdm_bldng.id_kingdom, kngdm_bldng.id_building, kngdm_bldng.`level`) 
values (kingdom_id, (select building.id from building where building.name=building_name), `level`);
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.SetResourcesForKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `SetResourcesForKingdom`(
	IN `kingdom_id` INT,
	IN `resource` VARCHAR(20),
	IN `value` INT









)
BEGIN
	insert into kngdm_res (kngdm_res.id_kingdom, kngdm_res.id_resourse, kngdm_res.value) 
	values (kingdom_id, (select resources.id from resources where resources.name=resource), value);
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.SetUnitsInKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `SetUnitsInKingdom`(
	IN `kingdom_id` INT,
	IN `unit_name` VARCHAR(20),
	IN `count` INT




)
BEGIN
	insert into kngdm_unit (kngdm_unit.id_kingdom, kngdm_unit.id_unit, kngdm_unit.`count`) 
	values (kingdom_id, (select unit.id from unit where unit.name=unit_name), `count`);
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.StartAllEvents
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `StartAllEvents`()
    COMMENT 'ON - enable (start, run, power on) from global level all events'
BEGIN
	SET GLOBAL event_scheduler = ON;
END//
DELIMITER ;

-- Дъмп структура за procedure onlinegame.SubtractResForKingdom
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `SubtractResForKingdom`(
	IN `id` INT,
	IN `resource` VARCHAR(20),
	IN `value` INT



)
BEGIN
	update kngdm_res inner join resources 
	set kngdm_res.value = (value) 
	where kngdm_res.id_resourse=resources.id 
	and kngdm_res.id_kingdom=id 
	and resources.name=resource;
END//
DELIMITER ;

-- Дъмп структура за таблица onlinegame.unit
CREATE TABLE IF NOT EXISTS `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `attack` double NOT NULL,
  `live` double NOT NULL,
  `id_needBldngL` varchar(20) NOT NULL,
  `id_resNeedPU` varchar(20) NOT NULL,
  `timePU` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `FK_unit_group_res` (`id_resNeedPU`),
  KEY `FK_unit_group_bldng` (`id_needBldngL`),
  CONSTRAINT `FK_unit_group_bldng` FOREIGN KEY (`id_needBldngL`) REFERENCES `group_bldng` (`group_name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_unit_group_res` FOREIGN KEY (`id_resNeedPU`) REFERENCES `group_res` (`group_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.unit: ~8 rows (approximately)
DELETE FROM `unit`;
/*!40000 ALTER TABLE `unit` DISABLE KEYS */;
INSERT INTO `unit` (`id`, `name`, `attack`, `live`, `id_needBldngL`, `id_resNeedPU`, `timePU`) VALUES
	(1, 'swordsman', 15, 60, 'NBL_U-swordsman', 'NPU_swordsman', 25),
	(2, 'pikeman', 24, 75, 'NBL_U-pikeman', 'NPU_pikeman', 32),
	(3, 'spear_thrower', 30, 80, 'NBL_U-spear_thrower', 'NPU_spear_thrower', 40),
	(4, 'archer', 36, 87, 'NBL_U-archer', 'NPU_archer', 50),
	(5, 'horseman', 41, 99, 'NBL_U-horseman', 'NPU_horseman', 59),
	(6, 'medic', 5, 50, 'NBL_U-medic', 'NPU_medic', 67),
	(7, 'catapult', 68, 134, 'NBL_U-catapult', 'NPU_catapult', 80),
	(8, 'extra_unit', 92, 150, 'NBL_U-extra_unit', 'NPU_extra_unit', 90);
/*!40000 ALTER TABLE `unit` ENABLE KEYS */;

-- Дъмп структура за таблица onlinegame.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `id_kingdom` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `username_password` (`username`,`password`),
  KEY `FK_users_kingdom` (`id_kingdom`),
  CONSTRAINT `FK_users_kingdom` FOREIGN KEY (`id_kingdom`) REFERENCES `kingdom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дъмп данни за таблица onlinegame.users: ~2 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `nickname`, `username`, `password`, `email`, `id_kingdom`, `date_created`) VALUES
	(1, 'Test', 'tester', '$2y$13$6scM9F8cRZYYmVQdXNbumeLr6uhXOV/Hq6DwfIg5YWOpxoF7OUjgy', 'test@pass.is', 1, '2017-12-29 19:03:08'),
	(2, 'User2', 'User2', '$2y$13$gdaNeauR66onfWs2Vg7k4u8Cm9tKL2lluFqjAHI3X1PWYOJQYLl3i', 'test@pass.is', 2, '2018-01-04 10:39:49');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
