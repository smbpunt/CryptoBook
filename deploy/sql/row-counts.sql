-- Exact row count per table, sorted: psql -X -At -d <db> -f deploy/sql/row-counts.sql > counts.txt
SELECT format('SELECT %L || '' '' || count(*) FROM %I.%I', tablename, schemaname, tablename)
FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename
\gexec
