/**
 * Author:  Jaume Ramos
 * 
 */

ALTER TABLE users
    ADD className VARCHAR(15) NOT NULL AFTER password_hash,
    ADD email VARCHAR(30) NOT NULL AFTER className,
    ADD emailValid BOOLEAN DEFAULT FALSE AFTER email,
    ADD verification_token VARCHAR(64) NULL AFTER emailValid,
    ADD verification_expires DATETIME NULL AFTER verification_token;


ALTER TABLE bitz
    ADD className VARCHAR(15) NOT NULL default "SMIX2" AFTER text;


ALTER TABLE bitz
    MODIFY className VARCHAR(15) NOT NULL;