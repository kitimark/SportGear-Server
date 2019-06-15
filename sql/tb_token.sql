USE gearsport;
CREATE TABLE login_token(
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fk_sid varchar(13) NOT NULL,
    token varchar(512) NOT NULL,
    expire DATETIME NOT NULL,
    last_login DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fk_sid) REFERENCES account(sid),
    UNIQUE KEY (fk_sid)
);