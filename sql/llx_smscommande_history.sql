create table llx_smscommande_history (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_commande integer NOT NULL,
  fk_user integer NOT NULL,
  status_commande varchar(45) NOT NULL,
  num_envoi varchar(45) NOT NULL,
  content varchar(350) NOT NULL,
  date timestamp NOT NULL
)ENGINE=innodb;