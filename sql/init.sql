CREATE DATABASE IF NOT EXISTS gearsport CHARACTER SET = 'utf8' COLLATE = 'utf8_bin';
CREATE USER 'gearsport'@'localhost' IDENTIFIED BY 'Z2VhcnNwb3J0';
GRANT select,insert,update,delete ON gearsport.* TO 'gearsport'@'localhost';
SET character_set_server = 'utf8';

use gearsport;

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE account_uni(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	uni VARCHAR(25) NOT NULL,
	uni_full_name VARCHAR(100) NOT NULL,
    UNIQUE KEY(uni)
);

CREATE TABLE account_role(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_type VARCHAR(1) NOT NULL,
    role_name VARCHAR(255) NOT NULL,
    role_des VARCHAR(255),
    UNIQUE KEY(role_type)
);

INSERT INTO account_role(role_type,role_name) VALUES ("A","กกบ"),
("B","นักกีฬา/ผู้เข้าร่วม"),
("C","เฮดเกัยร์"),
("D","ผู้เข้าประกวดดาวเดือน"),
("E","ผู้ดูแลดาวเดือน"),
("F","คณาจารย์/เจ้าหน้าที่"),
("G","สตาฟ"),
("U","มหาวิทยาลัย");

CREATE TABLE account_staff(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fk_account INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    pwd VARCHAR(255) NOT NULL,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fk_account) REFERENCES account(id),
    UNIQUE KEY(username)
);

CREATE TABLE account(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sid VARCHAR(25),
    uni VARCHAR(7) NOT NULL,
    fname VARCHAR(128) NOT NULL,
    lname VARCHAR(128) NOT NULL,
    type_role VARCHAR(1) NOT NULL,
    email VARCHAR(255),
    gender ENUM('Male','Female'),
    img_url text,
    details JSON,
    CHECK (JSON_VALID(details)),
    UNIQUE KEY (sid,uni),
    FOREIGN KEY (uni) REFERENCES account_uni(uni),
    FOREIGN KEY (type_role) REFERENCES account_role(role_type)
);

CREATE TABLE sport(
    id VARCHAR(4) NOT NULL PRIMARY KEY,
    sport_name VARCHAR(255) NOT NULL,
    sport_type VARCHAR(255) NOT NULL,
    sport_name_th VARCHAR(255),
    sport_type_th VARCHAR(255),
    each_team INT,
    teams INT,
    gender ENUM("Male","Female","Male&Female"),
    UNIQUE KEY(sport_name,sport_type)
);

CREATE TABLE sport_team(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(255) NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    uni VARCHAR(7) NOT NULL,
    ts TIMESTAMP NOT NULL,

    FOREIGN KEY (fk_sport_id) REFERENCES sport(id)
);

CREATE TABLE sport_player(
    fk_team_id INT NOT NULL,
    fk_account_id INT NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    FOREIGN KEY (fk_team_id) REFERENCES sport_team(id),
    FOREIGN KEY (fk_account_id) REFERENCES account(id) ON DELETE CASCADE,
    FOREIGN KEY (fk_sport_id) REFERENCES sport(id),
    PRIMARY KEY(fk_team_id,fk_account_id,fk_sport_id)
);


INSERT INTO `sport` (`id`, `sport_name`, `sport_type`, `each_team`, `teams`) VALUES
('1001', 'badminton', 'singles men', 1, 1),
('1002', 'badminton', 'singles women', 1, 1),
('1003', 'badminton', 'doubles men', 2, 1),
('1004', 'badminton', 'doubles women', 2, 1),
('1005', 'badminton', 'doubles mixed', 2, 1),
('1101', 'basketball', 'men', 12, 1),
('1102', 'basketball', 'women', 12, 1),
('1201', 'board game', 'a-math single', 1, 2),
('1202', 'board game', 'a-math double', 2, 1),
('1203', 'board game', 'crossword single', 1, 2),
('1204', 'board game', 'crossword double', 2, 1),
('1205', 'board game', 'thai chess', 1, 2),
('1301', 'e-sport', '5 vs 5', 6, 2),
('1401', 'football', 'men', 27, 1),
('1402', 'footsal', 'men', 18, 1),
('1501', 'petong', 'singles men', 1, 1),
('1502', 'petong', 'singles women', 1, 1),
('1503', 'petong', 'doubles men', 2, 1),
('1504', 'petong', 'doubles women', 2, 1),
('1505', 'petong', 'doubles mixed', 2, 1),
('1601', 'rugby', 'men', 25, 1),
('1701', 'athletics', '100M relay men', 1, 2),
('1702', 'athletics', '100M relay women', 1, 2),
('1703', 'athletics', '200M relay men', 1, 2),
('1704', 'athletics', '200M relay women', 1, 2),
('1705', 'athletics', '400M relay men', 1, 2),
('1706', 'athletics', '400M relay women', 1, 2),
('1707', 'athletics', '1X400M relay men', 4, 1),
('1708', 'athletics', '1X400M relay women', 4, 1),
('1709', 'athletics', '4X400M relay men', 4, 1),
('1710', 'athletics', '4X400M relay women', 4, 1),
('1801', 'softball', 'men', 16, 1),
('1901', 'tabletennis', 'singles men', 1, 2),
('1902', 'tabletennis', 'singles women', 1, 2),
('1903', 'tabletennis', 'doubles men', 3, 1),
('1904', 'tabletennis', 'doubles women', 3, 1),
('2001', 'sepak takraw', 'men', 3, 1),
('2002', 'sepak takraw', 'women', 3, 1),
('2101', 'tennis', 'singles men', 1, 1),
('2102', 'tennis', 'singles women', 1, 1),
('2103', 'tennis', 'doubles men', 2, 1),
('2104', 'tennis', 'doubles women', 2, 1),
('2201', 'volleyball', 'men', 12, 1),
('2202', 'volleyball', 'women', 12, 1),
('2301', 'swimming', '200M individual medley men', 1, 2),
('2302', 'swimming', '200M individual medley women', 1, 2),
('2303', 'swimming', '50M freestyle men', 1, 2),
('2304', 'swimming', '50M freestyle women', 1, 2),
('2305', 'swimming', '100M freestyle men', 1, 2),
('2306', 'swimming', '100M freestyle women', 1, 2),
('2307', 'swimming', '200M freestyle men', 1, 2),
('2308', 'swimming', '200M freestyle women', 1, 2),
('2309', 'swimming', '50M butterfly men', 1, 2),
('2310', 'swimming', '50M butterfly women', 1, 2),
('2311', 'swimming', '100M butterfly men', 1, 2),
('2312', 'swimming', '100M butterfly women', 1, 2),
('2313', 'swimming', '200M butterfly men', 1, 2),
('2314', 'swimming', '200M butterfly women', 1, 2),
('2315', 'swimming', '50M breaststroke men', 1, 2),
('2316', 'swimming', '50M breaststroke women', 1, 2),
('2317', 'swimming', '100M breaststroke men', 1, 2),
('2318', 'swimming', '100M breaststroke women', 1, 2),
('2319', 'swimming', '200M breaststroke men', 1, 2),
('2320', 'swimming', '200M breaststroke women', 1, 2),
('2321', 'swimming', '50M backstroke men', 1, 2),
('2322', 'swimming', '50M backstroke women', 1, 2),
('2323', 'swimming', '100M backstroke men', 1, 2),
('2324', 'swimming', '100M backstroke women', 1, 2),
('2325', 'swimming', '200M backstroke men', 1, 2),
('2326', 'swimming', '200M backstroke women', 1, 2),
('2327', 'swimming', '4X50M freestyle relay men', 4, 2),
('2328', 'swimming', '4X50M freestyle relay women', 4, 2),
('2329', 'swimming', '4X50M individual medley relay men', 4, 2),
('2330', 'swimming', '4X50M individual medley relay women', 4, 2),
('2331', 'swimming', '4X50M freestyle relay mixed', 4, 2),
('2332', 'swimming', '4X50M individual medley relay mixed', 4, 2);


CREATE TABLE sport_match(
    id INT NOT NULL AUTO_INCREMENT,
    match_timestamp TIMESTAMP NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    fk_team1 INT NOT NULL,
    fk_team2 INT NOT NULL,
    FOREIGN KEY (fk_sport_id) REFERENCES sport(id),
    FOREIGN KEY (fk_team1) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team2) REFERENCES sport_team(id),
    PRIMARY KEY(id)
);

CREATE TABLE sport_match_8v8(
    id INT NOT NULL AUTO_INCREMENT,
    match_timestamp TIMESTAMP NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    fk_team1 INT,
    fk_team2 INT,
    fk_team3 INT,
    fk_team4 INT,
    fk_team5 INT,
    fk_team6 INT,
    fk_team7 INT,
    fk_team8 INT,
    FOREIGN KEY (fk_sport_id) REFERENCES sport(id),
    FOREIGN KEY (fk_team1) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team2) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team3) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team4) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team5) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team6) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team7) REFERENCES sport_team(id),
    FOREIGN KEY (fk_team8) REFERENCES sport_team(id),
    PRIMARY KEY (id)
);

CREATE TABLE mail_info(
    id INT NOT NULL AUTO_INCREMENT,
    uni VARCHAR(7) NOT NULL,
    email VARCHAR(255) NULL,
    fullname VARCHAR(255) NULL,
    owner_fname VARCHAR(255) NULL,
    owner_lname VARCHAR(255) NULL,
    temp_username VARCHAR(50) DEFAULT 'NULL' null,
    temp_pwd VARCHAR(1024) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY(uni)
)

INSERT INTO gearsport.mail_info (uni, email, fullname, owner_fname, owner_lname, temp_username, temp_pwd) VALUES 
('CMU', 'geargame30@eng.cmu.ac.th', 'Chaingmai University', 'Savakorn', 'Wongponkanan', 'CMU752', '4ea71b91'),
('KMUTT', null, null, null, null, 'KMUTT910', '9394b329'),
('NU', null, null, null, null, 'NU244', '67122c29'),
('MJU', null, null, null, null, 'MJU858', '68cba824'),
('TU', null, null, null, null, 'TU048', '0931a84c'),
('SPU', null, null, null, null, 'SPU603', '8690c382'),
('ABAC', null, null, null, null, 'ABAC791', '0c61792a'),
('CU', null, null, null, null, 'CU577', '67ac0327'),
('KMUTNB', null, null, null, null, 'KMUTNB771', 'e19f9f0c'),
('MUT', null, null, null, null, 'MUT823', '1f2967e3'),
('RU', null, null, null, null, 'RU496', '1fa45a97'),
('UTCC', null, null, null, null, 'UTCC187', 'c161711f'),
('PIT', null, null, null, null, 'PIT642', 'c4e05039'),
('RMUTT', null, null, null, null, 'RMUTT974', '2bcb3a18'),
('DPU', null, null, null, null, 'DPU755', '3e5ab88a'),
('BU', null, null, null, null, 'BU424', '658a82e6'),
('RMUTP', null, null, null, null, 'RMUTP438', '702cc506'),
('KKU', null, null, null, null, 'KKU942', '120a0e1d'),
('KMITL', null, null, null, null, 'KMITL880', '7192c674'),
('SUT', null, null, null, null, 'SUT402', '32abfc61'),
('UBU', null, null, null, null, 'UBU110', 'd1824a14'),
('MSU', null, null, null, null, 'MSU118', '0b0172be'),
('BUU', null, null, null, null, 'BUU291', '7be0d06e'),
('KBU', null, null, null, null, 'KBU860', 'aff756f0'),
('PSU', null, null, null, null, 'PSU267', '87b58543'),
('KU', null, null, null, null, 'KU947', '8795900c'),
('MU', null, null, null, null, 'MU349', 'f7054d75'),
('SU', null, null, null, null, 'SU728', '234ce573'),
('WU', null, null, null, null, 'WU409', '96d78976'),
('RSU', null, null, null, null, 'RSU603', 'ae4af20b'),
('SWU', null, null, null, null, 'SWU054', '340a26cd'),
('SIITTU', null, null, null, null, 'SIITTU919', 'bb25b684'),
('SJU', null, null, null, null, 'SJU506', '7c29852e'),
('TRU', null, null, null, null, 'TRU525', 'a1d0b5ba'),
('NEU', null, null, null, null, 'NEU997', '472087ce'),
('RTU', null, null, null, null, 'RTU138', '578c14ca'),
('VU', null, null, null, null, 'VU329', '496255ad'),
('SIAM', null, null, null, null, 'SIAM651', 'ed8bbdba'),
('SAU', null, null, null, null, 'SAU100', '79b7581c'),
('RMUTK', null, null, null, null, 'RMUTK100', '4dffa0e8'),
('RMUTSB', null, null, null, null, 'RMUTSB189', '33f7be51'),
('RMUTI', null, null, null, null, 'RMUTI513', '9b95e1c9'),
('RMUTR', null, null, null, null, 'RMUTR982', 'c782104d'),
('RMUTTO', null, null, null, null, 'RMUTTO772', '1062603e'),
('NCU', null, null, null, null, 'NCU526', '84cd853b'),
('RMUTL', null, null, null, null, 'RMUTL054', 'a01db9a4'),
('RMUTSV', null, null, null, null, 'RMUTSV385', '89234c8f'),
('KKCRMUTI', null, null, null, null, 'KKCRMUTI757', '6c4e331a'),
('KUKPS', null, null, null, null, 'KUKPS686', '4d1801b9'),
('KUCSC', null, null, null, null, 'KUCSC965', '057085f4'),
('KUSRC', null, null, null, null, 'KUSRC351', '9e50eed9'),
('PNU', null, null, null, null, 'PNU442', '5a8ebc2f'),
('BTU', null, null, null, null, 'BTU749', '8281fada'),
('PTU', null, null, null, null, 'PTU937', '9b38beff'),
('TNI', null, null, null, null, 'TNI623', '51d12dfe'),
('PIM', null, null, null, null, 'PIM537', '8d3e8416'),
('CRMA', null, null, null, null, 'CRMA055', 'e4ed6c98'),
('RTNA', null, null, null, null, 'RTNA277', '1266d506'),
('NKRAFA', null, null, null, null, 'NKRAFA967', 'a399c285'),
('PCT', null, null, null, null, 'PCT500', 'd0f84e80'),
('UP', null, null, null, null, 'UP613', '80ef6da7'),
('RMU', null, null, null, null, 'RMU998', '80affa59'),
('NPU', null, null, null, null, 'NPU732', '40a90402'),
('PBRU', null, null, null, null, 'PBRU309', '42c93cd5'),
('test', 'tunesudro@hotmail.com', null, null, null, 'test', '1234');