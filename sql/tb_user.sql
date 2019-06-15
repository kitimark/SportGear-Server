use gearsport;

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