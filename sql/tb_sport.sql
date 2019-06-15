use gearsport
CREATE TABLE sport(
    id VARCHAR(4) NOT NULL PRIMARY KEY,
    sport_name VARCHAR(255) NOT NULL,
    sport_type VARCHAR(255) NOT NULL
);

CREATE TABLE sport_team(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(255) NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    uni VARCHAR(7) NOT NULL,
    FOREIGN KEY (fk_sport_id) REFERENCES sport(id)
);

CREATE TABLE sport_player(
    fk_team_id INT NOT NULL,
    fk_account_id INT NOT NULL,
    fk_sport_id VARCHAR(4) NOT NULL,
    FOREIGN KEY (fk_team_id) REFERENCES sport_team(id),
    FOREIGN KEY (fk_account_id) REFERENCES account(id),
    FOREIGN KEY (fk_sport_id) REFERENCES sport(id)
);