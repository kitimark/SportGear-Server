use gearsport

CREATE TABLE sport(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sport_name VARCHAR(80) NOT NULL,
    sex enum('M','F'),
    num_player INT,
    details JSON,
    CHECK (JSON_VALID(details))
);
/*
0 - null
1 - M
2 - F
*/

--badminton
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('badminton',1,1,'{"type":"singles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('badminton',2,1,'{"type":"singles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('badminton',1,1,'{"type":"doubles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('badminton',2,2,'{"type":"doubles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('badminton',NULL,2,'{"type":"doubles mixed"}');


--basketball
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('basketball',1,12,'{"type":"team men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('basketball',2,12,'{"type":"team women"}');

--boardgame
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('boardgame',NULL,1,'{"type":"a-math singles"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('boardgame',NULL,2,'{"type":"a-math doubles"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('boardgame',NULL,1,'{"type":"crossword singles"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('boardgame',NULL,2,'{"type":"crossword doubles"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('boardgame',NULL,1,'{"type":"thai chess"}');

--e-sport
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('e-sport',NULL,6,'{"type":"5 vs 5"}');

--football
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('football',1,27,'{"type":"team men"}');

--footsol
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('footsol',1,18,'{"type":"team men"}');

--petong
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('petong',1,1,'{"type":"singles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('petong',2,1,'{"type":"singles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('petong',1,2,'{"type":"doubles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('petong',2,2,'{"type":"doubles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('petong',NULL,2,'{"type":"doubles mixed"}');

--rugby
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('rugby',1,25,'{"type":"team men"}');

--athletics
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',1,1,'{"type":"100M relay men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',2,1,'{"type":"100M relay women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',1,1,'{"type":"200M relay men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',2,1,'{"type":"200M relay women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',1,1,'{"type":"400M relay men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',2,1,'{"type":"400M relay women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',1,4,'{"type":"1X400M relay men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',2,4,'{"type":"1X400M relay women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',1,4,'{"type":"4X400M relay men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('athletics',2,4,'{"type":"4X400M relay women"}');

--softball
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('softball',1,16,'{"type":"team men"}');

--tabletennis
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tabletennis',1,1,'{"type":"singles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tabletennis',2,1,'{"type":"singles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tabletennis',1,3,'{"type":"doubles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tabletennis',2,3,'{"type":"doubles women"}');

--takro
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('takro',1,3,'{"type":"team men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('takro',2,3,'{"type":"team women"}');

--tennis
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tennis',1,1,'{"type":"singles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tennis',2,1,'{"type":"singles women"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tennis',1,2,'{"type":"doubles men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('tennis',2,2,'{"type":"doubles women"}');

--volleyball
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('volleyball',1,12,'{"type":"team men"}');
INSERT INTO sport(sport_name,sex,num_player,details) VALUES ('volleyball',2,12,'{"type":"team men"}');



