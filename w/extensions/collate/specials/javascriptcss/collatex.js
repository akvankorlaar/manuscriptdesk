  /*
   * This function constructs the collate table from the raw collatex output (json) 
   * 
   * important notice: Active development of the YUI library has ended as of 29 August 2014 (see https://en.wikipedia.org/wiki/YUI_Library). 
   */

YUI().use('node', 'json', 'json-parse', 'json-stringify', 'yui-base', 'escape', 'array-extras', function (Y) {
  

                var table = Y.Node.create('<table class="alignment" id="align"/>');
                var cells = [];
                var variantStatus = [];
                
                Y.each(at.table, function (r) {
                    var cellContents = [];
                    
                    Y.each(r, function (c) {
                        cellContents.push(c.length == 0 ? null : Y.Array.reduce(c, "", function (str, next) {
                            next = Y.Lang.isString(next) ? next : Y.dump(next);
                            return str + next;
                        }));
                    });
                    
                    cells.push(cellContents);
                    
                    var cellContentsFiltered = Y.Array.filter(cellContents, function (c) {
                        return (c != null);
                    });
                    
                    var cellContentsNormalized = Y.Array.map(cellContentsFiltered, function (c) {
                    	var textonly =  c.replace(/\n/g, "");
                    	return Y.Lang.trimRight(textonly).toLowerCase();
                    });
                    
                    variantStatus.push(Y.Array.dedupe(cellContentsNormalized).length == 1);
                });
                    
                for (var wc = 0; wc < at.witnesses.length; wc++) {
                    var row = table.appendChild(Y.Node.create('<tr/>'));
                    Y.each(cells, function (r, cc) {
                        var c = r[wc];
                        row.append('<td class="' + (variantStatus[cc] ? "invariant" : "variant") + (c == null ? " gap" : "") + '">' + (c == null ? "" : c.replace(/\n/g, "<br />")));
                    });
                }
           
                Y.one("#result").append(table);                
              
        });
