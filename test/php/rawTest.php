<?php

use Seufert\Hamle\Exception\ParseError;
use Seufert\Hamle\Model\One;
use Seufert\Hamle\Model\WrapArray;
use Seufert\Hamle\Scope;
use Seufert\Hamle\Text;
use Seufert\Hamle\Text\Complex;
use Seufert\Hamle\Text\Filter;
use Seufert\Hamle\Text\SimpleVar;

require_once 'base.php';

class rawTest extends base
{
  private const HTML = '<strong title="A&B">Hi &amp; {$title}</strong>';

  public function setUp(): void
  {
    parent::setUp();
    $this->hamle->baseModel = new WrapArray([
      [
        'html' => self::HTML,
        'title' => '<em>A&B</em>',
        'placeholder' => 'x',
        'lines' => "one\ntwo",
        'child' => new WrapArray([['html' => self::HTML]]),
      ],
    ]);
  }

  /** @dataProvider templateProvider */
  public function testTemplate(string $template, string $expected): void
  {
    $this->hamle->string($template);
    $this->assertSame($expected, $this->hamle->output());
  }

  public static function templateProvider(): array
  {
    $escaped = htmlspecialchars(self::HTML);
    $title = '&lt;em&gt;A&amp;B&lt;/em&gt;';
    return [
      'default escaping' => ['div {$html}', '<div>' . $escaped . '</div>'],
      'raw content' => ['div {$html|raw}', '<div>' . self::HTML . '</div>'],
      'text tag' => ['_ {$html|raw}', self::HTML],
      'mixed content' => [
        'p < {$title} {$html|raw} {$title} >',
        '<p>&lt; ' . $title . ' ' . self::HTML . ' ' . $title . ' &gt;</p>',
      ],
      'adjacent expressions' => [
        'p {$title}{$html|raw}{$title}',
        '<p>' . $title . self::HTML . $title . '</p>',
      ],
      'adjacent raw expressions' => [
        'p {$html|raw}{$html|raw}',
        '<p>' . self::HTML . self::HTML . '</p>',
      ],
      'selector' => ['div {$child->html|raw}', '<div>' . self::HTML . '</div>'],
      'scope selector' => [
        'div {$[0]->html|raw}',
        '<div>' . self::HTML . '</div>',
      ],
      'preceding filter' => [
        'div {$lines|newlinebr|raw}',
        "<div>one<br />\ntwo</div>",
      ],
      'preceding filters with arguments' => [
        'div {$html|replace("Hi","Bye")|strtoupper|raw}',
        '<div>' . strtoupper(str_replace('Hi', 'Bye', self::HTML)) . '</div>',
      ],
      'attribute escaping' => [
        'div[data-html={$html|raw}] {$html|raw}',
        '<div data-html="' . $escaped . '">' . self::HTML . '</div>',
      ],
      'nested raw does not disable outer escaping' => [
        'div {$placeholder|replace("x","{$html|raw}")}',
        '<div>' . $escaped . '</div>',
      ],
      'existing raw text tag' => ['___ {$html}', self::HTML],
      'existing literal HTML tag' => [
        '__ <b>{$html|raw}</b>',
        '<b>{$html|raw}</b>',
      ],
    ];
  }

  public function testNamedScope(): void
  {
    $namedScopes = Scope::$namedScopes;
    try {
      Scope::add($this->hamle->baseModel, 'raw_test');
      $this->hamle->string('div {$[raw_test]->html|raw}');
      $this->assertSame(
        '<div>' . self::HTML . '</div>',
        $this->hamle->output(),
      );
    } finally {
      Scope::$namedScopes = $namedScopes;
    }
  }

  /** @dataProvider expressionProvider */
  public function testCompiledOutput(string $class, string $input): void
  {
    $text = new $class($input);
    $plain = new $class($class === Text::class ? '{$value}' : '$value');
    $this->assertSame($plain->toPHP(), $text->toPHP());
    $this->assertSame(
      '<?=htmlspecialchars(' . $text->toPHP() . ')?>',
      $text->toHTMLAtt(),
    );
    $this->assertSame('<?=' . $text->toPHP() . '?>', $text->toHTML(true));
  }

  /** @dataProvider expressionProvider */
  public function testValuesKeepTheirTypes(string $class, string $input): void
  {
    $stringable = new class implements Stringable {
      public function __toString(): string
      {
        return '<em>HTML &amp;</em>';
      }
    };
    foreach ([self::HTML, '', null, 0, false, true, $stringable] as $value) {
      $model = new class ($value) extends One {
        public function __construct(private mixed $value) {}

        public function hamleGet(string $key): mixed
        {
          return $this->value;
        }
      };
      Scope::add($model);
      try {
        $text = new $class($input);
        $this->assertSame($value, $text->doEval());
        ob_start();
        try {
          eval('use Seufert\Hamle; ?>' . $text->toHTML(true));
          $this->assertSame((string) $value, ob_get_contents());
        } finally {
          ob_end_clean();
        }
      } finally {
        Scope::done();
      }
    }
  }

  public static function expressionProvider(): array
  {
    return [
      [Text::class, '{$value|raw}'],
      [Complex::class, '$value|raw'],
      [SimpleVar::class, '$value|raw'],
    ];
  }

  /** @dataProvider invalidExpressionProvider */
  public function testInvalidModifier(string $class, string $input): void
  {
    $this->expectException(ParseError::class);
    new $class($input);
  }

  public static function invalidExpressionProvider(): array
  {
    $cases = [];
    foreach ([Text::class, Complex::class, SimpleVar::class] as $class) {
      foreach (
        ['raw()', 'raw("x")', 'raw|strtoupper', 'raw|raw', 'raw->title']
        as $suffix
      ) {
        $input = '$value|' . $suffix;
        $cases[] = [
          $class,
          $class === Text::class ? '{' . $input . '}' : $input,
        ];
      }
    }
    return $cases;
  }

  public function testRawPrefixDoesNotReserveOtherFilters(): void
  {
    $resolver = Filter::$filterResolver;
    Filter::$filterResolver = static fn(string $name): ?string => $name ===
    'raw_html'
      ? 'strtoupper'
      : null;
    try {
      $this->hamle->string('div {$title|raw_html}');
      $this->assertSame(
        '<div>&lt;EM&gt;A&amp;B&lt;/EM&gt;</div>',
        $this->hamle->output(),
      );
    } finally {
      Filter::$filterResolver = $resolver;
    }
  }

  public function testLegacyFilterChain(): void
  {
    foreach ([Complex::class, SimpleVar::class] as $class) {
      $text = new $class('$html|strtoupper|raw');
      $plain = new $class('$html|strtoupper');
      $this->assertSame($plain->toPHP(), $text->toPHP());
      $this->assertSame('<?=' . $plain->toPHP() . '?>', $text->toHTML(true));
      $this->assertSame(
        '<?=htmlspecialchars(' . $plain->toPHP() . ')?>',
        $text->toHTMLAtt(),
      );
    }
  }
}
