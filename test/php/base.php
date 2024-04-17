<?php
require_once __DIR__ . '/../../php/autoload.php';

use Seufert\Hamle\Field\Button;
use Seufert\Hamle\Field\Memo;
use Seufert\Hamle\Hamle;
use Seufert\Hamle as H;
use Seufert\Hamle\Field;
use Seufert\Hamle\Model\WrapArray;
use Seufert\Hamle\Runtime\Scope;

class base extends \PHPUnit\Framework\TestCase
{
  /**
   * @var Hamle Hamle Parser
   */
  protected $hamle;
  public H\Scope $scope;
  public H\Runtime\Context $ctx;

  public function setUp(): void
  {
    $model = new WrapArray([
      [
        'url' => 'https://www.secure.com',
        'title' => 'This is My TITLE',
        'class' => 'colored',
        'empty' => '',
        'nottrue' => false,
        'csv' => 'a,b,c',
        'scsv' => 'a;b;c;',
        'unescaped' => 'Hi & >',
        'istrue' => true,
      ],
    ]);
    $this->hamle = new Hamle(($this->ctx = new baseTestSetup()));
    $this->scope = new H\Scope($model);
    parent::setUp();
  }

  public function compareXmlStrings($expected, $actual)
  {
    $this->assertXmlStringEqualsXmlString(
      '<root>' . $expected . '</root>',
      '<root>' . $actual . '</root>',
    );
    return;
    $docExpected = new DOMDocument();

    try {
      if (!$docExpected->loadXML($expected)) {
        $this->fail(
          "Couldn't load expected xml into DOMDocument. The xml was: $actual",
        );
      }
    } catch (Exception $ex) {
      $this->fail(
        "Couldn't load expected xml into DOMDocument. The xml was: $actual",
      );
    }

    $docActual = new DOMDocument();

    try {
      if (!$docActual->loadXML($actual)) {
        $this->fail(
          "Couldn't load actual xml into DOMDocument. The xml was: $actual",
        );
      }
    } catch (Exception $ex) {
      $this->fail(
        "Couldn't load actual xml into DOMDocument. The xml was: $actual",
      );
    }

    $differ = new XmlDiff($docExpected, $docActual);

    $delta = (string) $differ->diff();

    $this->assertEmpty($delta, "Differences found: $delta");
  }

  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();
    $cacheDir = __DIR__ . '/../../cache/';
    foreach (glob($cacheDir . '*') as $cacheFile) {
      unlink($cacheFile);
    }
  }
}

class baseTestSetup extends H\Setup implements H\Runtime\Context
{
  function hamleFindTypeTags(
    Scope $scope,
    array $typeTags,
    array $sort = [],
    int $limit = 0,
    int $offset = 0
  ): H\Model {
    if (in_array('basetest', array_keys($typeTags))) {
      return new WrapArray([
        ['url' => 'http://www.test.com', 'title' => 'Test.com'],
        ['url' => 'http://www.test2.com', 'title' => 'Test2.com'],
        ['url' => 'http://www.test3.com', 'title' => 'Test3.com'],
      ]);
    }
    if (in_array('formtest', array_keys($typeTags))) {
      return new WrapArray([
        ['title' => 'The Title', 'testform' => new formTestForm()],
      ]);
    }
    parent::hamleFindTypeTags($scope, $typeTags, $sort, $limit, $offset);
  }

  function templatePath($f)
  {
    return __DIR__ . '/' . $f;
  }

  public function getFragment(Hamle $hamle, $fragment): string
  {
    return "<frag>$fragment</frag>";
  }
}

class formTestForm extends H\Form
{
  function setup()
  {
    $this->_fields = [
      (new Field('title'))->required(true),
      (new Field('message'))->default('Message goes here'),
      (new Field('string'))->default("Tricky String '\""),
      (new Memo('memo'))->default("Some <Funky> Text\"'"),
      new Button('save'),
    ];
  }
}
