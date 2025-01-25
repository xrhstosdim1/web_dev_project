/*
DONT FORGET:
GRANT ALL PRIVILEGES ON project_web.* TO 'pma'@'localhost';
FLUSH PRIVILEGES;
*/



drop database if exists project_web;
create database project_web;
use project_web;

Create table User(
	email VARCHAR(50) NOT NULL PRIMARY KEY,
	password VARCHAR(20) DEFAULT "password",
	name varchar(30),
    surname varchar(30)
    );
    
Create Table Students(
	am integer (8) NOT NULL PRIMARY KEY,
	email varchar(50) NOT NULL,
	father_name varchar(20),
	mob VARCHAR(10),
	tel VARCHAR(10),
	street varchar (100),
	str_number varchar (4),
	city varchar (100),
	postcode integer(5),
	CONSTRAINT email1 foreign key (email) REFERENCES User(email) on delete cascade on update cascade,
	unique (email)
 );
 
create table professor(
	email varchar(50) NOT NULL,
	topic varchar(100),
	dept varchar(100),
	uni varchar(100),
	land_tel integer(10),
	mob_tel integer(10),
	constraint email2 foreign key (email) references User(email) on delete cascade on update cascade,
	unique (email)
);

create table secretary(
	email varchar(50) NOT NULL UNIQUE,
	mob_tel integer(10),
	land_tel integer(10),
	constraint email3 foreign key (email) references User(email) on delete cascade on update cascade
);

CREATE TABLE diplwmatiki_ka8igita (
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    email VARCHAR(50) NOT NULL,
    creation_date DATETIME,
    topic VARCHAR(100),
	summary VARCHAR(500),
    status ENUM("diathesimi", "pros_anathesi", "pros_egrisi", "energi", "allagi_foititi", "oloklirwmeni", "exetasi", "vathmologisi", "akurwmeni") DEFAULT "diathesimi",
	start_date DATETIME,
    exam_date DATETIME,
	file_name VARCHAR(100),
	completion_date DATETIME,
    CONSTRAINT mailka8igita FOREIGN KEY (email) REFERENCES professor(email) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE diplwmatiki_foitita (
    am_foititi INT(8) NOT NULL,
    id_diplwmatikis INT,
	date_selected DATETIME DEFAULT now(),
    prof2 VARCHAR(50),
    prof3 VARCHAR(50),
	nemertes_link VARCHAR(100),
	date_file_uploaded DATETIME,
    file_name VARCHAR(100),
	status ENUM("energi", "akurwmeni") DEFAULT 'energi' NOT NULL,
	CONSTRAINT id1 FOREIGN KEY (id_diplwmatikis) REFERENCES diplwmatiki_ka8igita(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT proff2 FOREIGN KEY (prof2) REFERENCES professor(email) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT proff3 FOREIGN KEY (prof3) REFERENCES professor(email) ON DELETE CASCADE ON UPDATE CASCADE,
	PRIMARY KEY (am_foititi, id_diplwmatikis)
);


CREATE TABLE diplwmatiki_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_diplwmatikis INT NOT NULL,
    link VARCHAR(2083) NOT NULL,
    CONSTRAINT fk_diplwmatikis FOREIGN KEY (id_diplwmatikis) REFERENCES diplwmatiki_foitita (id_diplwmatikis) ON DELETE CASCADE
);

CREATE TABLE announcments (
	id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    am_foititi INT(8) NOT NULL,
    id_diplwmatikis INT,
	exam_date DATETIME DEFAULT now(),
	_location VARCHAR(100),
	ann_body VARCHAR(1000),
	status ENUM("private", "public") DEFAULT "private",
	CONSTRAINT iddddd FOREIGN KEY (id_diplwmatikis) REFERENCES diplwmatiki_ka8igita(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT ammmmm FOREIGN KEY (am_foititi) REFERENCES Students(am) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE gramateia(
	id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	aitwn_email VARCHAR(50) NOT NULL,
	am_foititi INT(8) NOT NULL,
	id_diplwmatikis INT,
	ari8mos_protokolou INT,
	final_grade  DECIMAL(4,2),
	date_requested DATETIME DEFAULT now(),
	nemertes_link VARCHAR(100),
	prof1 VARCHAR(50) NOT NULL,
	prof2 VARCHAR(50) NOT NULL,
	prof3 VARCHAR(50) NOT NULL,
	comment VARCHAR(150),
	date_of_response DATETIME,
	aithsh_gia ENUM("pros_egrisi_oloklirwmenh","pros_egrisi_energi", "pros_egrisi_akurwshs", "pros_egrisi_allagis_f") DEFAULT "pros_egrisi_energi",
	apanthsh ENUM("pending","accepted", "denied") NOT NULL DEFAULT 'pending',
	CONSTRAINT iddd2 FOREIGN KEY (id_diplwmatikis) REFERENCES diplwmatiki_ka8igita(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT profff1 FOREIGN KEY (prof1) REFERENCES diplwmatiki_ka8igita(email) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT profff2 FOREIGN KEY (prof2) REFERENCES diplwmatiki_foitita(prof2) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT profff3 FOREIGN KEY (prof3) REFERENCES diplwmatiki_foitita(prof3) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE vathmologio (
    id_diplwmatikis INT PRIMARY KEY NOT NULL,
    prof1 VARCHAR(50) NOT NULL,
    prof1_grade_crit_1 DECIMAL(4,2),
    prof1_grade_crit_2 DECIMAL(4,2),
    prof1_grade_crit_3 DECIMAL(4,2),
    prof1_grade_crit_4 DECIMAL(4,2),
    prof1_final_grade DECIMAL(4,2) AS (prof1_grade_crit_1 * 0.6 + prof1_grade_crit_2 * 0.15 + prof1_grade_crit_3 * 0.1 + prof1_grade_crit_4 * 0.15),
    prof2 VARCHAR(50) NOT NULL,
    prof2_grade_crit_1 DECIMAL(4,2),
    prof2_grade_crit_2 DECIMAL(4,2),
    prof2_grade_crit_3 DECIMAL(4,2),
    prof2_grade_crit_4 DECIMAL(4,2),
    prof2_final_grade DECIMAL(4,2) AS (prof2_grade_crit_1 * 0.6 + prof2_grade_crit_2 * 0.15 + prof2_grade_crit_3 * 0.1 + prof2_grade_crit_4 * 0.15),
    prof3 VARCHAR(50) NOT NULL,
    prof3_grade_crit_1 DECIMAL(4,2),
    prof3_grade_crit_2 DECIMAL(4,2),
    prof3_grade_crit_3 DECIMAL(4,2),
    prof3_grade_crit_4 DECIMAL(4,2),
    prof3_final_grade DECIMAL(4,2) AS (prof3_grade_crit_1 * 0.6 + prof3_grade_crit_2 * 0.15 + prof3_grade_crit_3 * 0.1 + prof3_grade_crit_4 * 0.15),
	final_grade DECIMAL(4,2),
    status ENUM("prosva8mologisi", "egkekrimeni", "aporif8ike", "anamoni_gia_egkrisi", "anamoni") DEFAULT "anamoni",
    CONSTRAINT fk_vathmologio_diplwmatikis FOREIGN KEY (id_diplwmatikis) REFERENCES diplwmatiki_ka8igita (id) ON DELETE CASCADE ON UPDATE CASCADE
);


CREATE TABLE epivlepontes_requests(
	id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	id_diplomatikis INT NOT NULL,
	student_am INT NOT NULL,
	prof_email VARCHAR(50) NOT NULL,
	date_requested DATETIME DEFAULT now(),
	date_answered DATETIME,
	status ENUM("pending", "accepted", "rejected", "canceled") DEFAULT "pending",
	CONSTRAINT iddd4 FOREIGN KEY (id_diplomatikis) REFERENCES diplwmatiki_foitita(id_diplwmatikis) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT am FOREIGN KEY (student_am) REFERENCES Students(am) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT email4 FOREIGN KEY (prof_email) REFERENCES professor(email) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE professor_comments_on_theses(
	id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	id_diplomatikis INT NOT NULL,
	prof_email VARCHAR(50) NOT NULL,
	comment VARCHAR(300),
	date_commented DATETIME DEFAULT now(),
	CONSTRAINT iddd5 FOREIGN KEY (id_diplomatikis) REFERENCES diplwmatiki_ka8igita(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT email5 FOREIGN KEY (prof_email) REFERENCES professor(email) ON DELETE CASCADE ON UPDATE CASCADE
);