CREATE TABLE books (
                id INTEGER NOT NULL PRIMARY KEY,
                title VARCHAR ( 255 ) NOT NULL,
                author VARCHAR(255) NOT NULL,
                sortauthor VARCHAR(255) NOT NULL,
                file VARCHAR(255) NOT NULL,
                summary TEXT,
                md5id varchar(255) NOT NULL UNIQUE,
                series_id INTEGER,
                series_volume DECIMAL(5,2),
                added timestamp NOT NULL
                );
CREATE TABLE tags (
                id INTEGER NOT NULL PRIMARY KEY,
                tag VARCHAR ( 255 ) NOT NULL UNIQUE
                );
CREATE TABLE taggedbooks (
                bookid INTEGER NOT NULL,
                tagid  INTEGER NOT NULL
                );
CREATE TABLE activitylog (
                logid INTEGER NOT NULL PRIMARY KEY,
                datestamp  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                entry TEXT NOT NULL
                , level int(8) not null default 1);
CREATE TABLE downloadqueue (
                queueid INTEGER NOT NULL PRIMARY KEY,
                datestamp  TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                entry VARCHAR(255) NOT NULL,
                done INTEGER DEFAULT 0 NOT NULL
                );
CREATE TABLE series (
  id INTEGER NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL
);
CREATE TABLE busy_flag (
  busy INTEGER,
  job  TEXT
);
INSERT INTO busy_flag VALUES (0, '');
