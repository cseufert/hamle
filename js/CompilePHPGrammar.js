var pegjs = require("pegjs");
var phpegjs = require("phpegjs");
var fs = require('fs');

var parser = pegjs.generate(fs.readFileSync(__dirname + "/../StringGrammar.peg", 'utf-8'), {
  plugins: [phpegjs],
  phpegjs: {
    parserNamespace: 'Seufert\\Hamle\\Grammar',
  },
  allowedStartRules: ['HtmlInput','CodeInput','ControlInput']
});
fs.writeFileSync(__dirname + '/../php/hamle/Grammar/Parser.php', parser);
