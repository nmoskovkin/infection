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

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

final class MultiplicationTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates normal multiplication' => [
                <<<'PHP'
<?php

$a = 10 * 3;
PHP
                ,
                <<<'PHP'
<?php

$a = 10 / 3;
PHP
        ];

        yield 'It does not mutate multiplication equals' => [
                <<<'PHP'
<?php

$a = 1;
$a *= 2;
PHP
        ];

        yield 'It does not mutate when the left side is 1 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = 1 * $b;
PHP
        ];

        yield 'It does not mutate when the right side is 1 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = $b * 1;
PHP
        ];

        yield 'It does not mutate when the left side is -1 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = -1 * $b;
PHP
        ];

        yield 'It does not mutate when the right side is -1 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = $b * -1;
PHP
        ];

        yield 'It does not mutate when the left side is 1.0 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = 1.0 * $b;
PHP
        ];

        yield 'It does not mutate when the right side is 1.0 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = $b * 1.0;
PHP
        ];

        yield 'It does not mutate when the left side is -1.0 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = -1.0 * $b;
PHP
        ];

        yield 'It does not mutate when the right side is -1.0 to avoid an equivalent mutation' => [
            <<<'PHP'
<?php

$a = $b * -1.0;
PHP
        ];
    }
}
