WITH numbered_rows AS (
  SELECT id, row_number() OVER (ORDER BY id) AS new_id
  FROM histories
)
UPDATE histories h
SET id = nr.new_id
FROM numbered_rows nr
WHERE h.id = nr.id;

-- Reset auto-incremented IDs and set to start from 1
SELECT setval(pg_get_serial_sequence('histories', 'id'), (SELECT MAX(id) FROM histories));
