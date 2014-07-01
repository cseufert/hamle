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
          var _model = model;
          _tpl.find("span[data-var]").each(function(item) {
            var key = $(this).attr("data-var");
            if(key in model) {
              $(this).html(model[key]);
            };
          });
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

      function hamleString(s, m) {
        var nodes = [];
        var mode = m?m:"html";
        var buff = "";
        var reVar = /\$([a-zA-Z0-9_]+)/;
        var reBarVar = /\{\$([a-zA-Z0-9_]+)(.*?)\}/;
        function dollarStr() {
          var _m = s.match(reVar);
          if(!_m) throw "Unable to determine variable in string (" + s + ")";
          s = s.substr(1+_m[1].length)
          nodes.push($(document.createElement("span")).attr('data-var',_m[1]));
        }
        function barDollar() {
          var _m = s.match(reBarVar);
          if(!_m) throw "Unable to determine variable in string (" + s + ")";
          s = s.substr(3 + _m[1].length + _m[2].length);
          nodes.push($(document.createElement("span")).attr('data-var',_m[1]));
          if(_m[2]) throw "Variable accessor is not yet suppoted (" + _m[2] + ")";
        }
        function bufferText() {
          var len = [];
          var p = s.indexOf("$");
          if(p >= 0) len.push(p);
          var p = s.indexOf("{$");
          if(p >= 0) len.push(p);
          var p = s.indexOf("\\");
          if(p >= 0) len.push(p);
          if(len.length) {
            minLen = Math.min.apply(Math,len);
            buff = buff + s.substr(0,minLen);
            s = s.substr(minLen);
          } else {
            buff = buff + s;
            s = "";
            nodes.push($(document.createTextNode(buff)));
          }

        }
        while(s.length > 1) {
          var _s = s.charAt(0);
          if(_s == "\\" && s.charAt(1) == "$") {
            buff = buff + s.substr(0,2);
            s = s.substr(2);
          } else {
            if(_s == "{" && s.charAt(1) == "$") {
              if(mode == "html") {
                if(buff.length) nodes.push($(document.createTextNode(buff)));
                buff = "";
                barDollar();
              } else {
                bufferText();
              }
            } else {
              if(_s == "$") {
                if(mode == "html") {
                  if(buff.length) nodes.push($(document.createTextNode(buff)));
                  buff = "";
                  dollarStr();
                } else {
                  bufferText();
                }
              } else {
                bufferText();
              }
            }
          }
        }
        return nodes;
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
              tag.append(hamleString(text));
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