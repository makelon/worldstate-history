CREATE TABLE file_positions (
  path character varying NOT NULL PRIMARY KEY,
  last_pos integer NOT NULL
);

CREATE SEQUENCE items_item_id_seq;
CREATE TABLE items (
  item_id integer NOT NULL PRIMARY KEY DEFAULT nextval('items_item_id_seq'),
  item_name character varying NOT NULL UNIQUE,
  item_type character varying NOT NULL
);
CREATE INDEX items_item_search_idx ON items USING GIN (to_tsvector('simple', item_name || ' ' || item_type));
ALTER SEQUENCE items_item_id_seq OWNED BY items.item_id;

CREATE TABLE pc_alerts (
  alert_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  mission_type character varying NOT NULL
);
CREATE INDEX pc_alerts_time_end_idx ON pc_alerts(time_end);

CREATE TABLE pc_alert_items (
  alert_id character varying NOT NULL REFERENCES pc_alerts ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE pc_invasions (
  invasion_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX pc_invasions_time_end_idx ON pc_invasions(time_end);

CREATE TABLE pc_invasion_items (
  invasion_id character varying NOT NULL REFERENCES pc_invasions ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE pc_voidtraders (
  voidtrader_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX pc_voidtraders_time_end_idx ON pc_voidtraders(time_end);

CREATE TABLE pc_voidtrader_items (
  voidtrader_id character varying NOT NULL REFERENCES pc_voidtraders ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE ps4_alerts (
  alert_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  mission_type character varying NOT NULL
);
CREATE INDEX ps4_alerts_time_end_idx ON ps4_alerts(time_end);

CREATE TABLE ps4_alert_items (
  alert_id character varying NOT NULL REFERENCES ps4_alerts ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE ps4_invasions (
  invasion_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX ps4_invasions_time_end_idx ON ps4_invasions(time_end);

CREATE TABLE ps4_invasion_items (
  invasion_id character varying NOT NULL REFERENCES ps4_invasions ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE ps4_voidtraders (
  voidtrader_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX ps4_voidtraders_time_end_idx ON ps4_voidtraders(time_end);

CREATE TABLE ps4_voidtrader_items (
  voidtrader_id character varying NOT NULL REFERENCES ps4_voidtraders ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE xb1_alerts (
  alert_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  mission_type character varying NOT NULL
);
CREATE INDEX xb1_alerts_time_end_idx ON xb1_alerts(time_end);

CREATE TABLE xb1_alert_items (
  alert_id character varying NOT NULL REFERENCES xb1_alerts ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE xb1_invasions (
  invasion_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX xb1_invasions_time_end_idx ON xb1_invasions(time_end);

CREATE TABLE xb1_invasion_items (
  invasion_id character varying NOT NULL REFERENCES xb1_invasions ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);

CREATE TABLE xb1_voidtraders (
  voidtrader_id character varying NOT NULL PRIMARY KEY,
  time_start bigint NOT NULL,
  time_end bigint NOT NULL,
  location character varying NOT NULL
);
CREATE INDEX xb1_voidtraders_time_end_idx ON xb1_voidtraders(time_end);

CREATE TABLE xb1_voidtrader_items (
  voidtrader_id character varying NOT NULL REFERENCES xb1_voidtraders ON DELETE CASCADE,
  item_id integer NOT NULL REFERENCES items ON DELETE CASCADE,
  item_count integer NOT NULL
);
