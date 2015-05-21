 /**
 * Copyright 2013 Megatome Technologies
 *
 * Dual licensed under the MIT and GPL licenses.
 *
 * This is a brush for use with SyntaxHighlighter - 
 * http://alexgorbatchev.com/SyntaxHighlighter
 * 
 * This brush is an attempt to provide highlighting for the Pig query language.
 */
;(function()
{
	// CommonJS
	typeof(require) != 'undefined' ? SyntaxHighlighter = require('shCore').SyntaxHighlighter : null;

	function Brush()
	{
		var keywords = 'arrange as asc bag by bytearray cache cat cd chararray cogroup ' +
					   'cp cross %declare %default define desc describe ' +
					   'distinct double du dump e E eval exec explain f F filter ' +
					   'flatten float foreach full generate group help if ' +
					   'illustrate inner input int into is join kill l L left limit ' +
					   'load long ls map matches mkdir mv not null or order ' +
					   'outer output parallel pig pwd quit ' +
					   'register right rm rmf run sample set ship split ' +
					   'stderr stdin stdout store stream ' +
					   'through tuple union using ' +
					   '\\. \\# ::';

		var cswords =  'AVG BinStorage CONCAT copyFromLocal copyToLocal COUNT ' +
					   'DIFF MAX MIN PigDump PigStorage SIZE SUM TextLoader TOKENIZE';

		this.regexList = [
			{ regex: /--(.*)$/gm,												css: 'comments' },			// one line and multiline comments
			{ regex: SyntaxHighlighter.regexLib.multiLineDoubleQuotedString,	css: 'string' },			// double quoted strings
			{ regex: SyntaxHighlighter.regexLib.multiLineSingleQuotedString,	css: 'string' },			// single quoted strings
			{ regex: new RegExp(this.getKeywords(keywords), 'gmi'),				css: 'keyword' },			// keyword
			{ regex: new RegExp(this.getKeywords(cswords), 'gm'),				css: 'keyword' }			// case sensitive keyword
			];
	};

	Brush.prototype	= new SyntaxHighlighter.Highlighter();
	Brush.aliases	= ['pig'];

	SyntaxHighlighter.brushes.Pig = Brush;

	// CommonJS
	typeof(exports) != 'undefined' ? exports.Brush = Brush : null;
})();

