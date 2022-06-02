UPDATE cryptocurrency SET price_usd = 0 WHERE price_usd IS NULL;
UPDATE cryptocurrency SET mcap_usd = 0 WHERE mcap_usd IS NULL;
UPDATE position SET description = '' WHERE description IS NULL;
UPDATE strategy_farming SET description = '' WHERE description IS NULL;
UPDATE strategy_lp SET description = '' WHERE description IS NULL;

ALTER TABLE deposit CHANGE exchange_id exchange_id INT(11) NOT NULL, CHANGE type_id type_id INT(11) NOT NULL, CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE loan CHANGE dapp_id dapp_id INT(11) NOT NULL, CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE nft CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE position CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE project_monitoring CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE strategy_dca CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE strategy_farming CHANGE user_id owner_id INT(11) NOT NULL;
ALTER TABLE strategy_lp CHANGE user_id owner_id INT(11) NOT NULL;
