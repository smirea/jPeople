
/** Tracking **/

CREATE TABLE IF NOT EXISTS `Tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `time` varchar(32) DEFAULT NULL,
  `ip` varchar(48) NOT NULL,
  `HTTP_REFERER` varchar(256) NOT NULL,
  `os` varchar(16) NOT NULL,
  `browser` varchar(16) NOT NULL,
  `browser_version` varchar(16) NOT NULL,
  `user_agent` varchar(512) NOT NULL,
  `failed` tinyint(1) NOT NULL DEFAULT '0',
  `error` text NOT NULL,
  `query` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`,`ip`,`failed`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

/** Feedback **/
CREATE TABLE IF NOT EXISTS `Feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` varchar(32) DEFAULT NULL,
  `name` varchar(128),
  `email` varchar(128),
  `type` varchar(32),
  `message` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

/** Course search **/

DROP TABLE IF EXISTS Professors;
CREATE TABLE Professors  (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `name` VARCHAR(255) NOT NULL ,
  UNIQUE KEY(name)
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS Schools;
CREATE TABLE Schools (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  UNIQUE KEY(name)
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS Majors;
CREATE TABLE Majors (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `name` VARCHAR( 255 ) NOT NULL ,
  `school_id` INT NOT NULL,
  UNIQUE KEY(name),
  CONSTRAINT fk_schools FOREIGN KEY(school_id) REFERENCES Schools(id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS Courses;
CREATE TABLE Courses (
  `id` INT NOT NULL PRIMARY KEY ,
  `number` VARCHAR(20) NOT NULL,
  `name` VARCHAR( 255 ) NOT NULL ,
  `link` MEDIUMTEXT NOT NULL,
  `description` MEDIUMTEXT NOT NULL,
  UNIQUE KEY(name)
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS Teaching;
CREATE TABLE Teaching (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cid` INT NOT NULL,
  `pid` INT NOT NULL,
  CONSTRAINT fk_courses FOREIGN KEY(cid) REFERENCES Courses (id)
  ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_professors FOREIGN KEY(pid) REFERENCES Professors (id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = MYISAM ;

DROP TABLE IF EXISTS Structure;
CREATE TABLE Structure (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cid` INT NOT NULL,
  `mid` INT NOT NULL,
  CONSTRAINT fk_courses FOREIGN KEY(cid) REFERENCES Courses (id)
  ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_majors FOREIGN KEY(mid) REFERENCES Majors (id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = MYISAM;

DROP TABLE IF EXISTS Appointments;
CREATE TABLE Appointments (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cid` INT NOT NULL,
  `date` VARCHAR(128) NOT NULL,
  `start` VARCHAR(128) NOT NULL,
  `end` VARCHAR(128) NOT NULL,
  `room` VARCHAR(128) NOT NULL,
  CONSTRAINT fk_course FOREIGN KEY(cid) REFERENCES Courses(id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = MYISAM;

/*
DROP TABLE Comments;
CREATE TABLE IF NOT EXISTS Comments (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `course_id` INT NOT NULL ,
  `rating` INT NOT NULL ,
  `comment` MEDIUMTEXT NOT NULL ,
  `pInteractiv` INT UNSIGNED NOT NULL ,
  `pSlides` INT UNSIGNED NOT NULL ,
  `pEvaluation` INT UNSIGNED NOT NULL ,
  `cInteresting` INT UNSIGNED NOT NULL ,
  `cBreadth` INT UNSIGNED NOT NULL ,
  `cDepth` INT UNSIGNED NOT NULL,
  CONSTRAINT fk_courses FOREIGN KEY(course_id) REFERENCES Courses(id)
  ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = MYISAM ;
*/
