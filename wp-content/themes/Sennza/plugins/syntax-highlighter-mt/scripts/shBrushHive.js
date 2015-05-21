/**
 * Copyright 2013 Megatome Technologies
 *
 * Dual licensed under the MIT and GPL licenses.
 *
 * This is a brush for use with SyntaxHighlighter - 
 * http://alexgorbatchev.com/SyntaxHighlighter
 * 
 * This brush is an attempt to provide highlighting for the Hive query language.
 */
;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		var funcs	=	'AVG COUNT CUME_DIST DENSE_RANK MAX MIN SORT LEFT RIGHT';

		// These are from the original SQL brush. Not sure how many are relevant to Hive QL.
		/*var funcs	=	'abs avg case cast coalesce convert count current_timestamp ' +
						'current_user day isnull left lower month nullif replace right ' +
						'session_user space substring sum system_user upper user year';*/

		var keywords = 	'ADD AFTER ALTER ARCHIVE ARRAY AS ASC BIGINT BINARY BOOLEAN BUCKET BUCKETS ' +
						'BY CASCADE CHANGE CLUSTER CLUSTERED COLLECTION COLUMN COLUMNS COMMENT CREATE CURRENT ' +
						'DATA DATABASE DATABASES DBPROPERTIES DECIMAL DEFERRED DELIMITED DEPENDENCY DESC DESCRIBE ' +
						'DISABLE DISTINCT DISTRIBUTE DOUBLE DROP ENABLE ESCAPED EXISTS EXPLAIN EXPORT EXTENDED EXTERNAL ' +
						'FIELDS FILEFORMAT FIRST FIRST_VALUE FLOAT FORMAT FORMATTED FROM FULL FUNCTION FUNCTIONS GROUP ' +
						'HAVING IDXPROPERTIES IF IGNORE IMPORT INPATH INDEX INDEXES INPUTFORMAT INSERT INT INTO ITEMS KEYS ' +
						'LAG LAST_VALUE LATERAL LEAD LIMIT LINES LOAD LOCATION MAP MSCK NO_DROP NTILE OF OFFLINE ON ORC OUT OVER OVERWRITE OUTPUTFORMAT ' +
						'PARTITION PARTITIONED PERCENT_RANK PRECEDING PROTECTION RANK RCFILE REBUILD RECOVER RENAME REPAIR ' +
						'REPLACE RESTRICT ROW ROWS ROW_NUMBER SCHEMA SCHEMAS SELECT SEMI SEQUENCEFILE SERDE SERDEPROPERTIES ' +
						'SET SHOW SKEWED SMALLINT SORTED STORED STRING STRUCT TABLE TABLES TABLESAMPLE ' +
						'TBLPROPERTIES TEMPORARY TERMINATED TEXTFILE TIMESTAMP TINYINT TO TOUCH ' +
						'TRUNCATE UNARCHIVE UNBOUNDED UNION UNIONTYPE VIEW WHERE WINDOW WITH';

		var cswords =   'BLOCK__OFFSET__INSIDE__FILE INPUT__FILE__NAME';

		// These are from the original SQL brush. More of these may be OK to add.
		/*var keywords =	'absolute action add after alter as asc at authorization begin bigint ' +
						'binary bit by cascade char character check checkpoint close collate ' +
						'column commit committed connect connection constraint contains continue ' +
						'create cube current current_date current_time cursor database date ' +
						'deallocate dec decimal declare default delete desc distinct double drop ' +
						'dynamic else end end-exec escape except exec execute false fetch first ' +
						'float for force foreign forward free from full function global goto grant ' +
						'group grouping having hour ignore index inner insensitive insert instead ' +
						'int integer intersect into is isolation key last level load local max min ' +
						'minute modify move name national nchar next no numeric of off on only ' +
						'open option order out output partial password precision prepare primary ' +
						'prior privileges procedure public read real references relative repeatable ' +
						'restrict return returns revoke rollback rollup rows rule schema scroll ' +
						'second section select sequence serializable set size smallint static ' +
						'statistics table temp temporary then time timestamp to top transaction ' +
						'translation trigger true truncate uncommitted union unique update values ' +
						'varchar varying view when where with work';*/

		var operators =	'all and any between cross in join like not null or outer some';

		this.regexList = [
			{ regex: /--(.*)$/gm,												css: 'comments' },			// one line and multiline comments
			{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,	css: 'string' },			// double quoted strings
			{ regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,	css: 'string' },			// single quoted strings
			{ regex: new RegExp(this.getKeywords(funcs), 'gmi'),				css: 'color2' },			// functions
			{ regex: new RegExp(this.getKeywords(operators), 'gmi'),			css: 'color1' },			// operators and such
			{ regex: new RegExp(this.getKeywords(keywords), 'gmi'),				css: 'keyword' },			// keyword
			{ regex: new RegExp(this.getKeywords(cswords), 'gm'),				css: 'keyword' }			// case sensitive keyword
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['hive'];

	SyntaxHighlighter.brushes.Hive = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();

