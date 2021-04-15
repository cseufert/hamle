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

  public function complexProvider(): array
  {
    return [
      [
        "Hamle\Run::modelTypeTags(array('page'=>array()),array(),0,0)",
        '$(page)',
      ],
      ["Hamle\Scope::getName('user')", '$[user]', ''],
      ['Hamle\Scope::get(-2)', '$[-2]', ''],
      [
        "Hamle\Run::modelTypeTags(array('page'=>array(0=>'test')),array(),1,0)",
        '$(page.test:1)',
      ],
      [
        "Hamle\Scope::getName('user')->hamleRel(1,array('application'=>array(0=>'current')),array(),1,0,5)",
        '$($[user] > application.current:1@5)',
      ],
      [
        "Hamle\Run::modelTypeTags(array('page'=>array()),array(),0,0)->hamleGet('title')",
        '$(page)->title',
      ],
      [
        "strtoupper(Hamle\Run::modelTypeTags(array('page'=>array()),array(),0,0)->hamleGet('code'))",
        '$(page)->code|strtoupper',
      ],
    ];
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

  public function compareProvider()
  {
    return [
      ["Hamle\Scope::get()->hamleGet('code') == 'Test'", '$code equals Test'],
      [
        "Hamle\Scope::get()->hamleGet('code') != 'Test'",
        '$code notequals Test',
      ],
      [
        "strpos(Hamle\Scope::get()->hamleGet('code'), 'Test') === 0",
        '$code starts Test',
      ],
      [
        "strpos(Hamle\Scope::get()->hamleGet('code'), 'Test') !== FALSE",
        '$code contains Test',
      ],
      [
        "substr(Hamle\Scope::get()->hamleGet('code'), -strlen('Test')) === 'Test'",
        '$code ends Test',
      ],
      ["Hamle\Scope::get()->hamleGet('price') < 4", '$price less 4'],
      ["Hamle\Scope::get()->hamleGet('price') > 5", '$price greater 5'],
      ["Hamle\Scope::get()->hamleGet('price') > 5", '$price greater 5'],
    ];
  }
}
