<?php

/*
 * This file is part of the zenstruck/assert-html package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Assert\Tests;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Zenstruck\Assert;
use Zenstruck\Assert\HtmlExpectation;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class HtmlExpectationTest extends TestCase
{
    /**
     * @test
     */
    public function assertions(): void
    {
        (new HtmlExpectation(\file_get_contents(__DIR__.'/Fixtures/test.html')))
            ->contains('h1 title')
            ->doesNotContain('invalid text')
            ->containsIn('h1', 'title')
            ->doesNotContainIn('h1', 'invalid text')
            ->hasElement('h1')
            ->doesNotHaveElement('h2')
            ->hasElementCount('ul li', 2)

            // head assertions
            ->containsIn('title', 'meta title')
            ->attributeContains('meta[name="description"]', 'content', 'meta')
            ->attributeDoesNotContain('meta[name="description"]', 'content', 'invalid')
            ->attributeContains('html', 'lang', 'en')

            // form assertions
            ->FieldEquals('Input 1', 'input 1')
            ->FieldEquals('input1', 'input 1')
            ->FieldEquals('input_1', 'input 1')
            ->fieldDoesNotEqual('Input 1', 'invalid')
            ->fieldDoesNotEqual('input1', 'invalid')
            ->fieldDoesNotEqual('input_1', 'invalid')
            ->fieldChecked('Input 3')
            ->fieldChecked('input3')
            ->fieldChecked('input_3')
            ->fieldNotChecked('Input 2')
            ->fieldNotChecked('input2')
            ->fieldNotChecked('input_2')
            ->fieldSelected('Input 4', 'option 1')
            ->fieldSelected('input4', 'option 1')
            ->fieldSelected('input_4', 'option 1')
            ->fieldSelected('Input 7', 'option 1')
            ->fieldSelected('input7', 'option 1')
            ->fieldSelected('input_7[]', 'option 1')
            ->fieldSelected('Input 7', 'option 3')
            ->fieldSelected('input7', 'option 3')
            ->fieldSelected('input_7[]', 'option 3')
            ->fieldNotSelected('Input 4', 'option 2')
            ->fieldNotSelected('input4', 'option 2')
            ->fieldNotSelected('input_4', 'option 2')
            ->fieldNotSelected('Input 7', 'option 2')
            ->fieldNotSelected('input7', 'option 2')
            ->fieldNotSelected('input_7[]', 'option 2')
            ->fieldNotSelected('input_8', 'option 1')
            ->fieldSelected('input_8', 'option 2')
            ->fieldNotChecked('Radio 1')
            ->fieldNotChecked('radio1')
            ->fieldNotChecked('Radio 3')
            ->fieldNotChecked('radio3')
            ->fieldChecked('Radio 2')
            ->fieldChecked('radio2')
        ;
    }

    /**
     * @test
     */
    public function failed_assertion(): void
    {
        $expectation = new HtmlExpectation(\file_get_contents(__DIR__.'/Fixtures/test.html'));

        Assert::that(fn() => $expectation->containsIn('h1', 'invalid'))
            ->throws(AssertionFailedError::class)
        ;
    }
}
