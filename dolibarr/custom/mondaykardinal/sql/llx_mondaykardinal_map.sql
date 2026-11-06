-- Correspondance entre un document Dolibarr (devis / facture) et l'élément monday.com associé
CREATE TABLE llx_mondaykardinal_map (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer DEFAULT 1 NOT NULL,
  doc_type varchar(16) NOT NULL,
  doc_id integer NOT NULL,
  monday_item_id varchar(32) NOT NULL,
  hash varchar(40) NOT NULL,
  date_sync datetime NOT NULL
) ENGINE=innodb;
