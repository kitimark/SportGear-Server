--ALLINONE INIT
DROP DATABASE gearsport;
CREATE DATABASE gearsport;
DROP USER 'gearsport'@'localhost';
CREATE USER 'gearsport'@'localhost' IDENTIFIED BY 'Z2VhcnNwb3J0';
GRANT select,insert,update,delete ON gearsport.* TO 'gearsport'@'localhost';

use gearsport

CREATE TABLE account_uni(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uni VARCHAR(7) NOT NULL,
    email VARCHAR(255) NOT NULL,
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
    email VARCHAR(255) NOT NULL,
    pwd VARCHAR(255) NOT NULL,
    img_url text,
    details JSON,
    CHECK (JSON_VALID(details)),
    UNIQUE KEY (email),
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

LOAD DATA LOCAL INFILE  'sport.csv'
INTO TABLE sport
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS;


