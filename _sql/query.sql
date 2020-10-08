-- Rodrigo Dias Javornik
-- 26/03/2020
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name varchar(150),
    linkedin_image_url varchar(300),
    linkedin_access_token varchar(1000),
    linkedin_user_id varchar(100),
    linkedin_token_expires_in DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT null,
PRIMARY KEY (id)
) ENGINE = InnoDB;

-- Rodrigo Dias Javornik
-- 26/03/2020
CREATE TABLE states (
    id INT(11) NOT NULL,
    name varchar(45),
PRIMARY KEY (id)
)  ENGINE = InnoDB

-- Rodrigo Dias Javornik
-- 26/03/2020
CREATE TABLE cities (
    id INT(11) NOT NULL,
    id_state INT(11) NOT NULL,
    name varchar(65),
    CONSTRAINT fk_cities_id_states FOREIGN KEY (id_state) REFERENCES states (id),
PRIMARY KEY (id)
)  ENGINE = InnoDB

-- Rodrigo Dias Javornik
-- 30/03/2020
CREATE TABLE occupation_areas (
    id INT(11) NOT NULL,
    name varchar(65),
PRIMARY KEY (id)
)  ENGINE = InnoDB

INSERT INTO occupation_areas (id, name) VALUES (1,"Customer Success");
INSERT INTO occupation_areas (id, name) VALUES (2,"Desenvolvimento");
INSERT INTO occupation_areas (id, name) VALUES (3,"Design");
INSERT INTO occupation_areas (id, name) VALUES (4,"Marketing");
INSERT INTO occupation_areas (id, name) VALUES (5,"Pré-vendas");
INSERT INTO occupation_areas (id, name) VALUES (6,"UX Design");
INSERT INTO occupation_areas (id, name) VALUES (7,"Vendas");

-- Rodrigo Dias Javornik
-- 26/03/2020
CREATE TABLE resumes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_user INT NOT NULL,
    name varchar(150),
    email varchar(150),
    id_occupation_area INT,
    id_city INT,
    remote_work boolean,
    phone varchar(15),
    skills varchar(500),
    linkedin_url varchar(150),
    linkedin_image_url varchar(300),
    description TEXT COLLATE "utf8mb4_general_ci",
    status boolean DEFAULT false,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT null,
    CONSTRAINT fk_resumes_id_user FOREIGN KEY (id_user) REFERENCES users (id),
    CONSTRAINT fk_resumes_id_city FOREIGN KEY (id_city) REFERENCES cities (id),
    CONSTRAINT fk_resumes_id_occupation_area FOREIGN KEY (id_occupation_area) REFERENCES occupation_areas (id),
PRIMARY KEY (id)
) ENGINE = InnoDB 

-- Rodrigo Dias Javornik
-- 02/04/2020
INSERT INTO occupation_areas (id, name) VALUES (8,"Finanças");
INSERT INTO occupation_areas (id, name) VALUES (9,"Recursos Humanos");


-- Rodrigo Dias Javornik
-- 22/04/2020
CREATE TABLE jobs_companies (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name varchar(150),
    logo_image longblob DEFAULT NULL,
    jobs_link varchar(200),
    description varchar(100),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME DEFAULT null,
PRIMARY KEY (id)
) ENGINE = InnoDB 