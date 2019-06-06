-- require mariadb >= 10.3
CREATE DATABASE gearsport;
CREATE USER 'gearsport'@'localhost' IDENTIFIED BY 'Z2VhcnNwb3J0';
GRANT select,insert,update,delete ON gearsport.* TO 'gearsport'@'localhost';