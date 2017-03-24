#
# Table structure for table 'tx_myredirects_domain_model_redirect'
#
CREATE TABLE tx_myredirects_domain_model_redirect (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0',
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    editlock tinyint(4) DEFAULT '0' NOT NULL,
    start_time int(11) unsigned DEFAULT '0' NOT NULL,
    end_time int(11) unsigned DEFAULT '0' NOT NULL,

    url_hash varchar(40) DEFAULT '',
    url text NOT NULL,
    destination text NOT NULL,
    last_referrer text NOT NULL,
    counter int(11) DEFAULT '0' NOT NULL,
    http_response int(11) DEFAULT '301' NOT NULL,
    domain int(11) DEFAULT '0' NOT NULL,
    backend_note text,
    active tinyint(4) DEFAULT '1' NOT NULL,
    last_hit int(11) DEFAULT '0' NOT NULL,
    last_checked int(11) DEFAULT '0' NOT NULL,
    inactive_reason text,

    PRIMARY KEY (uid),
    UNIQUE KEY active (url_hash,domain),
    INDEX query (url_hash,domain,start_time,end_time)
);
