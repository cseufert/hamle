<?php

use Seufert\Hamle\Text\Comparison;
use Seufert\Hamle\Text\Complex;

require_once 'base.php';

class LegacyStringTest extends base
{
  /**
   * @dataProvider complexProvider
   * @param string $expect
   * @param string $input
   */
  public function testComplex(string $expect, string $input): void
  {
    $ct = new Complex($input);
    $this->assertEquals($expect, $ct->toPHP());
  }

  public static function complexProvider(): array
  {
    return [
      [
        "\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array(),0,0)",
        '$(page)',
      ],
      ["\$scope->namedModel('user')", '$[user]', ''],
      ['$scope->modelNum(-2)', '$[-2]', ''],
      [
        "\$ctx->hamleFindTypeTags(\$scope,array('page'=>array(0=>'test')),array(),1,0)",
        '$(page.test:1)',
      ],
      [
        "\$scope->namedModel('user')->hamleRel(1,array('application'=>array(0=>'current')),array(),1,0,5)",
        '$($[user] > application.current:1@5)',
      ],
      [
        "\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array(),0,0)->hamleGet('title')",
        '$(page)->title',
      ],
      [
        "strtoupper(\$ctx->hamleFindTypeTags(\$scope,array('page'=>array()),array(),0,0)->hamleGet('code'))",
        '$(page)->code|strtoupper',
      ],
      ["\$scope->model()->hamleGet('a')", '$a'],
    ];
  }

  /**
   * @dataProvider invalidComplexProvider
   * @param string $input
   * @throws \Seufert\Hamle\Exception\ParseError
   */
  public function testInvalidCompex(string $input): void
  {
    $this->expectException(\Seufert\Hamle\Exception\ParseError::class);
    $ct = new Complex($input);
  }

  public static function invalidComplexProvider()
  {
    return [['$(abc'], ['$blah|non(', ['{$a}']]];
  }
  /**
   * @dataProvider compareProvider
   * @param string $expect
   * @param string $input
   * @throws \Seufert\Hamle\Exception\Unimplemented
   */
  public function testCompare(string $expect, string $input): void
  {
    $ct = new Comparison($input);
    $this->assertEquals($expect, $ct->toPHP());
  }

  public static function compareProvider()
  {
    return [
      ["\$scope->model()->hamleGet('code') == 'Test'", '$code equals Test'],
      ["\$scope->model()->hamleGet('code') != 'Test'", '$code notequals Test'],
      [
        "strpos(\$scope->model()->hamleGet('code'), 'Test') === 0",
        '$code starts Test',
      ],
      [
        "strpos(\$scope->model()->hamleGet('code'), 'Test') !== FALSE",
        '$code contains Test',
      ],
      [
        "substr(\$scope->model()->hamleGet('code'), -strlen('Test')) === 'Test'",
        '$code ends Test',
      ],
      ["\$scope->model()->hamleGet('price') < 4", '$price less 4'],
      ["\$scope->model()->hamleGet('price') > 5", '$price greater 5'],
      ["\$scope->model()->hamleGet('price') > 5", '$price greater 5'],
    ];
  }
}
