use gearsport

CREATE TABLE sport(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sport_name VARCHAR(80) NOT NULL,
    sex enum('M','F'),
    num_player INT
    details JSON,
    CHECK (JSON_VALID(details))
);
/*
0 - null
1 - M
2 - F
*/

--badminton
INSERT INTO sport(sport_name,sex,num,details) VALUES ('badminton',1,1,'{"type":"single men"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('badminton',2,1,'{"type":"single women"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('badminton',1,1,'{"type":"doubles men"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('badminton',2,2,'{"type":"doubles women"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('badminton',0,2,'{"type":"doubles mixed"}');


--basketball
INSERT INTO sport(sport_name,sex,num,details) VALUES ('basketball',1,12,'{"type":"team men"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('basketball',2,12,'{"type":"team women"}');

--boardgame
INSERT INTO sport(sport_name,sex,num,details) VALUES ('boardgame',0,1,'{"type":"a-math single"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('boardgame',0,2,'{"type":"a-math doubles"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('boardgame',0,1,'{"type":"crossword single"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('boardgame',0,2,'{"type":"crossword doubles"}');
INSERT INTO sport(sport_name,sex,num,details) VALUES ('boardgame',0,1,'{"type":"thai chess"}');

--e-sport
INSERT INTO sport(sport_name,sex,num,details) VALUES ('e-sport',0,6,'{"type":"5 vs 5"}');

--football
INSERT INTO sport(sport_name,sex,num,details) VALUES ('football',1,27,'{"type":"team men"}');

--footsol
INSERT INTO sport(sport_name,sex,num,details) VALUES ('footsol',1,18,'{"type":"team men"}');

