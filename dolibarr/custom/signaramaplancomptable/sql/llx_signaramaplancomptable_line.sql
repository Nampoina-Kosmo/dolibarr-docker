create table llx_signaramaplancomptable_line
(
    rowid           integer AUTO_INCREMENT PRIMARY KEY,
    entity          integer NOT NULL DEFAULT 1,
    fk_pcg_version  varchar(32) NOT NULL,
    account_number  varchar(32) NOT NULL,
    label           varchar(255) NOT NULL,
    labelshort      varchar(255) DEFAULT NULL,
    parent_number   varchar(32) DEFAULT NULL,
    pcg_type        varchar(60) NOT NULL,
    to_delete       tinyint NOT NULL DEFAULT 0,
    datec           datetime,
    tms             timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fk_user_author  integer DEFAULT NULL
) ENGINE=innodb;
