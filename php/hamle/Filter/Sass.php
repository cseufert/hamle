<?php
/*
This project is Licenced under The MIT License (MIT)

Copyright (c) 2014 Christopher Seufert

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

 */
namespace Seufert\Hamle\Filter;

use SassFile;
use SassParser;
use SassRenderer;
use stdConf;

class Sass extends Css {
  static function filterText($s) {
    $as = explode("\n", $s);
    $indent = -1;
    foreach ($as as $line)
      if (preg_match('/^(\s+).*$/', $line, $m)) {
        $lnInd = strlen($m[1]);
        if ($indent < 0) $indent = $lnInd;
        $indent = min($indent, $lnInd);
      }
    foreach ($as as $k => $v)
      $as[$k] = substr($v, $indent);
    $s = implode("\n", $as);

    require_once ME_DIR . "/lib/phpsass/SassParser.php";
    $sp = new SassParser(array("cache" => FALSE,
        "style" => stdConf::get("me.developer") ? SassRenderer::STYLE_EXPANDED : SassRenderer::STYLE_COMPRESSED,
        "syntax" => SassFile::SASS, 'debug' => TRUE));
    $tree = $sp->toTree($s);
    $out = $tree->render();
    $pad = str_pad("", $indent, " ");
    return $pad . str_replace("\n", "\n$pad", trim($out));
  }
}