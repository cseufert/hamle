var pegjs = require("pegjs");
var phpegjs = require("phpegjs");
var fs = require('fs');

var parser = pegjs.generate(fs.readFileSync("../StringGrammar.peg", 'utf-8'), {
  plugins: [phpegjs],
  phpegjs: {
    parserNamespace: 'Seufert\\Hamle\\Grammar'
  }
});
fs.writeFileSync('../php/hamle/Grammar/Parser.php', parser);
