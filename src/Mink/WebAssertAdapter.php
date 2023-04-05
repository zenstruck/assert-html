<?php

/*
 * This file is part of the zenstruck/assert-html package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Assert\Mink;

use Behat\Mink\WebAssert;
use Zenstruck\Assert;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 *
 * @mixin WebAssert
 */
final class WebAssertAdapter
{
    public function __construct(private WebAssert $webAssert)
    {
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return Assert::try(fn() => $this->webAssert->{$name}(...$arguments));
    }
}
