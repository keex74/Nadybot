DROP TABLE IF EXISTS dynadb;
CREATE TABLE dynadb (
	`playfield_id` INT NOT NULL,
	`mob` VARCHAR(200),
	`minQl` INT,
	`maxQl` INT,
	`cX` INT,
	`cY` INT
);
INSERT INTO dynadb (`playfield_id`, `mob`, `minQl`, `maxQl`, `cX`, `cY`) VALUES
(585, 'Blubbags', 20, 24, 1741, 1938),
(585, 'Fleas', 30, 35, 1422, 2620),
(585, 'Blubbags', 31, 35, 1713, 1719),
(585, 'Blubbags', 31, 36, 1674, 1719),
(585, 'Fleas', 32, 36, 1582, 2778),
(585, 'Fleas', 32, 35, 2101, 2338),
(585, 'Leets', 36, 40, 1340, 1899),
(585, 'Helperbots', 48, 54, 981, 379),
(585, 'Hyenas', 48, 54, 1620, 2062),
(585, 'Fleas', 49, 52, 1861, 2778),
(585, 'Mechdog', 54, 59, 1102, 1699),
(585, 'Aquaans', 54, 60, 1423, 696),
(585, 'Aquaans', 54, 60, 1181, 939),
(585, 'Hyenas', 54, 60, 2122, 980),
(585, 'Helperbots', 61, 66, 860, 2339),
(585, 'Hyenas', 66, 72, 821, 978),
(585, 'Helperbots', 66, 71, 382, 2300),
(585, 'Claw-mutants', 74, 77, 662, 819),
(585, 'Anuns', 74, 77, 1305, 1534),
(585, 'Rhinomen', 75, 77, 469, 350),
(585, 'Aquaans', 75, 78, 2060, 699),
(655, 'Leets', 36, 40, 1380, 2700),
(655, 'Hammer', 47, 48, 1696, 2743),
(655, 'Scorpiods', 49, 53, 1217, 2104),
(655, 'Shade', 62, 63, 3017, 2228),
(655, 'Hyenas', 66, 72, 456, 583),
(655, 'Snakes', 72, 76, 1177, 1418),
(655, 'Hammers', 85, 89, 4176, 422),
(655, 'Spiders', 90, 94, 2217, 1502),
(655, 'Spiders', 91, 94, 2936, 2022),
(655, 'Shades', 98, 101, 4515, 1148),
(655, 'Snakes', 102, 107, 3015, 302),
(605, 'Pit-Lizards', 111, 114, 1451, 669),
(605, 'Enigmas', 132, 136, 1660, 3098),
(605, 'Ottous', 133, 138, 2119, 2979),
(605, 'Ninjadroids', 150, 156, 380, 2259),
(605, 'Bileswarm', 158, 162, 1702, 2578),
(605, 'Ninjadroids', 162, 166, 2500, 2139),
(605, 'Ninjadroids', 162, 167, 2196, 2511),
(605, 'Enigmas', 164, 168, 580, 1820),
(605, 'Quake-Lizards', 168, 173, 901, 740),
(605, 'Snakes', 170, 174, 460, 778),
(605, 'Quake-Lizards', 170, 173, 820, 1942),
(605, 'Nanofreaks', 180, 185, 1660, 1778),
(605, 'Snake', 180, 186, 1700, 1377),
(605, 'Snake', 187, 192, 1742, 1071),
(605, 'Snakes', 188, 192, 1501, 2111),
(605, 'Pit-Lizards', 192, 198, 1482, 641),
(605, 'Snakes', 192, 195, 2380, 1458),
(605, 'Pit-Lizards', 195, 198, 822, 1341),
(605, 'Nanofreaks', 198, 202, 2380, 738),
(605, 'Swampfiends', 204, 208, 1701, 538),
(605, 'Swampfiends', 204, 210, 2299, 1260),
(590, 'Cyborgs', 115, 119, 1382, 2737),
(590, 'Enigma', 121, 124, 1702, 2498),
(590, 'Spiders', 128, 130, 862, 2377),
(590, 'Spiders', 134, 138, 461, 2378),
(590, 'Anun', 139, 142, 421, 1060),
(590, 'Spiders', 140, 144, 941, 1859),
(590, 'Spiders', 144, 150, 502, 1698),
(590, 'Spiders', 145, 150, 3380, 1378),
(590, 'Spider', 147, 148, 2342, 339),
(590, 'Snake', 152, 156, 3220, 1617),
(590, 'Enigmas', 156, 162, 2622, 2818),
(590, 'Enigmas', 156, 159, 2381, 2339),
(590, 'Anun', 156, 159, 3580, 2939),
(590, 'Snakes', 157, 161, 380, 418),
(590, 'Enigmas', 162, 167, 1340, 858),
(590, 'Enigmas', 168, 172, 941, 1540),
(590, 'Nanofreaks', 200, 204, 2660, 298),
(590, 'Snakes', 212, 213, 3541, 1417),
(590, 'Snakes', 240, 245, 3500, 1898),
(590, 'Snakes', 271, 275, 3496, 1904),
(565, 'Rhinomen', 13, 18, 1340, 2340),
(565, 'Rhinomen', 15, 18, 1263, 2539),
(565, 'Eye-Mutants', 19, 22, 2181, 2739),
(565, 'Greckos', 24, 29, 341, 1739),
(565, 'Rhinomen', 24, 29, 736, 2389),
(565, 'Brontos', 24, 27, 2322, 1059),
(565, 'Buzzsaws', 24, 26, 2820, 699),
(565, 'Fleas', 31, 36, 3262, 2899),
(565, 'Leets', 36, 38, 742, 2899),
(565, 'Salamander', 36, 42, 2740, 2459),
(565, 'Leets', 37, 42, 460, 2379),
(565, 'Rhinomen', 37, 41, 1461, 2138),
(565, 'Rhinomen', 37, 39, 1700, 1580),
(565, 'Snakes', 38, 42, 2100, 2217),
(565, 'Scorpiods', 38, 40, 2622, 299),
(565, 'Leets', 39, 41, 1221, 2900),
(565, 'Scorpiods', 39, 43, 2342, 659),
(565, 'Minibulls', 48, 52, 3422, 2099),
(565, 'Rhinomen', 49, 54, 3488, 1296),
(565, 'Rhinomen', 58, 60, 2822, 1218),
(565, 'Rhinomen', 60, 62, 2662, 1459),
(950, 'Aquaan', 4, 4, 189, 179),
(952, 'Salamander', 4, 4, 186, 182),
(954, 'Gladiatorbot', 4, 4, 53, 104),
(716, 'Blubbag', 4, 5, 536, 2181),
(716, 'Reets', 5, 7, 655, 2543),
(716, 'Malles', 6, 7, 376, 1498),
(716, 'Salamanders', 7, 8, 376, 3182),
(716, 'Bio-mutants', 8, 9, 377, 1307),
(716, 'Blubbags', 10, 10, 537, 2862),
(716, 'Leets', 10, 12, 375, 819),
(716, 'Shades', 11, 12, 291, 1057),
(716, 'Tac-mutants', 14, 16, 855, 3259),
(716, 'Reets', 14, 15, 297, 623),
(716, 'Aquaans', 15, 17, 536, 662),
(716, 'Hyenas', 16, 17, 657, 1348),
(716, 'Rhinomen', 19, 19, 616, 341),
(716, 'Fleas', 21, 23, 537, 1018),
(570, 'Mantises', 102, 106, 1159, 1145),
(570, 'Cyborgs', 103, 108, 1937, 1382),
(570, 'Cyborgs', 106, 108, 1952, 1390),
(570, 'Anuns', 127, 129, 3061, 920),
(570, 'Mantises', 162, 164, 1460, 859),
(570, 'Mantises', 165, 167, 2662, 2300),
(570, 'Anuns', 174, 179, 380, 900),
(570, 'Anuns', 175, 179, 380, 499),
(570, 'Anuns', 180, 186, 462, 3139),
(570, 'Anuns', 180, 183, 1341, 3060),
(570, 'Anuns', 180, 186, 1261, 2858),
(570, 'Anuns', 181, 186, 701, 2459),
(570, 'Saltworms', 186, 188, 420, 1501),
(570, 'Mantis', 195, 200, 1858, 2802),
(570, 'Mantises', 198, 204, 2460, 2659),
(570, 'Mantises', 198, 203, 2741, 2458),
(570, 'Anuns', 198, 203, 3516, 2020),
(570, 'Mantis', 199, 204, 2981, 2939),
(570, 'Mantises', 199, 204, 3101, 3260),
(570, 'Mantises', 200, 204, 2220, 3338),
(570, 'Daemon', 208, 209, 3942, 1779),
(570, 'Cyborgs', 229, 234, 3461, 2939),
(570, 'Cyborgs', 241, 245, 3475, 2978),
(570, 'Cyborgs', 250, 250, 3742, 2414),
(570, 'Cyborgs', 250, 250, 3080, 1928),
(600, 'Grecko', 20, 24, 1737, 2657),
(600, 'Greckos', 25, 27, 1021, 2739),
(600, 'Leet', 37, 39, 3342, 2859),
(600, 'Rhinomen', 55, 59, 1020, 299),
(600, 'Rhinomen', 60, 66, 1221, 339),
(600, 'Spiders', 60, 66, 1422, 1538),
(600, 'Blubbag', 62, 66, 1300, 2338),
(600, 'Blubbags', 62, 66, 3061, 2218),
(600, 'Manteze', 66, 67, 4021, 1780),
(600, 'Rhinomen', 70, 72, 1140, 979),
(600, 'Mantezes', 72, 77, 700, 2178),
(600, 'Mantezes', 72, 78, 582, 2539),
(600, 'Rhinomen', 72, 77, 1340, 899),
(600, 'Rhinomen', 72, 78, 1940, 1859),
(600, 'Breed-mutant', 72, 78, 2041, 395),
(600, 'Manteze', 73, 78, 4062, 1339),
(600, 'Breed-mutant', 77, 80, 2039, 418),
(600, 'Rhinomen', 78, 84, 1381, 539),
(600, 'Manteze', 82, 84, 4222, 378),
(600, 'Rhinomen', 96, 100, 1861, 1099),
(600, 'Bileswarms', 127, 130, 1700, 818),
(551, 'Biofreaks', 42, 47, 343, 1055),
(551, 'Blubbag', 42, 47, 1100, 2018),
(551, 'Rollerrats', 44, 48, 1742, 1219),
(551, 'Hyenas', 48, 53, 1580, 1139),
(551, 'Hyenas', 48, 54, 1421, 1138),
(551, 'Blubbag', 48, 54, 1700, 1739),
(551, 'Hyenas', 49, 53, 1379, 1380),
(551, 'Blubbag', 50, 54, 2300, 1338),
(551, 'Clawfingers', 51, 56, 1140, 1699),
(551, 'Blubbags', 54, 59, 2421, 1339),
(551, 'Spiders', 55, 60, 1980, 2578),
(551, 'Scorpiods', 56, 60, 540, 1459),
(551, 'Spiders', 60, 62, 622, 3058),
(551, 'Blubbags', 64, 66, 2340, 1498),
(551, 'Spiders', 66, 71, 1260, 2538),
(551, 'Spiders', 66, 72, 1340, 2497),
(551, 'Blubbags', 75, 78, 2340, 1418),
(551, 'Hammers', 84, 90, 1542, 3338),
(551, 'Hammers', 84, 89, 1700, 3377),
(551, 'Hammers', 85, 90, 982, 3298),
(750, 'Tac-mutants', 130, 135, 266, 3344),
(750, 'Medusaes', 130, 155, 1453, 623),
(750, 'Shades', 145, 159, 1968, 2128),
(750, 'Ninjadroid', 148, 160, 1390, 1732),
(6553, 'Cleaning Robot', 2, 2, 3544, 866),
(6553, 'Helperbot', 4, 4, 3516, 906),
(6553, 'Collector', 4, 4, 3507, 942),
(6553, 'Flea', 7, 7, 3423, 889),
(6553, 'Rollerrat *', 7, 7, 3425, 747),
(6553, 'Minibull *', 10, 10, 3770, 565),
(6553, 'Saltworm *', 13, 13, 3466, 628),
(4582, 'Snake', 2, 2, 999, 790),
(4582, 'Leet', 2, 2, 864, 724),
(4582, 'Salamander', 2, 2, 797, 753),
(4582, 'Malle', 3, 3, 601, 793),
(4582, 'Rollerrat', 4, 4, 636, 867),
(4582, 'Helperbot', 5, 5, 956, 1027),
(4582, 'Gladiatorbot', 5, 5, 1031, 1010);