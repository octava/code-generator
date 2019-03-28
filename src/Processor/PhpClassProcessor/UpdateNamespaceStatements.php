<?php
declare(strict_types=1);

namespace Octava\CodeGenerator\Processor\PhpClassProcessor;

use Octava\CodeGenerator\Exception\NotEqualNamespaceException;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Psr\Log\LoggerInterface;
use PhpParser\Parser;

class UpdateNamespaceStatements
{
    use GetClassTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Parser
     */
    protected $parser;

    public function __construct(LoggerInterface $logger, Parser $parser)
    {
        $this->logger = $logger;
        $this->parser = $parser;
    }

    /**
     * @param Stmt[] $originStmts
     * @param Stmt[] $templateStmts
     * @return Stmt[]
     * @throws NotEqualNamespaceException
     */
    public function __invoke(array $originStmts, array $templateStmts): array
    {
        $func = function (array $stmts): ?string {
            $result = null;
            foreach ($stmts as $stmt) {
                if ($stmt instanceof Namespace_) {
                    $result = $stmt->name->toString();
                    break;
                }
            }

            return $result;
        };
        $originNamespace = $func($originStmts);
        $templateNamespace = $func($templateStmts);

        if ($originNamespace !== $templateNamespace) {
            throw new NotEqualNamespaceException(
                sprintf('Origin namespace "%s" not equal to "%s"', $originNamespace, $templateNamespace)
            );
        }

        return $originStmts;
    }
}
