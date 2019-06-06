USE gearsport;
--NOT DONE yet
CREATE TABLE login_token(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    who varchar(13) NOT NULL,
    token varchar(512) NOT NULL,
    expire DATETIME NOT NULL,
    last_login DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT sid_unique UNIQUE (who),
    `fk_sid`
		FOREIGN KEY (who) REFERENCES account(sid)
		ON DELETE CASCADE
		ON UPDATE RESTRICT
);