CREATE OR REPLACE FUNCTION sp_cond_exec(character varying, character varying, character varying)
  RETURNS character varying AS
'
/*
	sp_cond_exec(Tipo, Oggetto, Query)

	Esegue una query passata come argomento se verifica l`esistenza di un dato oggetto.

	Parametri: Tipo, Oggetto, Query

	Tipo: FUNCTION|TABLE|!TABLE|COLUMN|!COLUMN|INDEX|!INDEX|TYPE|!TYPE|
	      CONSTRAINT|!CONSTRAINT|QUERY|!QUERY|TRIGGER|!TRIGGER

	Oggetto: Nome dell`oggetto di cui verificare l`esistenza:
	
	FUNCTION: nome della funzione senza parentesi o parametri
	TABLE o INDEX o TYPE: nome dell`oggetto 
	COLUMN: nome della colonna nel formato `table.column`
	CONSTRAINT: nome della primary/foreign key 
	TRIGGER: nome del trigger nel formato `table.trigger`
	QUERY: select SQL completa. Se almeno un recordset viene ritornato allora Query viene eseguita
	
	!TABLE, !INDEX, !TYPE, !COLUMN, !CONSTRAINT, !TRIGGER, !QUERY: equivalgono ai corrispondenti 
	senza ! ma invertono il test: la query viene eseguita se l`oggetto non esiste.

	N.B.: Nel caso FUNCTION Query deve contenere, in qualsiasi punto,
	      la definizione dei parametri della funzione, ogni parametro fra doppi apici.

	Ritorna il valore di Query se eseguita, `NULL` altrimenti.
*/

DECLARE sTip ALIAS FOR $1;
DECLARE sName ALIAS FOR $2;
DECLARE sCmd ALIAS FOR $3;

DECLARE iPos int4;
DECLARE sTableName varchar;
DECLARE sColName varchar;
DECLARE sTrgName varchar;

DECLARE sDummy varchar;
DECLARE sArgs varchar;
DECLARE sOid varchar;

DECLARE sQry varchar;
DECLARE sRec RECORD;

BEGIN

IF sTip = ''FUNCTION'' THEN
	BEGIN

	iPos := 2;
	sArgs := '''';
	
	WHILE iPos > 0 LOOP
		sDummy = split_part(sCmd, ''"'', iPos);

		IF sDummy <> '''' THEN
			SELECT INTO sOid oid FROM pg_type WHERE pg_type.typname = sDummy;
			sArgs := sArgs || '' '' || sOid;
			iPos := iPos + 2;
		ELSE
			iPos := 0;
		END IF;
	END LOOP;

	sQry := ''SELECT proargtypes FROM pg_proc WHERE lower(proname) = '' || quote_literal(lower(sName)) || '' AND proargtypes = '' || quote_literal(btrim(sArgs));
	FOR sRec IN EXECUTE sQry LOOP
		EXECUTE sCmd;
		RETURN sCmd;
	END LOOP;

	RETURN ''NULL'';
	END;
ELSIF sTip = ''TABLE''  OR sTip = ''INDEX'' THEN
	BEGIN
	PERFORM relname,relnamespace FROM pg_class WHERE lower(relname) = lower(sName) AND pg_table_is_visible(oid);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''TYPE'' THEN
	BEGIN
	PERFORM typname FROM pg_type WHERE lower(typname) = lower(sName);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''COLUMN'' THEN
	BEGIN
	iPos := position(''.'' in sName);
	sTableName := substring(sName from 1 for iPos - 1);
	sColName := substring(sName from iPos + 1 for char_length(sName) - iPos);
	PERFORM attname,attrelid FROM pg_class INNER JOIN pg_attribute 
		ON pg_class.relfilenode = pg_attribute.attrelid
		WHERE lower(pg_class.relname) = lower(sTableName) AND lower(pg_attribute.attname) = lower(sColName);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''CONSTRAINT'' THEN
	BEGIN
	PERFORM conname FROM pg_constraint WHERE lower(conname) = lower(sName);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''TRIGGER'' THEN
	BEGIN
	iPos := position(''.'' in sName);
	sTableName := substring(sName from 1 for iPos - 1);
	sTrgName := substring(sName from iPos + 1 for char_length(sName) - iPos);
	PERFORM tgname FROM pg_trigger INNER JOIN pg_class 
		ON pg_class.oid = pg_trigger.tgrelid
		WHERE lower(pg_class.relname) = lower(sTableName) AND lower(pg_trigger.tgname) = lower(sTrgName);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!TRIGGER'' THEN
	BEGIN
	iPos := position(''.'' in sName);
	sTableName := substring(sName from 1 for iPos - 1);
	sTrgName := substring(sName from iPos + 1 for char_length(sName) - iPos);
	PERFORM tgname FROM pg_trigger INNER JOIN pg_class 
		ON pg_class.oid = pg_trigger.tgrelid
		WHERE lower(pg_class.relname) = lower(sTableName) AND lower(pg_trigger.tgname) = lower(sTrgName);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!COLUMN'' THEN
	BEGIN
	iPos := position(''.'' in sName);
	sTableName := substring(sName from 1 for iPos - 1);
	sColName := substring(sName from iPos + 1 for char_length(sName) - iPos);
	PERFORM attname,attrelid FROM pg_class INNER JOIN pg_attribute 
		ON pg_class.relfilenode = pg_attribute.attrelid
		WHERE lower(pg_class.relname) = lower(sTableName) AND lower(pg_attribute.attname) = lower(sColName);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!TABLE''  OR sTip = ''!INDEX'' THEN
	BEGIN
	PERFORM relname,relnamespace FROM pg_class WHERE lower(relname) = lower(sName) AND pg_table_is_visible(oid);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!TYPE'' THEN
	BEGIN
	PERFORM typname FROM pg_type WHERE lower(typname) = lower(sName);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!CONSTRAINT'' THEN
	BEGIN
	PERFORM conname FROM pg_constraint WHERE lower(conname) = lower(sName);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''QUERY'' THEN
	BEGIN
	FOR sRec IN EXECUTE sName LOOP
		EXECUTE sCmd;
		RETURN sCmd;
	END LOOP;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!QUERY'' THEN
	BEGIN
	FOR sRec IN EXECUTE sName LOOP
		RETURN ''NULL'';
	END LOOP;
	EXECUTE sCmd;
	RETURN sCmd;
	END;
ELSIF sTip = ''VIEW'' THEN
	BEGIN
	PERFORM viewname FROM pg_views WHERE lower(viewname) = lower(sName);
	IF FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSIF sTip = ''!VIEW'' THEN
	BEGIN
	PERFORM viewname FROM pg_views WHERE lower(viewname) = lower(sName);
	IF NOT FOUND THEN
		EXECUTE sCmd;
		RETURN sCmd;
	END IF;
	RETURN ''NULL'';
	END;
ELSE
	RETURN ''ERROR'';
END IF;
END;
'
  LANGUAGE 'plpgsql' VOLATILE;


       SELECT * FROM sp_cond_exec('!TABLE','tbldfnsoftware001','CREATE TABLE tbldfnsoftware001(nomprogramma character varying (100) 
NOT NULL,  idmajor character varying(6),  idminor character varying(6),  idrevision character varying(6),  idsubrevision 
character varying(6),   md5 character varying (50),  ricregistrazione character varying (1),  ordregistrazione integer,  
datprogramma timestamp without time zone,  pacrilascio character varying(50),
sys_idazcreazione integer NOT NULL DEFAULT 0,  sys_idutcreazione integer NOT NULL DEFAULT 0,  sys_datcreazione timestamp 
without time zone NOT NULL,  sys_idutulmodifica integer NOT NULL DEFAULT 0,  sys_datulmodifica timestamp without time zone 
NOT NULL, cpccchk character(10), CONSTRAINT pk_tbldfnsoftware001 PRIMARY KEY (nomprogramma)) WITH OIDS; ALTER TABLE 
tbldfnsoftware001 OWNER TO postgres;');

SELECT * FROM sp_cond_exec('!TABLE','tbldfndatabases001','CREATE TABLE tbldfndatabases001
(
  codicecallcenter character varying(6) NOT NULL,
  dbsipadx character varying(20) NOT NULL,
  codicecallcenterextra character varying(50),
  sys_idazcreazione integer NOT NULL DEFAULT 0,
  sys_idutcreazione integer NOT NULL DEFAULT 0,
  sys_datcreazione timestamp without time zone NOT NULL,
  sys_idutulmodifica integer NOT NULL DEFAULT 0,
  sys_datulmodifica timestamp without time zone NOT NULL,
  cpccchk character(10),
  CONSTRAINT pk_tbldfndatabases001 PRIMARY KEY (codicecallcenter, dbsipadx)
)
WITH OIDS;
ALTER TABLE tbldfndatabases001 OWNER TO postgres;');

SELECT * FROM sp_cond_exec('!TABLE','tbldfnotherfiles001','CREATE TABLE tbldfnotherfiles001
(
  nomprogramma character varying(100) NOT NULL,
  idmajor character varying(6),
  idminor character varying(6),
  idrevision character varying(6),
  idsubrevision character varying(6),
  md5 character varying(50),
  ricregistrazione character varying(1),
  ordregistrazione integer,
  datprogramma timestamp without time zone,
  pacrilascio character varying(50),
  tipfile character varying(50),
  subpath character varying(50),
  sys_idazcreazione integer NOT NULL DEFAULT 0,
  sys_idutcreazione integer NOT NULL DEFAULT 0,
  sys_datcreazione timestamp without time zone NOT NULL,
  sys_idutulmodifica integer NOT NULL DEFAULT 0,
  sys_datulmodifica timestamp without time zone NOT NULL,
  cpccchk character(10),
  forzasediverso character(1),
  CONSTRAINT pk_tbldfnotherfiles001 PRIMARY KEY (nomprogramma)
)
WITH OIDS;
ALTER TABLE tbldfnotherfiles001 OWNER TO postgres;');

SELECT * FROM sp_cond_exec('!TABLE','tbldfnserver001','CREATE TABLE tbldfnserver001
(
  codicecallcenter character varying(6) NOT NULL,
  ipsipadx character varying(20),
  hostname character varying(255),
  ipextra character varying(255),
  phpver character varying(255),
  phpgdver character varying(255),
  linuxver character varying(255),
  idclienteqms integer DEFAULT 0,
  sys_idazcreazione integer NOT NULL DEFAULT 0,
  sys_idutcreazione integer NOT NULL DEFAULT 0,
  sys_datcreazione timestamp without time zone NOT NULL,
  sys_idutulmodifica integer NOT NULL DEFAULT 0,
  sys_datulmodifica timestamp without time zone NOT NULL,
  cpccchk character(10),
  CONSTRAINT pk_tbldfnserver001 PRIMARY KEY (codicecallcenter)
)
WITH OIDS;
ALTER TABLE tbldfnserver001 OWNER TO postgres;');

SELECT * FROM sp_cond_exec('!TABLE','tbldfnsoftwaredependencies001','CREATE TABLE tbldfnsoftwaredependencies001
(
  nomcaller character varying(100) NOT NULL,
  nomprogramma character varying(100) NOT NULL,
  sys_idazcreazione integer NOT NULL DEFAULT 0,
  sys_idutcreazione integer NOT NULL DEFAULT 0,
  sys_datcreazione timestamp without time zone NOT NULL,
  sys_idutulmodifica integer NOT NULL DEFAULT 0,
  sys_datulmodifica timestamp without time zone NOT NULL,
  cpccchk character(10),
  CONSTRAINT pk_tbldfnsoftwaredependencies001 PRIMARY KEY (nomcaller, nomprogramma)
)
WITH OIDS;
ALTER TABLE tbldfnsoftwaredependencies001 OWNER TO postgres;');

