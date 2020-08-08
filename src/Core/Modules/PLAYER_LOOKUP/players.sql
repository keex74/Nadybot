CREATE TABLE IF NOT EXISTS players(
	`charid`        INT NOT NULL,
	`firstname`     VARCHAR(30) NOT NULL DEFAULT '',
	`name`          VARCHAR(20) NOT NULL,
	`lastname`      VARCHAR(30) NOT NULL DEFAULT '',
	`level`         SMALLINT DEFAULT NULL,
	`breed`         VARCHAR(20) NOT NULL DEFAULT '',
	`gender`        VARCHAR(20) NOT NULL DEFAULT '',
	`faction`       VARCHAR(20) NOT NULL DEFAULT '',
	`profession`    VARCHAR(20) NOT NULL DEFAULT '',
	`prof_title`    VARCHAR(20) NOT NULL DEFAULT '',
	`ai_rank`       VARCHAR(20) NOT NULL DEFAULT '',
	`ai_level`      SMALLINT DEFAULT NULL,
	`guild_id`      INT DEFAULT NULL,
	`guild`         VARCHAR(255) NOT NULL DEFAULT '',
	`guild_rank`    VARCHAR(20) NOT NULL DEFAULT '',
	`guild_rank_id` SMALLINT DEFAULT NULL,
	`dimension`     SMALLINT,
	`head_id`       INT DEFAULT NULL,
	`pvp_rating`    SMALLINT DEFAULT NULL,
	`pvp_title`     VARCHAR (20) DEFAULT NULL,
	`source`        VARCHAR (50) NOT NULL DEFAULT '',
	`last_update`   INT     
);
CREATE INDEX IF NOT EXISTS players_name ON players(name);
CREATE INDEX IF NOT EXISTS players_charid ON players(charid);