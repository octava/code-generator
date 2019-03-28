<?php
declare(strict_types=1);

namespace Octava\Tests\CodeGenerator\Processor\PhpClassProcessor;

use Octava\CodeGenerator\Exception\ConflictClassExtendsException;
use Octava\CodeGenerator\Exception\ConflictClassnameException;
use Octava\CodeGenerator\Exception\NotEqualNamespaceException;
use Octava\CodeGenerator\Processor\PhpClassProcessor\UpdateClassStatements;
use Octava\CodeGenerator\Processor\PhpClassProcessor\UpdateExtendsStatements;
use Octava\CodeGenerator\Processor\PhpClassProcessor\UpdateNamespaceStatements;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class UpdateNamespaceStatementsTest extends TestCase
{
    /**
     * @var UpdateExtendsStatements
     */
    protected $processor;
    /**
     * @var Parser
     */
    protected $parser;
    /**
     * @var PrettyPrinter\Standard
     */
    protected $printer;

    public function testNamespaceClassStatementsWithoutTemplateNamespace(): void
    {
        $this->expectException(NotEqualNamespaceException::class);

        $originSource = <<<'PHP'
<?php
namespace App;

class Classname
{
}
PHP;

        $templateSource = <<<'PHP'
<?php

class Classname
{
}
PHP;

        $this->processor->__invoke($this->parser->parse($originSource), $this->parser->parse($templateSource));
    }

    public function testNamespaceClassStatementsWithoutOriginClass(): void
    {
        $this->expectException(NotEqualNamespaceException::class);

        $originSource = <<<'PHP'
<?php

class Classname
{
}
PHP;

        $templateSource = <<<'PHP'
<?php
namespace App;

class Classname
{
}
PHP;

        $this->processor->__invoke($this->parser->parse($originSource), $this->parser->parse($templateSource));
    }

    public function testNamespaceClassStatementsSameNamespace(): void
    {
        $originSource = <<<'PHP'
<?php
namespace App;

class Classname
{
}
PHP;

        $templateSource = <<<'PHP'
<?php
namespace App;

class Classname
{
}
PHP;

        $expectedSource = <<<'PHP'
namespace App;

class Classname
{
}
PHP;

        $actualSourceStmts = $this->processor->__invoke($this->parser->parse($originSource), $this->parser->parse($templateSource));
        $actualSource = $this->printer->prettyPrint($actualSourceStmts);
        $this->assertEquals($expectedSource, $actualSource);
    }

    public function testNamespaceClassStatementsConflict(): void
    {
        $this->expectException(NotEqualNamespaceException::class);

        $originSource = <<<'PHP'
<?php
namespace Origin;

class Classname
{
}
PHP;

        $templateSource = <<<'PHP'
<?php
namespace Template;

class Classname
{
}
PHP;

        $this->processor->__invoke($this->parser->parse($originSource), $this->parser->parse($templateSource));
    }


    protected function setUp(): void
    {
        $logger = new NullLogger();
        $this->printer = new PrettyPrinter\Standard();
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->processor = new UpdateNamespaceStatements($logger, $this->parser);
    }
}
