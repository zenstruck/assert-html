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
use Behat\Mink\WebAssert;
use Zenstruck\Assert;
use Zenstruck\Assert\Mink\WebAssertAdapter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait HtmlAssertions
{
    final public function contains(string $expected): static
    {
        $this->webAssert()->pageTextContains($expected);

        return $this;
    }

    final public function doesNotContain(string $expected): static
    {
        $this->webAssert()->pageTextNotContains($expected);

        return $this;
    }

    final public function containsIn(string $selector, string $expected): static
    {
        $this->webAssert()->elementTextContains('css', $selector, $expected);

        return $this;
    }

    final public function doesNotContainIn(string $selector, string $expected): static
    {
        $this->webAssert()->elementTextNotContains('css', $selector, $expected);

        return $this;
    }

    final public function hasElement(string $selector): static
    {
        $this->webAssert()->elementExists('css', $selector);

        return $this;
    }

    final public function doesNotHaveElement(string $selector): static
    {
        $this->webAssert()->elementNotExists('css', $selector);

        return $this;
    }

    final public function hasElementCount(string $selector, int $count): static
    {
        $this->webAssert()->elementsCount('css', $selector, $count);

        return $this;
    }

    final public function attributeContains(string $selector, string $attribute, string $expected): static
    {
        $this->webAssert()->elementAttributeContains('css', $selector, $attribute, $expected);

        return $this;
    }

    final public function attributeDoesNotContain(string $selector, string $attribute, string $expected): static
    {
        $this->webAssert()->elementAttributeNotContains('css', $selector, $attribute, $expected);

        return $this;
    }

    final public function fieldEquals(string $selector, string $expected): static
    {
        $this->webAssert()->fieldValueEquals($selector, $expected);

        return $this;
    }

    final public function fieldDoesNotEqual(string $selector, string $expected): static
    {
        $this->webAssert()->fieldValueNotEquals($selector, $expected);

        return $this;
    }

    final public function fieldSelected(string $selector, string $expected): static
    {
        $field = $this->webAssert()->fieldExists($selector);

        Assert::that((array) $field->getValue())->contains($expected);

        return $this;
    }

    final public function fieldNotSelected(string $selector, string $expected): static
    {
        $field = $this->webAssert()->fieldExists($selector);

        Assert::that((array) $field->getValue())->doesNotContain($expected);

        return $this;
    }

    final public function fieldChecked(string $selector): static
    {
        $this->webAssert()->checkboxChecked($selector);

        return $this;
    }

    final public function fieldNotChecked(string $selector): static
    {
        $this->webAssert()->checkboxNotChecked($selector);

        return $this;
    }

    final public function dump(?string $selector = null): self
    {
        $dump = fn($what) => \function_exists('dump') ? dump($what) : \var_dump($what);

        if (!$selector) {
            $dump($this->session()->getDriver()->getContent());

            return $this;
        }

        $elements = $this->session()->getPage()->findAll('css', $selector);

        if (0 === \count($elements)) {
            throw new \RuntimeException("Element \"{$selector}\" not found.");
        }

        foreach ($elements as $element) {
            $dump($element->getOuterHtml());
        }

        return $this;
    }

    final public function dd(?string $selector = null): void
    {
        $this->dump($selector);

        exit;
    }

    private function webAssert(): WebAssertAdapter
    {
        return new WebAssertAdapter(new WebAssert($this->session()));
    }

    abstract private function session(): Session;
}
