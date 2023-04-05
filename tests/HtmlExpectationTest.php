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
            ->see('h1 title')
            ->notSee('invalid text')
            ->seeIn('h1', 'title')
            ->notSeeIn('h1', 'invalid text')
            ->seeElement('h1')
            ->notSeeElement('h2')
            ->elementCount('ul li', 2)

            // head assertions
            ->seeIn('title', 'meta title')
            ->elementAttributeContains('meta[name="description"]', 'content', 'meta')
            ->elementAttributeNotContains('meta[name="description"]', 'content', 'invalid')
            ->elementAttributeContains('html', 'lang', 'en')

            // form assertions
            ->FieldEquals('Input 1', 'input 1')
            ->FieldEquals('input1', 'input 1')
            ->FieldEquals('input_1', 'input 1')
            ->FieldNotEquals('Input 1', 'invalid')
            ->FieldNotEquals('input1', 'invalid')
            ->FieldNotEquals('input_1', 'invalid')
            ->Checked('Input 3')
            ->Checked('input3')
            ->Checked('input_3')
            ->NotChecked('Input 2')
            ->NotChecked('input2')
            ->NotChecked('input_2')
            ->selected('Input 4', 'option 1')
            ->selected('input4', 'option 1')
            ->selected('input_4', 'option 1')
            ->selected('Input 7', 'option 1')
            ->selected('input7', 'option 1')
            ->selected('input_7[]', 'option 1')
            ->selected('Input 7', 'option 3')
            ->selected('input7', 'option 3')
            ->selected('input_7[]', 'option 3')
            ->notSelected('Input 4', 'option 2')
            ->notSelected('input4', 'option 2')
            ->notSelected('input_4', 'option 2')
            ->notSelected('Input 7', 'option 2')
            ->notSelected('input7', 'option 2')
            ->notSelected('input_7[]', 'option 2')
            ->notSelected('input_8', 'option 1')
            ->selected('input_8', 'option 2')
            ->notChecked('Radio 1')
            ->notChecked('radio1')
            ->notChecked('Radio 3')
            ->notChecked('radio3')
            ->checked('Radio 2')
            ->checked('radio2')
        ;
    }

    /**
     * @test
     */
    public function failed_assertion(): void
    {
        $expectation = new HtmlExpectation(\file_get_contents(__DIR__.'/Fixtures/test.html'));

        Assert::that(fn() => $expectation->seeIn('h1', 'invalid'))
            ->throws(AssertionFailedError::class)
        ;
    }
}
