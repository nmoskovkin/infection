<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Mutator\Removal;

use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class MethodCallRemovalChain extends Mutator
{
    public function mutate(Node $node)
    {
        $count = $this->getCountOfMethodCalls($node->expr);
        for ($i = 0; $i < $count; $i++) {
            $paramsToRemove = [];
            $expr = $this->remove($node->expr, $i, $paramsToRemove);
            foreach ($paramsToRemove as $removingParameter) {
                $expr = $this->removeParameter($expr, $removingParameter);
            }
            yield new Node\Stmt\Expression($expr);
        }
    }

    private function removeParameter($expr, string $name)
    {
        $expr1 = clone $expr;

        if ($expr1 instanceof Node\Expr\MethodCall
            && $expr1->name->name === 'setParameter'
            && !empty($expr1->args[0]->value->value)
            && is_string($expr1->args[0]->value->value)
            && $expr1->args[0]->value->value === $name
        ) {
            return $expr1->var;
        } elseif (property_exists($expr1, 'var')) {
            $expr1->var = $this->removeParameter($expr1->var, $name);
        }


        return $expr1;
    }

    private function remove($expr, $n, &$paramsToRemove, $currentN = 0, $skip = 0)
    {
        $expr1 = clone $expr;

        if ($expr1 instanceof Node\Expr\MethodCall && in_array($expr1->name, $this->getMethods())) {
            if ($currentN === $n) {
                if (in_array($expr1->name, ['where', 'orWhere', 'andWhere'])) {
                    $query = '';
                    if (!empty($expr1->args[0]->value->value) && is_string($expr1->args[0]->value->value)) {
                        $query = $expr1->args[0]->value->value;
                    }
                    if ($query && \Safe\preg_match_all('/:\w+/', $query, $m) && !empty($m[0]) && is_array($m[0])) {
                        foreach ($m[0] as $v) {
                            $paramsToRemove[] = substr($v, 1);
                        }
                    }
                }

                return $this->remove($expr1->var, $n, $paramsToRemove, $currentN + 1, 1);
            }
            if ($skip > 0) {
                $skip--;
            }
            $expr1->var = $this->remove($expr1->var, $n, $paramsToRemove, $currentN + 1, $skip);
        } elseif (property_exists($expr1, 'var')) {
            $expr1->var = $this->remove($expr1->var, $n, $paramsToRemove, $currentN, $skip);
        }

        return $expr1;
    }

    protected function getCountOfMethodCalls($expr)
    {
        if (!$expr instanceof Node\Expr\MethodCall
            && property_exists($expr, 'var')
        ) {
            return $this->getCountOfMethodCalls($expr->var);
        }

        if (!$expr instanceof Node\Expr\MethodCall) {
            return 0;
        }

        if (!in_array($expr->name, $this->getMethods())) {
            return $this->getCountOfMethodCalls($expr->var);
        }

        return 1 + $this->getCountOfMethodCalls($expr->var);
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Expression) {
            return false;
        }
        $className = $this->getClassName($node);
        if (!is_string($className) || (strpos($className, 'Test') === false && strpos(
                    $className,
                    'Repository'
                ) === false)) {
            return false;
        }

        return $this->getCountOfMethodCalls($node->expr) > 0;
    }

    protected function getClassName(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            if ($node->name instanceof Node\Identifier) {
                return $node->name->name;
            }
        }

        if (!empty($node->getAttributes()['parent'])) {
            return $this->getClassName($node->getAttributes()['parent']);
        }
    }

    protected function getMethods(): array
    {
        return [
            'andWhere',
            'where',
            'orWhere',
            'orderBy',
            'setMaxResults',
        ];
    }
}
