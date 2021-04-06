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
  public function testDollarString1()
  {
    $hs = new Text("Hello \$user");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString2()
  {
    $hs = new Text("Hello {\$user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString3()
  {
    $hs = new Text("Hello {\$[0]->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString3alt()
  {
    $hs = new Text("Hello {\$[0]-!user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals("'Hello '.Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString4()
  {
    $hs = new Text("Hello {\$(site)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Run::modelTypeTags(array('site'=>array()),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Run::modelTypeTags(array('site'=>array()),array(),0,0)->hamleGet('user')",
      $php,
    );
  }
  public function testDollarString4alt()
  {
    $hs = new Text("Hello {\$(site)-!user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Run::modelTypeTags(array('site'=>array()),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Run::modelTypeTags(array('site'=>array()),array(),0,0)->hamleGet('user')",
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
        "Hamle\\Run::modelTypeTags(array('site'=>array()),array(),0,0)->" .
        "hamleRel(1,array('address'=>array(0=>'mail')),array(),0,0,2)->" .
        "hamleGet('state')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '." .
        'Hamle\\Run::modelTypeTags(' .
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
      "Hello <?=Hamle\\Scope::get(1)->hamleGet('summary')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Scope::get(1)->hamleGet('summary')",
      $php,
    );
  }
  public function testDollarString7()
  {
    $hs = new Text("$[0]->user", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("<?=Hamle\\Scope::get()->hamleGet('user')?>", $html);
    $this->assertEquals("Hamle\\Scope::get()->hamleGet('user')", $php);
  }
  public function testDollarString8()
  {
    $hs = new Text('{$[test]->user}', Text::TOKEN_HTML);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "<?=Hamle\\Scope::getName('test')->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "Hamle\\Scope::getName('test')->hamleGet('user')",
      $php,
    );
  }
  public function testDollarString9()
  {
    $hs = new Text("Hello {\$(page.home.feature)->user}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Run::modelTypeTags(array('page'=>array(0=>'home',1=>'feature')),array(),0,0)->hamleGet('user')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Run::modelTypeTags(array('page'=>array(0=>'home',1=>'feature')),array(),0,0)->hamleGet('user')",
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
      "Hello <?=Hamle\Scope::get()->hamleRel(1,array('test'=>array(0=>'7ba736fc-3d6e-4907-b448-d995bd78a477')),array(),0,0,0)->hamleGet('valid')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\Scope::get()->hamleRel(1,array('test'=>array(0=>'7ba736fc-3d6e-4907-b448-d995bd78a477')),array(),0,0,0)->hamleGet('valid')",
      $php,
    );
  }
  public function testDollarString11()
  {
    $hs = new Text("{\$(_request#get)->application}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "<?=Hamle\Run::modelTypeId(array('_request'=>array(0=>'get')),array(),0,0)->hamleGet('application')?>",
      $html,
    );
    $this->assertEquals(
      "Hamle\Run::modelTypeId(array('_request'=>array(0=>'get')),array(),0,0)->hamleGet('application')",
      $php,
    );
  }
  public function testDollarString12()
  {
    $hs = new Text("{\$(_checkout2#cart)->cond_empty___0}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $code =
      "Hamle\Run::modelTypeId(array('_checkout2'=>array(0=>'cart')),array(),0,0)->hamleGet('cond_empty___0')";
    $this->assertEquals("<?=$code?>", $html);
    $this->assertEquals($code, $php);
  }

  public function testDollarStringSymbol1()
  {
    $hs = new Text("Hello \$test_str");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('test_str')?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Scope::get()->hamleGet('test_str')",
      $php,
    );
  }
  public function testDollarStringSymbol2()
  {
    $hs = new Text("Hello \$test-str");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=Hamle\\Scope::get()->hamleGet('test')?>-str",
      $html,
    );
    $this->assertEquals(
      "'Hello '.Hamle\\Scope::get()->hamleGet('test').'-str'",
      $php,
    );
  }
  public function testDollarFormat1()
  {
    $hs = new Text("Hello {\$length|round(0)}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(Hamle\\Scope::get()->hamleGet('length'),0)?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(Hamle\\Scope::get()->hamleGet('length'),0)",
      $php,
    );
  }

  public function testDollarFormat2()
  {
    $hs = new Text("Hello {\$box->length|round(0)}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),0)?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),0)",
      $php,
    );
  }
  public function testDollarFormat3()
  {
    $hs = new Text("Hello {\$box->length|round}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'))",
      $php,
    );
  }
  public function testDollarFormat4()
  {
    $hs = new Text("Hello {\$box->length|json}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=json_encode(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.json_encode(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'))",
      $php,
    );
  }
  public function testDollarFormat5()
  {
    $hs = new Text("Hello {\$box->length|round({\$[-1]->decimals})}");
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hello <?=round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),Hamle\\Scope::get(-1)->hamleGet('decimals'))?>",
      $html,
    );
    $this->assertEquals(
      "'Hello '.round(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),Hamle\\Scope::get(-1)->hamleGet('decimals'))",
      $php,
    );
  }
  public function testDollarExplode1()
  {
    $hs = new Text('{$box->length|itersplit(";")}', Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "Seufert\\Hamle\\Text\\Filter::itersplit(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),';')",
      $php,
    );
  }
  public function testDollarExplode2()
  {
    $hs = new Text("{\$box->length|itersplit(',')}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "Seufert\\Hamle\\Text\\Filter::itersplit(Hamle\\Scope::get()->hamleGet('box')->hamleGet('length'),',')",
      $php,
    );
  }
  public function testDollarNewlineBr()
  {
    $hs = new Text("{\$desc|newlinebr}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "Seufert\\Hamle\\Text\\Filter::newlinebr(Hamle\\Scope::get()->hamleGet('desc'))",
      $php,
    );
  }
  public function testDollarUrlQuery()
  {
    $hs = new Text("{\$url|strtoupper('hash={\$[-2]->hash}')}");
    $php = $hs->toPHP();
    $this->assertEquals(
      "strtoupper(Hamle\Scope::get()->hamleGet('url'),'hash='.Hamle\Scope::get(-2)->hamleGet('hash'))",
      $php,
    );
  }
  public function testDollarNewlineJson()
  {
    $hs = new Text("{\$desc|newlinebr|json}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "json_encode(Seufert\\Hamle\\Text\\Filter::newlinebr(Hamle\\Scope::get()->hamleGet('desc')))",
      $php,
    );
  }

  public function testAsCents()
  {
    $hs = new Text("{\$price|ascents}", Text::TOKEN_CONTROL);
    $php = $hs->toPHP();
    $this->assertEquals(
      "Seufert\\Hamle\\Text\\Filter::ascents(Hamle\\Scope::get()->hamleGet('price'))",
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
      "\"My Title = <?=Hamle\\Scope::get()->hamleGet('title')?>\"",
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
    $this->assertEquals("Hamle\\Scope::getName('user')", $php);
  }

  public function testDollarScope2()
  {
    $hs = new Text("\$[layout]->view", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Scope::getName('layout')->hamleGet('view')",
      $php,
    );
  }

  public function testDollarFunc1()
  {
    $hs = new Text("\$(user)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('user'=>array()),array(),0,0)",
      $php,
    );
  }

  public function testDollarFunc2()
  {
    $hs = new Text("\$(user#3)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeId(array('user'=>array(0=>3)),array(),0,0)",
      $php,
    );
  }
  public function testDollarFunc3()
  {
    $hs = new Text("\$(#my_page)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('my_page',array(),0,0)", $php);
  }
  public function testDollarFunc4()
  {
    $hs = new Text("\$(page:4)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('page'=>array()),array(),4,0)",
      $php,
    );
  }
  public function testDollarFunc5()
  {
    $hs = new Text("\$(page^title:1-3)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('page'=>array()),array('title'=>2),3,1)",
      $php,
    );
  }
  public function testDollarFunc6()
  {
    $hs = new Text("\$(photo.heroimage^:1)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('photo'=>array(0=>'heroimage')),array(''=>4),1,0)",
      $php,
    );
  }
  public function testDollarFunc7()
  {
    $hs = new Text("\$(.hero-image^:1)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('*'=>array(0=>'hero-image')),array(''=>4),1,0)",
      $php,
    );
  }
  public function testDollarFunc8()
  {
    $hs = new Text("\$(#_ga)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals("Hamle\\Run::modelId('_ga',array(),0,0)", $php);
  }
  public function testDollarFuncChild1()
  {
    $hs = new Text("\$(#my_page > link)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelId('my_page',array(),0,0)" .
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
      "Hamle\\Run::modelId('my_page',array(),0,0)" .
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
      "Hamle\\Run::modelId('menu',array(),0,0)" .
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
      "Hamle\\Run::modelId('heroimage',array(),0,0)" .
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
      'Hamle\\Scope::get()->' . "hamleRel(1,array('*'=>array()),array(),0,0,2)",
      $php,
    );
  }
  public function testDollarFuncChild7()
  {
    $hs = new Text("\$(  >  image@2)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      'Hamle\\Scope::get()->' .
        "hamleRel(1,array('image'=>array()),array(),0,0,2)",
      $php,
    );
  }
  public function testDollarFuncSort1()
  {
    $hs = new Text("\$(testim.featured^-sorder^title)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Run::modelTypeTags(array('testim'=>array(0=>'featured')),array('sorder'=>3,'title'=>2),0,0)",
      $php,
    );
  }
  public function testDollarFuncParent1()
  {
    $hs = new Text("\$( < cat)", Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      'Hamle\\Scope::get()->' .
        "hamleRel(2,array('cat'=>array()),array(),0,0,0)",
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
      "Hamle\\Scope::get()->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testNested2()
  {
    $hs = new Text('$($[-1] > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Scope::get(-1)->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }
  public function testNestedNamed()
  {
    $hs = new Text('$($[prev] > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Scope::getName('prev')->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  public function testNestedNamed2()
  {
    $hs = new Text('$($[prev] > next > next)', Text::TOKEN_CONTROL);
    $html = $hs->toHTML();
    $php = $hs->toPHP();
    $this->assertEquals(
      "Hamle\\Scope::getName('prev')->hamleRel(1,array('next'=>array()),array(),0,0,0)->hamleRel(1,array('next'=>array()),array(),0,0,0)",
      $php,
    );
  }

  //  public function testDollarFuncVar1() {
  //    $hs = new Text('$(product.{$tags})', Text::TOKEN_CONTROL);
  //    $php = $hs->toPHP();
  //    $this->assertEquals("Hamle\\Run::modelTypeTags(array('product'=>array(0=>Hamle\\Scope::get()->hamleGet('tags'))),array(),0,0)", $php);
  //  }

  public function testFilterFunc()
  {
    $oldFR = Text\Filter::$filterResolver;
    Text\Filter::$filterResolver = static function ($s) {
      return $s == 'format_date' ? 'Test::formatDate' : null;
    };
    $hs = new Text('Date {$date|format_date}');
    $php = $hs->toPHP();
    $this->assertEquals(
      "'Date '.Test::formatDate(Hamle\Scope::get()->hamleGet('date'))",
      $php,
    );
    $hs = new Text('Date {$date|format_date("Y-m-d")}');
    $php = $hs->toPHP();
    $this->assertEquals(
      "'Date '.Test::formatDate(Hamle\Scope::get()->hamleGet('date'),'Y-m-d')",
      $php,
    );
    Text\Filter::$filterResolver = $oldFR;
  }
}
