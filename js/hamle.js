/**
* HAMLE Javascript Engine
*
* @author Chris Seufert <chris@seufert.id.au>
* @requires jQuery - http://jquery.com/
* @requires url.js - http://medialize.github.io/URI.js/
*/


hamle = {
  autoload: function() {
    $("script").each(function() {
      var $this = $(this);
      if($this.attr('type') == "text/x-hamle") {
        var _name = $this.attr('data-name');
        var _tpl = hamle.compile($this.html());
        hamle.tpl[_name] = function(model) {
          return _tpl.children();
        };
      };
    });
  },
  compile: function(s) {
    function procLines(lines) {
      var indents = [];
      function indentLevel(indent) {
        if(0 == indents.length) {
          indents.push(indent);
          return 0;
        };
        for (var i = 0; i < indents.length; i++) {
          if(indents[i] == indent) {
            indents = indents.slice(0,i);
            return i;
          };
        };
        indents.push(indent);
        return indents.length;
      };

      function hamleTag(tag, classid, params) {
        if(tag == null) tag = "div";
        var $tag = $(document.createElement(tag));
        if(params) {
          var attrs = URI("").query(params.substr(1,params.length-2)).query(true)
          $tag.attr(attrs);
        };
        return $tag;
      }

      var reParse = /^(\s*)(?:(?:([a-zA-Z0-9]*)((?:[\.#!][\w\-\_]+)*)(\[(?:(?:\{\$[^\}]+\})?[^\\\]{]*?(?:\\.)*?(?:{[^\$])*?)+\])?)|([_\/]{1,2})|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/;
//      var reParse = /(\s*)(?:(?:([a-zA-Z0-9]*)((?:[\.#!][\w\-\_]+)*)(\[(?:(?:\{\$[^\}]+\})?[^\\\]{]*?(?:\\.)*?(?:{[^\$])*?)+\])?)|([_\/]{1,2})|([\|:\$]\w+)|({?\$[^}]+}?)|)(?: (.*))?$/;
      var root = $("<div></div>");
      var path = [root];
      var current = root;
      var lineCount = lines.length;
      var lineNo = 0;
      while(lineNo < lineCount) {
        var line = lines[lineNo];
        var _m = [];
        if($.trim(line)) if(_m = reParse.exec(line)) {
          var indent = _m[1].length;
          var i = indentLevel(indent);
          var tagname = _m[2]?_m[2]:"div";
          var classid = _m[2]?_m[2]:"";
          var params = _m[4]?(_m[4].replace('\\&','%26')):"";
          var textcode = _m[5]?_m[5]:"";
          var text = _m[8]?_m[8]:"";
          var code = _m[6]?_m[6]:"";
          var tag = null;
          switch(code.length?code.substr(0,1):textcode) {
            case "|":
              tag = $(document.createElement('div')).attr("data-control",code.substr(1)).attr("data-condition",text);
              //throw "Control tags not implemented"
              break;
            case ":":
              throw "Fitler tag not implemented";
              break;
            case "_":
            case "__":
              throw "Text tag not implemented";
              break;
            case "/":
            case "//":
              throw "Comment tag not implemented";
              break;
            default:
              tag = hamleTag(tagname, classid, params);
              tag.html(text);
              break;
          };
          if(i == 0) {
            root.append(tag);
            path = [root, tag];
          } else {
            path[i-1].append(tag);
            path = path.slice(0,i);
            path[i] = tag;
          }
        } else {
          throw "Unable to parse " + lineNo + " in hamle file\n" + line;
        }
        lineNo++;
      }
      return root;
    }
    var _root = procLines(s.split("\n"));
    console.log(_root.html());
    return _root;
  },
  "tpl":[]
};