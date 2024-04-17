<?php

use Seufert\Hamle\Text;

//chdir(__DIR__ . '/../..');
//`node js/CompilePHPGrammar.js`;

require_once 'base.php';

class stringTest extends base
{
  public function testPlainString1()
  {
    $hs = new Text("\"SimpleFileName.hamle\"", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('SimpleFileName.hamle', $html);
    $this->assertEquals("'SimpleFileName.hamle'", $php);
  }
  public function testZeroString1()
  {
    $hs = new Text('0', Text::TOKEN_HTML);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('0', $html);
    $this->assertEquals('0', $php);
  }
  public function testPreseveSpaces()
  {
    $hs = new Text(' ');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(' ', $html);
    $this->assertEquals("' '", $php);
  }
  public function testDollarCodeString()
  {
    $hs = new Text('Hello $user', Text::TOKEN_CODE);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('Hello $user', $html);
    $this->assertEquals("'Hello \$user'", $php);
  }

  /**
   * @dataProvider dollarStringProvider
   *
   */
  public function testDollarString(
    string $hamle,
    string $php,
    int $mode = Text::TOKEN_HTML
  ) {
    $hs = new Text($hamle, $mode);
    $this->assertEquals($php, $hs->toPHP());
  }

  public function testDollarString1()
  {
    $hs = new Text("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=\$scope->model()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.\$scope->model()->hamleGet('user')", $php);
  }
  public function testDollarString2()
  {
    $hs = new Text("Hello {\$user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=\$scope->model()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.\$scope->model()->hamleGet('user')", $php);
  }
  public function testDollarString3()
  {
    $hs = new Text("Hello {\$[0]->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=\$scope->model()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.\$scope->model()->hamleGet('user')", $php);
  }
  public function testDollarString3alt()
  {
    $hs = new Text("Hello {\$[0]-!user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hello <?=\$scope->model()->hamleGet('user')?>", $html);
    $this->assertEquals("'Hello '.\$scope->model()->hamleGet('user')", $php);
  }
  public function testDollarString4()
  {
    $hs = new Text("Hello {\$(site)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$ctx->hamleFindTypeTags(\$scope,array('site'=>array()),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$ctx->hamleFindTypeTags(\$scope,array('site'=>array()),array(),0,0)->hamleGet('user')",
      $php,
    );
  }
  public function testDollarString4alt()
  {
    $hs = new Text("Hello {\$(site)-!user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$ctx->hamleFindTypeTags(\$scope,array('site'=>array()),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$ctx->hamleFindTypeTags(\$scope,array('site'=>array()),array(),0,0)->hamleGet('user')",
      $php,
    );
  }
  public function testDollarString5()
  {
    $hs = new Text("Hello {\$(site > address.mail@2)->state}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      'Hello <?=' .
        "\$ctx->hamleFindTypeTags(\$scope,array('site'=>array()),array(),0,0)->" .
        "hamleRel(1,array('address'=>array(0=>'mail')),array(),0,0,2)->" .
        "hamleGet('state')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '." .
        '$ctx->hamleFindTypeTags($scope,' .
        "array('site'=>array()),array(),0,0)->" .
        "hamleRel(1,array('address'=>array(0=>'mail')),array(),0,0,2)->" .
        "hamleGet('state')",
      $php,
    );
  }
  public function testDollarString6()
  {
    $hs = new Text("Hello {\$[1]->summary}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$scope->modelNum(1)->hamleGet('summary')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$scope->modelNum(1)->hamleGet('summary')",
      $php,
    );
  }
  public function testDollarString7()
  {
    $hs = new Text("$[0]->user", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("<?=\$scope->model()->hamleGet('user')?>", $html);
    $this->assertEquals("\$scope->model()->hamleGet('user')", $php);
  }
  public function testDollarString8()
  {
    $hs = new Text('{$[test]->user}', Text::TOKEN_HTML);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "<?=\$scope->namedModel('test')->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals("\$scope->namedModel('test')->hamleGet('user')", $php);
  }
  public function testDollarString9()
  {
    $hs = new Text("Hello {\$(page.home.feature)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$ctx->hamleFindTypeTags(\$scope,array('page'=>array(0=>'home',1=>'feature')),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$ctx->hamleFindTypeTags(\$scope,array('page'=>array(0=>'home',1=>'feature')),array(),0,0)->hamleGet('user')",
      $php,
    );
  }
  public function testDollarString10()
  {
    $hs = new Text(
      "Hello {\$( > test.7ba736fc-3d6e-4907-b448-d995bd78a477)->valid}",
    );
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$scope->model()->hamleRel(1,array('test'=>array(0=>'7ba736fc-3d6e-4907-b448-d995bd78a477')),array(),0,0,0)->hamleGet('valid')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$scope->model()->hamleRel(1,array('test'=>array(0=>'7ba736fc-3d6e-4907-b448-d995bd78a477')),array(),0,0,0)->hamleGet('valid')",
      $php,
    );
  }
  public function testDollarString11()
  {
    $hs = new Text("{\$(_request#get)->application}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "<?=\$ctx->hamleFindTypeId(\$scope,array('_request'=>array(0=>'get')),array(),0,0)->hamleGet('application')?>",
      $html,
    );
    $this->assertEquals(
      "\$ctx->hamleFindTypeId(\$scope,array('_request'=>array(0=>'get')),array(),0,0)->hamleGet('application')",
      $php,
    );
  }
  public function testDollarString12()
  {
    $hs = new Text("{\$(_checkout2#cart)->cond_empty___0}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $code =
      "\$ctx->hamleFindTypeId(\$scope,array('_checkout2'=>array(0=>'cart')),array(),0,0)->hamleGet('cond_empty___0')";
    $this->assertEquals("<?=$code?>", $html);
    $this->assertEquals($code, $php);
  }
  public function testDollarString13()
  {
    $hs = new Text("$(#32303 > 2011,1010,2012)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $code =
      '$ctx->hamleFindId($scope,32303,array(),0,0)->hamleRel(1,array(2011=>array(),1010=>array(),2012=>array()),array(),0,0,0)';
    $this->assertEquals("<?=$code?>", $html);
    $this->assertEquals($code, $php);
  }
  public function testDollarString14()
  {
    $hs = new Text("{\$a|strtoupper(\"a={\$a}&b={\$b}&c={\$c}\")}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $code =
      "strtoupper(\$scope->model()->hamleGet('a'),'a='.\$scope->model()->hamleGet('a').'&b='.\$scope->model()->hamleGet('b').'&c='.\$scope->model()->hamleGet('c'))";
    $this->assertEquals("<?=$code?>", $html);
    $this->assertEquals($code, $php);
  }

  public function testDollarStringSymbol1()
  {
    $hs = new Text("Hello \$test_str");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$scope->model()->hamleGet('test_str')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$scope->model()->hamleGet('test_str')",
      $php,
    );
  }
  public function testDollarStringSymbol2()
  {
    $hs = new Text("Hello \$test-str");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=\$scope->model()->hamleGet('test')?>-str",
      $html,
    );
    $this->assertEquals(
      "'Hello '.\$scope->model()->hamleGet('test').'-str'",
      $php,
    );
  }
  public function testDollarFormat1()
  {
    $hs = new Text("Hello {\$length|round(0)}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(\$scope->model()->hamleGet('length'),0)?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(\$scope->model()->hamleGet('length'),0)",
      $php,
    );
  }

  public function testDollarFormat2()
  {
    $hs = new Text("Hello {\$box->length|round(0)}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(\$scope->model()->hamleGet('box')->hamleGet('length'),0)?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(\$scope->model()->hamleGet('box')->hamleGet('length'),0)",
      $php,
    );
  }
  public function testDollarFormat3()
  {
    $hs = new Text("Hello {\$box->length|round}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(\$scope->model()->hamleGet('box')->hamleGet('length'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(\$scope->model()->hamleGet('box')->hamleGet('length'))",
      $php,
    );
  }
  public function testDollarFormat4()
  {
    $hs = new Text("Hello {\$box->length|json}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=json_encode(\$scope->model()->hamleGet('box')->hamleGet('length'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.json_encode(\$scope->model()->hamleGet('box')->hamleGet('length'))",
      $php,
    );
  }
  public function testDollarFormat5()
  {
    $hs = new Text("Hello {\$box->length|round({\$[-1]->decimals})}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(\$scope->model()->hamleGet('box')->hamleGet('length'),\$scope->modelNum(-1)->hamleGet('decimals'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(\$scope->model()->hamleGet('box')->hamleGet('length'),\$scope->modelNum(-1)->hamleGet('decimals'))",
      $php,
    );
  }
  public function testDollarExplode1()
  {
    $hs = new Text('{$box->length|itersplit(";")}', Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'itersplit',\$scope->model()->hamleGet('box')->hamleGet('length'),';')",
      $php,
    );
  }
  public function testDollarExplode2()
  {
    $hs = new Text("{\$box->length|itersplit(',')}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'itersplit',\$scope->model()->hamleGet('box')->hamleGet('length'),',')",
      $php,
    );
  }
  public function testDollarNewlineBr()
  {
    $hs = new Text("{\$desc|newlinebr}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'newlinebr',\$scope->model()->hamleGet('desc'))",
      $php,
    );
  }
  public function testDollarNewParam()
  {
    $hs = new Text('{$desc|iterSplit("\n","\'")}', Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'iterSplit',\$scope->model()->hamleGet('desc'),\"\\n\",'\\'')",
      $php,
    );
  }

  public function testSimpleQuery()
  {
    $hs = new Text('$(page:1)->title|iterSplit(\'-\')', Text::TOKEN_CONTROL);
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'iterSplit',\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array(),1,0)->hamleGet('title'),'-')",
      $hs->toPHP(),
    );
  }

  public static function invalidStringsProvider(): array
  {
    return [
      ['{$a|iterSplit("a)'],
      ["{\$a|iterSplit('a)"],
      ['{$a->->b}'],
      ['{$a'],
      ['{$a|iterSplit("_{$b.a}")'],
      ['{$[3.]->a}'],
      ['{$(image.text$)->a}'],
      ['{$( > !test)}'],
      ['{$a|strtoupper|newlinebr|trim(a)}'],
      ['{$a->b("c")'],
      ['{$a|strtoupper(}'],
    ];
  }

  /**
   * @dataProvider invalidStringsProvider
   * @param string $in
   * @param int $mode
   * @throws \Seufert\Hamle\Exception\ParseError
   */
  public function testParseErrors(
    string $in,
    int $mode = Text::TOKEN_HTML
  ): void {
    $this->expectException(\Seufert\Hamle\Exception\ParseError::class);
    $hs = new Text($in, $mode);
    var_dump($hs);
  }

  public function testDollarUrlQuery()
  {
    $hs = new Text("{\$url|strtoupper('hash={\$[-2]->hash}')}");
    $php = $hs->toPHP();
    $this->assertEquals(
      "strtoupper(\$scope->model()->hamleGet('url'),'hash='.\$scope->modelNum(-2)->hamleGet('hash'))",
      $php,
    );
  }
  public function testDollarNewlineJson()
  {
    $hs = new Text("{\$desc|newlinebr|json}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "json_encode(\$ctx->hamleFilter(\$scope,'newlinebr',\$scope->model()->hamleGet('desc')))",
      $php,
    );
  }

  public function testAsCents()
  {
    $hs = new Text("{\$price|ascents}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFilter(\$scope,'ascents',\$scope->model()->hamleGet('price'))",
      $php,
    );
    $this->assertEquals(2501, Text\Filter::ascents("$ 25.01"));
    $this->assertEquals(123456, Text\Filter::ascents("$ 1,234.562"));
  }

  public function testDollarCodeString1()
  {
    $hs = new Text("\"My Title = {\$title}\"", Text::TOKEN_CODE);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\"My Title = <?=\$scope->model()->hamleGet('title')?>\"",
      $html,
    );
  }
  public function testDollarCodeString2()
  {
    $hs = new Text("\"My Title = \$title\"", Text::TOKEN_CODE);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("\"My Title = \$title\"", $html);
  }
  public function testDollarStringEscape1()
  {
    $hs = new Text("String with \\$ Dollar Sign");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('String with $ Dollar Sign', $html);
    $this->assertEquals('\'String with $ Dollar Sign\'', $php);
  }
  public function testDollarStringEscape2()
  {
    $hs = new Text('Some & "Some" More');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('Some &amp; &quot;Some&quot; More', $html);
    $this->assertEquals('\'Some & "Some" More\'', $php);
  }
  public function testDollarStringEscape3()
  {
    $hs = new Text('This < that > this');
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals('This &lt; that &gt; this', $html);
    $this->assertEquals('\'This < that > this\'', $php);
  }

  public function testDollarStringEscape4()
  {
    $hs = new Text("I have \\\$10.00");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("I have $10.00", $html);
    $this->assertEquals("'I have \$10.00'", $php);
  }

  public function testDollarScope1()
  {
    $hs = new Text("\$[user]", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("\$scope->namedModel('user')", $php);
  }

  public function testDollarScope2()
  {
    $hs = new Text("\$[layout]->view", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$scope->namedModel('layout')->hamleGet('view')",
      $php,
    );
  }

  public function testDollarFunc1()
  {
    $hs = new Text("\$(user)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('user'=>array()),array(),0,0)",
      $php,
    );
  }

  public function testDollarFunc2()
  {
    $hs = new Text("\$(user#3)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeId(\$scope,array('user'=>array(0=>3)),array(),0,0)",
      $php,
    );
  }
  public function testDollarFunc3()
  {
    $hs = new Text("\$(#my_page)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindId(\$scope,'my_page',array(),0,0)",
      $php,
    );
  }
  public function testDollarFunc4()
  {
    $hs = new Text("\$(page:4)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array(),4,0)",
      $php,
    );
  }
  public function testDollarFunc5()
  {
    $hs = new Text("\$(page^title:1-3)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array('title'=>2),3,1)",
      $php,
    );
  }
  public function testDollarFunc6()
  {
    $hs = new Text("\$(photo.heroimage^:1)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('photo'=>array(0=>'heroimage')),array(''=>4),1,0)",
      $php,
    );
  }
  public function testDollarFunc7()
  {
    $hs = new Text("\$(.hero-image^:1)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('*'=>array(0=>'hero-image')),array(''=>4),1,0)",
      $php,
    );
  }
  public function testDollarFunc8()
  {
    $hs = new Text("\$(#_ga)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("\$ctx->hamleFindId(\$scope,'_ga',array(),0,0)", $php);
  }
  public function testDollarFuncChild1()
  {
    $hs = new Text("\$(#my_page > link)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindId(\$scope,'my_page',array(),0,0)" .
        "->hamleRel(1,array('link'=>array()),array(),0,0,0)",
      $php,
    );
  }
  public function testDollarFuncChild2()
  {
    $hs = new Text("\$(#my_page > .gallery)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindId(\$scope,'my_page',array(),0,0)" .
        "->hamleRel(1,array('*'=>array(0=>'gallery')),array(),0,0,0)",
      $php,
    );
  }
  public function testDollarFuncChild3()
  {
    $hs = new Text("\$(#menu > page,cat)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindId(\$scope,'menu',array(),0,0)" .
        "->hamleRel(1,array('page'=>array(),'cat'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testDollarFuncChild4()
  {
    $this->expectException(\Seufert\Hamle\Exception\ParseError::class);
    $hs = new Text("\$(#my_page > #me)", Text::TOKEN_CONTROL);
    var_dump($hs->toPHP());
  }
  public function testDollarFuncChild5()
  {
    $hs = new Text("\$(#heroimage > photo:4^)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindId(\$scope,'heroimage',array(),0,0)" .
        "->hamleRel(1,array('photo'=>array()),array(''=>4),4,0,0)",
      $php,
    );
  }
  public function testDollarFuncChild6()
  {
    $hs = new Text("\$( > *@2)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      '$scope->model()->' . "hamleRel(1,array('*'=>array()),array(),0,0,2)",
      $php,
    );
  }
  public function testDollarFuncChild7()
  {
    $hs = new Text("\$(  >  image@2)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      '$scope->model()->' . "hamleRel(1,array('image'=>array()),array(),0,0,2)",
      $php,
    );
  }
  public function testMultiple()
  {
    $in = <<<ENDIN
    {
      "hash": "{\$(_request#get)->hash}",
      "fileId": {\$[file]->id}
    }
    ENDIN;
    $out =
      '"{\n  \"hash\": \"".$ctx->hamleFindTypeId($scope,array(\'_request\'=>array(0=>\'get\')),array(),0,0)->hamleGet(\'hash\')."\",\n  \"fileId\": ".$scope->namedModel(\'file\')->hamleGet(\'id\')."\n}"';

    $hs = new Text($in);
    $php = $hs->toPHP();
    $this->assertEquals($out, $php);
  }
  public function testDollarFuncSort1()
  {
    $hs = new Text("\$(testim.featured^-sorder^title)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$ctx->hamleFindTypeTags(\$scope,array('testim'=>array(0=>'featured')),array('sorder'=>3,'title'=>2),0,0)",
      $php,
    );
  }
  public function testDollarFuncParent1()
  {
    $hs = new Text("\$( < cat)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      '$scope->model()->' . "hamleRel(2,array('cat'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testDollarHash()
  {
    $hs = new Text('"#test:1"', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("'#test:1'", $php);
  }

  public function testNested()
  {
    $hs = new Text('$($[0] > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$scope->model()->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testNested2()
  {
    $hs = new Text('$($[-1] > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$scope->modelNum(-1)->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }
  public function testNestedNamed()
  {
    $hs = new Text('$($[prev] > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$scope->namedModel('prev')->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testNestedNamed2()
  {
    $hs = new Text('$($[prev] > next > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "\$scope->namedModel('prev')->hamleRel(1,array('next'=>array()),array(),0,0,0)->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  //  public function testDollarFuncVar1() {
  //    $hs = new Text('$(product.{$tags})', Text::TOKEN_CONTROL);
  //    $php = $hs->toPHP();
  //    $this->assertEquals("\$ctx->hamleFindTypeTags(\$scope,array('product'=>array(0=>\$scope->model()->hamleGet('tags'))),array(),0,0)", $php);
  //  }

  public function testFilterFunc()
  {
    $hs = new Text('Date {$date|format_date}');
    $php = $hs->toPHP();
    $this->assertEquals(
      "'Date '.\$ctx->hamleFilter(\$scope,'format_date',\$scope->model()->hamleGet('date'))",
      $php,
    );
    $hs = new Text('Date {$date|format_date("Y-m-d")}');
    $php = $hs->toPHP();
    $this->assertEquals(
      "'Date '.\$ctx->hamleFilter(\$scope,'format_date',\$scope->model()->hamleGet('date'),'Y-m-d')",

      $php,
    );
  }

  /**
   * @dataProvider varDataProvider
   */
  public function testVarToCode(string $var): void
  {
    $this->assertEquals($var, eval('return ' . Text::varToCode($var) . ';'));
  }

  public static function varDataProvider()
  {
    return [
      'newline' => ["\n"],
      'multiline' => ["Hi\nChris"],
      'plain text' => ['My Data'],
      'string with tab' => ["\tText"],
      'string with blackslash' => ['\\'],
      'double quotes' => ['{"id":"abc"}'],
      'double quote newline' => ["{\n  \"id\":\"abc\"\n}"],
      'single quotes' => ["{a:'abc'}"],
    ];
  }

  public static function dollarStringProvider(): array
  {
    return [
      [
        '$($[0] > image@1:1)',
        "\$scope->model()->hamleRel(1,array('image'=>array()),array(),1,0,1)",
        Text::TOKEN_CONTROL,
      ],
      [
        '$( _cursor#next > bldwork )',
        "\$ctx->hamleFindTypeId(\$scope,array('_cursor'=>array(0=>'next')),array(),0,0)->hamleRel(1,array('bldwork'=>array()),array(),0,0,0)",
        Text::TOKEN_CONTROL,
      ],
      [
        '{$($[-1] &gt; page)-&gt;title}',
        "\$scope->modelNum(-1)->hamleRel(2,array('page'=>array()),array(),0,0,0)->hamleGet('title')",
        Text::TOKEN_HTML,
      ],
      [
        '{$($[-1] &lt; page)-&gt;title|strtoupper("{$(#12)-&gt;size}")}',
        "strtoupper(\$scope->modelNum(-1)->hamleRel(1,array('page'=>array()),array(),0,0,0)->hamleGet('title'),\$ctx->hamleFindId(\$scope,12,array(),0,0)->hamleGet('size'))",
        Text::TOKEN_HTML,
      ],
      [
        '$(_inspect#organisation^title:1000)',
        "\$ctx->hamleFindTypeId(\$scope,array('_inspect'=>array(0=>'organisation')),array('title'=>2),1000,0)",
        Text::TOKEN_CONTROL,
      ],
    ];
  }
}
