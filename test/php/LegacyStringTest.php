<?php

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
    ];
  }
}
