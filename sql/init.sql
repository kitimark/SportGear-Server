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

INSERT INTO `sport` VALUES ('1701','athletics','100M relay men'),('1702','athletics','100M relay women'),('1707','athletics','1X400M relay men'),('1708','athletics','1X400M relay women'),('1703','athletics','200M relay men'),('1704','athletics','200M relay women'),('1705','athletics','400M relay men'),('1706','athletics','400M relay women'),('1709','athletics','4X400M relay men'),('1710','athletics','4X400M relay women'),('1003','badminton','doubles men'),('1005','badminton','doubles mixed'),('1004','badminton','doubles women'),('1001','badminton','singles men'),('1002','badminton','singles women'),('1101','basketball','men'),('1102','basketball','women'),('1202','board game','a-math double'),('1201','board game','a-math single'),('1204','board game','crossword double'),('1203','board game','crossword single'),('1205','board game','thai chess'),('1301','e-sport','5 vs 5'),('1401','football','men'),('1402','footsol','men'),('1503','petong','doubles men'),('1505','petong','doubles mixed'),('1504','petong','doubles women'),('1501','petong','singles men'),('1502','petong','singles women'),('1601','rugby','men'),('1801','softball','men'),('2323','swimming','100M backstroke men'),('2324','swimming','100M backstroke women'),('2317','swimming','100M breaststroke men'),('2318','swimming','100M breaststroke women'),('2311','swimming','100M butterfly men'),('2312','swimming','100M butterfly women'),('2305','swimming','100M freestyle men'),('2306','swimming','100M freestyle women'),('2325','swimming','200M backstroke men'),('2326','swimming','200M backstroke women'),('2319','swimming','200M breaststroke men'),('2320','swimming','200M breaststroke women'),('2313','swimming','200M butterfly men'),('2314','swimming','200M butterfly women'),('2307','swimming','200M freestyle men'),('2308','swimming','200M freestyle women'),('2301','swimming','200M individual medley men'),('2302','swimming','200M individual medley women'),('2327','swimming','4X50M freestyle relay men'),('2331','swimming','4X50M freestyle relay mixed'),('2328','swimming','4X50M freestyle relay women'),('2329','swimming','4X50M individual medley relay men'),('2332','swimming','4X50M individual medley relay mixed'),('2330','swimming','4X50M individual medley relay women'),('2321','swimming','50M backstroke men'),('2322','swimming','50M backstroke women'),('2315','swimming','50M breaststroke men'),('2316','swimming','50M breaststroke women'),('2309','swimming','50M butterfly men'),('2310','swimming','50M butterfly women'),('2303','swimming','50M freestyle men'),('2304','swimming','50M freestyle women'),('1903','tabletennis','doubles men'),('1904','tabletennis','doubles women'),('1901','tabletennis','singles men'),('1902','tabletennis','singles women'),('2001','takro','men'),('2002','takro','women'),('2103','tennis','doubles men'),('2104','tennis','doubles women'),('2101','tennis','singles men'),('2102','tennis','singles women'),('2201','volleyball','men'),('2202','volleyball','women');
