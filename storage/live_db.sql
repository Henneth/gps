-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2018 at 02:14 AM
-- Server version: 5.6.38
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `gps_live_17`
--

-- --------------------------------------------------------

--
-- Table structure for table `athletes`
--

CREATE TABLE `athletes` (
  `athlete_id` int(11) NOT NULL,
  `bib_number` varchar(64) DEFAULT NULL,
  `first_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `zh_full_name` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible',
  `country_code` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `checkpoint`
--

CREATE TABLE `checkpoint` (
  `checkpoint_id` int(11) NOT NULL,
  `checkpoint_name` varchar(255) DEFAULT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `min_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `code` varchar(8) NOT NULL,
  `country` varchar(255) NOT NULL,
  `country_zh_hk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`code`, `country`, `country_zh_hk`) VALUES
('AFG', 'Afghanistan', '阿富汗'),
('ALB', 'Albania', '阿爾巴尼亞'),
('ALG', 'Algeria', '阿爾及利亞'),
('AND', 'Andorra', '安道爾'),
('ANG', 'Angola', '安哥拉'),
('ANT', 'Antigua and Barbuda', '安地卡及巴布達'),
('ASA', 'Argentina', '美屬薩摩亞'),
('ARG', 'Armenia', '阿根廷'),
('ARM', 'Aruba', '亞美尼亞'),
('ARU', 'American Samoa', '阿魯巴'),
('AUS', 'Australia', '澳大利亞'),
('AUT', 'Austria', '奧地利'),
('AZE', 'Azerbaijan', '阿塞拜疆'),
('BAH', 'Bahamas', '巴哈馬'),
('BAN', 'Bangladesh', '孟加拉國'),
('BAR', 'Barbados', '巴巴多斯'),
('BDI', 'Burundi', '布隆迪'),
('BEL', 'Belgium', '比利時'),
('BEN', 'Benin', '貝寧'),
('BER', 'Bermuda', '百慕大'),
('BHU', 'Bhutan', '不丹'),
('BIH', 'Bosnia and Herzegovina', '波斯尼亞和黑塞哥維那'),
('BIZ', 'Belize', '伯利茲'),
('BLR', 'Belarus', '白俄羅斯'),
('BOL', 'Bolivia', '玻利維亞'),
('BOT', 'Botswana', '博茨瓦納'),
('BRA', 'Brazil', '巴西'),
('BRN', 'Bahrain', '巴林'),
('BRU', 'Brunei', '文萊'),
('BUL', 'Bulgaria', '保加利亞'),
('BUR', 'Burkina Faso', '布吉納法索'),
('CAF', 'Central African Republic', '中非'),
('CAM', 'Cambodia', '柬埔寨'),
('CAN', 'Canada', '加拿大'),
('CAY', 'Cayman Islands', '開曼群島'),
('CGO', 'Congo', '剛果（布）'),
('CHA', 'Chad', '乍得'),
('CHI', 'Chile', '智利'),
('CHN', 'China', '中國'),
('CIV', 'Ivory Coast', '科特迪瓦'),
('CMR', 'Cameroon', '喀麥隆'),
('COD', 'Democratic Republic of the Congo', '剛果（金）'),
('COK', 'Cook Islands', '庫克群島'),
('COL', 'Colombia', '哥倫比亞'),
('COM', 'Comoros', '科摩羅'),
('CPV', 'Cape Verde', '佛得角'),
('CRC', 'Costa Rica', '哥斯達黎加'),
('CRO', 'Croatia', '克羅地亞'),
('CUB', 'Cuba', '古巴'),
('CYP', 'Cyprus', '賽普勒斯'),
('CZE', 'Czech Republic', '捷克'),
('DEN', 'Denmark', '丹麥'),
('DJI', 'Djibouti', '吉布提'),
('DMA', 'Dominica', '多米尼克'),
('DOM', 'Dominican Republic', '多米尼加'),
('ECU', 'Ecuador', '厄瓜多爾'),
('EGY', 'Egypt', '埃及'),
('ERI', 'Eritrea', '厄立特里亞'),
('ESA', 'El Salvador', '薩爾瓦多'),
('ESP', 'Spain', '西班牙'),
('EST', 'Estonia', '愛沙尼亞'),
('ETH', 'Ethiopia', '衣索比亞'),
('FIJ', 'Fiji', '斐濟'),
('FIN', 'Finland', '芬蘭'),
('FRA', 'France', '法國'),
('FSM', 'Federated States of Micronesia', '密克羅尼西亞聯邦'),
('GAB', 'Gabon', '加彭'),
('GAM', 'The Gambia', '岡比亞'),
('GBR', 'Great Britain', '英國'),
('GBS', 'Guinea-Bissau', '幾內亞比紹'),
('GEO', 'Georgia', '格魯吉亞'),
('GEQ', 'Equatorial Guinea', '赤道幾內亞'),
('GER', 'Germany', '德國'),
('GHA', 'Ghana', '加納'),
('GRE', 'Greece', '希臘'),
('GRN', 'Grenada', '格瑞那達'),
('GUA', 'Guatemala', '危地馬拉'),
('GUI', 'Guinea', '幾內亞'),
('GUM', 'Guam', '關島'),
('GUY', 'Guyana', '圭亞那'),
('HAI', 'Haiti', '海地'),
('HKG', 'Hong Kong', '中國香港'),
('HON', 'Honduras', '洪都拉斯'),
('HUN', 'Hungary', '匈牙利'),
('INA', 'Indonesia', '印尼'),
('IND', 'India', '印度'),
('IRI', 'Iran', '伊朗'),
('IRL', 'Ireland', '愛爾蘭'),
('IRQ', 'Iraq', '伊拉克'),
('ISL', 'Iceland', '冰島'),
('ISR', 'Israel', '以色列'),
('ISV', 'Virgin Islands', '美屬維爾京群島'),
('ITA', 'Italy', '義大利'),
('IVB', 'British Virgin Islands', '英屬維爾京群島'),
('JAM', 'Jamaica', '牙買加'),
('JOR', 'Jordan', '約旦'),
('JPN', 'Japan', '日本'),
('KAZ', 'Kazakhstan', '哈薩克斯坦'),
('KEN', 'Kenya', '肯尼亞'),
('KIR', 'Kyrgyzstan', '基里巴斯'),
('KGZ', 'Kiribati', '吉爾吉斯斯坦'),
('KOR', 'South Korea', '韓國'),
('KOS', 'Kosovo', '科索沃'),
('KSA', 'Saudi Arabia', '沙烏地阿拉伯'),
('KUW', 'Kuwait', '科威特'),
('LAO', 'Laos', '老撾人民民主共和國'),
('LAT', 'Latvia', '拉脫維亞'),
('LBA', 'Libya', '利比亞'),
('LBR', 'Lebanon', '利比里亞'),
('LCA', 'Liberia', '聖盧西亞'),
('LES', 'Saint Lucia', '賴索托'),
('LIB', 'Lesotho', '黎巴嫩'),
('LIE', 'Liechtenstein', '列支敦斯登'),
('LTU', 'Lithuania', '立陶宛'),
('LUX', 'Luxembourg', '盧森堡'),
('MAD', 'Madagascar', '馬達加斯加'),
('MAR', 'Morocco', '摩洛哥'),
('MAS', 'Malaysia', '馬來西亞'),
('MAW', 'Malawi', '馬拉維'),
('MDA', 'Moldova', '摩爾多瓦共和國'),
('MDV', 'Maldives', '馬爾地夫'),
('MEX', 'Mexico', '墨西哥'),
('MGL', 'Mongolia', '蒙古'),
('MHL', 'Marshall Islands', '馬紹爾群島'),
('MKD', 'Macedonia', '前南斯拉夫 馬其頓共和國'),
('MLI', 'Mali', '馬里'),
('MLT', 'Malta', '馬爾他'),
('MON', 'Montenegro', '摩納哥'),
('MNE', 'Monaco', '蒙特內哥羅'),
('MOZ', 'Mozambique', '莫桑比克'),
('MRI', 'Mauritius', '模里西斯'),
('MTN', 'Mauritania', '毛里塔尼亞'),
('MYA', 'Myanmar', '緬甸'),
('NAM', 'Namibia', '納米比亞'),
('NCA', 'Nicaragua', '尼加拉瓜'),
('NED', 'Netherlands', '荷蘭'),
('NEP', 'Nepal', '尼泊爾'),
('NGR', 'Nigeria', '奈及利亞'),
('NIG', 'Niger', '尼日爾'),
('NOR', 'Norway', '挪威'),
('NRU', 'Nauru', '瑙魯'),
('NZL', 'New Zealand', '新西蘭'),
('OMA', 'Oman', '阿曼'),
('PAK', 'Pakistan', '巴基斯坦'),
('PAN', 'Panama', '巴拿馬'),
('PAR', 'Paraguay', '巴拉圭'),
('PER', 'Peru', '秘魯'),
('PHI', 'Philippines', '菲律賓'),
('PLE', 'Palestine', '巴勒斯坦'),
('PLW', 'Palau', '帛琉'),
('PNG', 'Papua New Guinea', '巴布亞新幾內亞'),
('POL', 'Poland', '波蘭'),
('POR', 'Portugal', '葡萄牙'),
('PRK', 'North Korea', '朝鮮'),
('PUR', 'Puerto Rico', '波多黎各'),
('QAT', 'Qatar', '卡塔爾'),
('ROU', 'Romania', '羅馬尼亞'),
('RSA', 'South Africa', '南非'),
('RUS', 'Russia', '俄羅斯'),
('RWA', 'Rwanda', '盧旺達'),
('SAM', 'Samoa', '薩摩亞'),
('SEN', 'Senegal', '塞內加爾'),
('SEY', 'Seychelles', '塞舌爾'),
('SIN', 'Singapore', '新加坡'),
('SKN', 'Saint Kitts and Nevis', '聖基茨和尼維斯'),
('SLE', 'Sierra Leone', '塞拉利昂'),
('SLO', 'Slovenia', '斯洛維尼亞'),
('SMR', 'San Marino', '聖馬力諾'),
('SOL', 'Solomon Islands', '所羅門群島'),
('SOM', 'Somalia', '索馬利亞'),
('SRB', 'Serbia', '塞爾維亞'),
('SRI', 'Sri Lanka', '斯里蘭卡'),
('SSD', 'South Sudan', '南蘇丹'),
('STP', 'São Tomé and Príncipe', '聖多美和普林西比'),
('SUD', 'Sudan', '蘇丹'),
('SUI', 'Switzerland', '瑞士'),
('SUR', 'Suriname', '蘇里南'),
('SVK', 'Slovakia', '斯洛伐克'),
('SWE', 'Sweden', '瑞典'),
('SWZ', 'Swaziland', '斯威士蘭'),
('SYR', 'Syria', '敘利亞'),
('TAN', 'Tanzania', '坦桑尼亞'),
('TGA', 'Tonga', '湯加'),
('THA', 'Thailand', '泰國'),
('TJK', 'Tajikistan', '塔吉克斯坦'),
('TKM', 'Turkmenistan', '土庫曼斯坦'),
('TLS', 'East Timor', '東帝汶'),
('TOG', 'Togo', '多哥'),
('TPE', 'Taiwan', '中華台北'),
('TRI', 'Trinidad and Tobago', '千里達及托巴哥'),
('TUN', 'Tunisia', '突尼西亞'),
('TUR', 'Turkey', '土耳其'),
('TUV', 'Tuvalu', '圖瓦盧'),
('UAE', 'United Arab Emirates', '阿聯酋'),
('UGA', 'Uganda', '烏干達'),
('UKR', 'Ukraine', '烏克蘭'),
('URU', 'Uruguay', '烏拉圭'),
('USA', 'United States', '美國'),
('UZB', 'Uzbekistan', '烏茲別克斯坦'),
('VAN', 'Vanuatu', '瓦努阿圖'),
('VEN', 'Venezuela', '委內瑞拉'),
('VIE', 'Vietnam', '越南'),
('VIN', 'Saint Vincent and the Grenadines', '聖文森及格瑞那丁'),
('YEM', 'Yemen', '葉門'),
('ZAM', 'Zambia', '尚比亞'),
('ZIM', 'Zimbabwe', '辛巴威'),
('MAC', 'Macau', '中國澳門');

-- --------------------------------------------------------

--
-- Table structure for table `device_mapping`
--

CREATE TABLE `device_mapping` (
  `device_mapping_id` int(11) NOT NULL,
  `device_id` varchar(64) NOT NULL,
  `bib_number` varchar(64) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(128) NOT NULL,
  `event_type` enum('fixed route','shortest route','no route') NOT NULL,
  `live` tinyint(1) NOT NULL DEFAULT '0',
  `hide_others` tinyint(1) NOT NULL DEFAULT '0',
  `datetime_from` datetime NOT NULL,
  `datetime_to` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `invalid_data`
--

CREATE TABLE `invalid_data` (
  `id` int(11) NOT NULL,
  `device_id` varchar(32) NOT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `battery_level` varchar(64) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `map_point`
--

CREATE TABLE `map_point` (
  `point_order` int(11) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `distance_from_last_point` varchar(64) NOT NULL,
  `distance_from_start` varchar(64) NOT NULL,
  `is_checkpoint` tinyint(1) NOT NULL DEFAULT '0',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `checkpoint_name` varchar(255) DEFAULT NULL,
  `min_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `next_checkpoint`
--

CREATE TABLE `next_checkpoint` (
  `bib_number` varchar(64) NOT NULL,
  `checkpoint_id` int(11) NOT NULL,
  `accumulated_distance_since_last_ckpt` varchar(64) NOT NULL,
  `accumulated_time_since_last_ckpt` time NOT NULL,
  `distance_to_next_ckpt` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `bib_number` varchar(64) DEFAULT NULL,
  `first_name` varchar(128) DEFAULT NULL,
  `last_name` varchar(128) DEFAULT NULL,
  `zh_full_name` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '1',
  `country_code` varchar(128) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `country_zh_hk` varchar(255) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('visible','hidden') NOT NULL DEFAULT 'visible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `raw_data`
--

CREATE TABLE `raw_data` (
  `id` int(11) NOT NULL,
  `device_id` varchar(32) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `battery_level` varchar(64) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reached_checkpoint`
--

CREATE TABLE `reached_checkpoint` (
  `bib_number` varchar(64) NOT NULL,
  `checkpoint_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `elapsed_time_btwn_ckpts` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `valid_data`
--

CREATE TABLE `valid_data` (
  `id` int(11) NOT NULL,
  `device_id` varchar(32) NOT NULL,
  `latitude` varchar(255) DEFAULT NULL,
  `longitude` varchar(255) DEFAULT NULL,
  `distance` varchar(64) NOT NULL,
  `battery_level` varchar(64) NOT NULL,
  `datetime` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `athletes`
--
ALTER TABLE `athletes`
  ADD PRIMARY KEY (`athlete_id`),
  ADD UNIQUE KEY `bib_number` (`bib_number`);

--
-- Indexes for table `checkpoint`
--
ALTER TABLE `checkpoint`
  ADD PRIMARY KEY (`checkpoint_id`);

--
-- Indexes for table `device_mapping`
--
ALTER TABLE `device_mapping`
  ADD PRIMARY KEY (`device_mapping_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `invalid_data`
--
ALTER TABLE `invalid_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `map_point`
--
ALTER TABLE `map_point`
  ADD PRIMARY KEY (`point_order`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
  ADD UNIQUE KEY `bib_number` (`bib_number`);

--
-- Indexes for table `raw_data`
--
ALTER TABLE `raw_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `valid_data`
--
ALTER TABLE `valid_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `athletes`
--
ALTER TABLE `athletes`
  MODIFY `athlete_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkpoint`
--
ALTER TABLE `checkpoint`
  MODIFY `checkpoint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_mapping`
--
ALTER TABLE `device_mapping`
  MODIFY `device_mapping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invalid_data`
--
ALTER TABLE `invalid_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `map_point`
--
ALTER TABLE `map_point`
  MODIFY `point_order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_data`
--
ALTER TABLE `raw_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `valid_data`
--
ALTER TABLE `valid_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
