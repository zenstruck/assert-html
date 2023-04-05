<?php

/*
 * This file is part of the zenstruck/assert-html package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Assert;

use Behat\Mink\Session;
use Zenstruck\Assert\Mink\HtmlDriver;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class HtmlExpectation
{
    use HtmlAssertions;

    private Session $session;

    public function __construct(string $html)
    {
        $this->session = new Session(new HtmlDriver($html));
    }

    private function session(): Session
    {
        return $this->session;
    }
}
