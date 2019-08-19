CREATE DATABASE IF NOT EXISTS gearsport CHARACTER SET = 'utf8' COLLATE = 'utf8_bin';
CREATE USER 'gearsport'@'localhost' IDENTIFIED BY 'Z2VhcnNwb3J0';
GRANT select,insert,update,delete ON gearsport.* TO 'gearsport'@'localhost';
SET character_set_server = 'utf8';

use gearsport;

CREATE TABLE account_uni(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uni VARCHAR(7) NOT NULL,
    email VARCHAR(255) NOT NULL,
    owner_fname VARCHAR(255),
    owner_lname VARCHAR(255),
    uni_full_name VARCHAR(100) NOT NULL,
    uni_pwd VARCHAR(255) NOT NULL,
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY(uni)
);

CREATE TABLE account(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sid VARCHAR(13) NOT NULL,
    uni VARCHAR(7) NOT NULL,
    fname VARCHAR(128) NOT NULL,
    lname VARCHAR(128) NOT NULL,
    email VARCHAR(255),
    gender ENUM('Male','Female'),
    pwd VARCHAR(255) NOT NULL,
    img_url text,
    details JSON,
    CHECK (JSON_VALID(details)),
    UNIQUE KEY (sid),
    FOREIGN KEY (uni) REFERENCES account_uni(uni)
);

CREATE TABLE sport(
    id VARCHAR(4) NOT NULL PRIMARY KEY,
    sport_name VARCHAR(255) NOT NULL,
    sport_type VARCHAR(255) NOT NULL,
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
    FOREIGN KEY (fk_account_id) REFERENCES account(id),
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
    uni VARCHAR(4) NOT NULL,
    email VARCHAR(255) NOT NULL,
    fullname VARCHAR(255) NOT NULL,
    owner_fname VARCHAR(255) NOT NULL,
    owner_lname VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY(uni)
)

