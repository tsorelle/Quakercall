
DROP TABLE IF EXISTS qcall_endorsements;
CREATE TABLE `qcall_endorsements` (
                                      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                      submissionDate DATE,		-- "Submission Date",
                                      NAME VARCHAR(128),          -- "Your Name",
                                      email VARCHAR(128),         -- "Your E-mail Address",
                                      address VARCHAR(1024),      -- Address,
                                      comments MEDIUMTEXT,        -- Comments,
                                      endorserType VARCHAR(128),   -- "I am a...",
                                      howFound VARCHAR(128),       -- "How did you find us?"

                                      submissionId VARCHAR(128),
                                      ipAddress VARCHAR(34),

                                      `createdby` VARCHAR(64)  NOT NULL DEFAULT 'system',
                                      `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,
                                      `changedby` VARCHAR(64)  DEFAULT NULL,
                                      `changedon` DATETIME DEFAULT NULL,
                                      `active` TINYINT(1) NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS qcall_groupendorsements;
CREATE TABLE `qcall_groupendorsements` (
                                           `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                           contactId INT UNSIGNED,
                                           submissionDate DATE, 			-- "Submission Date",
                                           organizationType VARCHAR(128),	-- "Type of organization",
                                           NAME VARCHAR(128), -- "Organization name:"
                                           address VARCHAR(1024), -- Address
                                           contactName VARCHAR(128), -- "Authorized Contact"
                                           phone VARCHAR(128),	-- "Phone Number"
                                           email VARCHAR(128),	-- Email
                                           attachment VARCHAR(128),-- "Attach Copy of Authorizing Minute, if required."

                                           submissionId VARCHAR(128),
                                           ipAddress VARCHAR(34),


                                           `createdby` VARCHAR(64)  NOT NULL DEFAULT 'system',
                                           `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,
                                           `changedby` VARCHAR(64)  DEFAULT NULL,
                                           `changedon` DATETIME DEFAULT NULL,
                                           `active` TINYINT(1) NOT NULL DEFAULT '1',
                                           PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS qcall_registrations;
CREATE TABLE `qcall_registrations` (
                                       `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                       contactId INT UNSIGNED,
                                       meetingId INT UNSIGNED,
                                       submissionDate DATE,
--	firstName varchar(128),
--	lastName varchar(128),
--	email varchar(128),
--	phone varchar(128),
                                       affiliation VARCHAR(128),
                                       ORGANIZATION VARCHAR(128),

                                       submissionId VARCHAR(128),
                                       ipAddress VARCHAR(34),

                                       `createdby` VARCHAR(64)  NOT NULL DEFAULT 'system',
                                       `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,
                                       `changedby` VARCHAR(64)  DEFAULT NULL,
                                       `changedon` DATETIME DEFAULT NULL,
                                       `active` TINYINT(1) NOT NULL DEFAULT '1',
                                       PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS qcall_contacts;
CREATE TABLE `qcall_contacts` (
                                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                  subscribed TINYINT(1) NOT NULL DEFAULT 0,
                                  firstName	 VARCHAR(128) NOT NULL DEFAULT '',  -- first_name
                                  lastName	 VARCHAR(128) NOT NULL DEFAULT '', -- last_name
                                  email		 VARCHAR(128) NOT NULL, 		   -- email
                                  phone		 VARCHAR(128) , -- phone,
                                  ORGANIZATION VARCHAR(128) , -- organization
                                  jobTitle	 VARCHAR(128) , -- job_title
                                  address1	 VARCHAR(128) , -- address1
                                  address2	 VARCHAR(128) , -- address2
                                  city		 VARCHAR(128) , -- city
                                  state		 VARCHAR(128) , -- state/province
                                  country		 VARCHAR(128) , -- country
                                  postalcode	 VARCHAR(128) , -- postal code
                                  fullname	 VARCHAR(256) NOT NULL DEFAULT '',
                                  sortcode	 VARCHAR(256) NOT NULL DEFAULT '',
                                  SOURCE       VARCHAR(128),
                                  postedDate		DATETIME,

                                  `createdby` VARCHAR(64)  NOT NULL DEFAULT 'system',
                                  `createdon` DATETIME DEFAULT CURRENT_TIMESTAMP,
                                  `changedby` VARCHAR(64)  DEFAULT NULL,
                                  `changedon` DATETIME DEFAULT NULL,
                                  `active` TINYINT(1) NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`)
);
