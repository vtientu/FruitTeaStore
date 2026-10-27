-- Run once on existing databases only. New installs include this column.
ALTER TABLE orders ADD COLUMN email VARCHAR(254) NOT NULL DEFAULT '';
