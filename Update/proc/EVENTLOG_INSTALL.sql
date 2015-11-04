CREATE TABLE `eventlog` (
  `logid` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK Stores ID of log Number',
  `time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() COMMENT 'timestamp of event',
  `user` TEXT NOT NULL COMMENT 'username associated with event',
  `event` TEXT NOT NULL COMMENT 'event that occured in plain text',
  `source` TEXT NULL COMMENT 'page or location that the event originated from',
  `result` VARCHAR(45) NULL COMMENT 'OPTIONAL: result of event (may be stored as separate event)',
  PRIMARY KEY (`logid`)  COMMENT '',
  UNIQUE INDEX `logId_UNIQUE` (`logid` ASC)  COMMENT '')
COMMENT = 'database storage of event logs, all fields required except result';