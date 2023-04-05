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

use Behat\Mink\Driver\CoreDriver;
use Behat\Mink\Exception\DriverException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal
 */
final class HtmlDriver extends CoreDriver
{
    private Crawler $crawler;

    /** @var Form[] */
    private array $forms = [];

    public function __construct(private string $html)
    {
        $this->crawler = new Crawler($this->html);
    }

    public function getContent(): string
    {
        return $this->html;
    }

    public function getOuterHtml($xpath): string
    {
        return $this->filteredCrawler($xpath)->outerHtml();
    }

    public function getText($xpath): string
    {
        return \trim($this->filteredCrawler($xpath)->text());
    }

    public function getAttribute($xpath, $name): ?string
    {
        $node = $this->filteredCrawler($xpath);

        if ($this->crawlerNode($node)->hasAttribute($name)) {
            return $node->attr($name);
        }

        return null;
    }

    public function getValue($xpath): array|bool|string|null
    {
        if (\in_array($this->getAttribute($xpath, 'type'), ['submit', 'image', 'button'], true)) {
            return $this->getAttribute($xpath, 'value');
        }

        $node = $this->crawlerNode($this->filteredCrawler($xpath));

        if ('option' === $node->tagName) {
            return $this->optionValue($node);
        }

        try {
            $field = $this->formField($xpath);
        } catch (\InvalidArgumentException $e) {
            return $this->getAttribute($xpath, 'value');
        }

        $value = $field->getValue();

        if ('select' === $node->tagName && null === $value) {
            // symfony/dom-crawler returns null as value for a non-multiple select without
            // options but we want an empty string to match browsers.
            $value = '';
        }

        return $value;
    }

    public function isChecked($xpath): bool
    {
        $field = $this->formField($xpath);

        if (!$field instanceof ChoiceFormField || 'select' === $field->getType()) {
            throw new DriverException(\sprintf('Impossible to get the checked state of the element with XPath "%s" as it is not a checkbox or radio input', $xpath));
        }

        if ('checkbox' === $field->getType()) {
            return $field->hasValue();
        }

        $radio = $this->crawlerNode($this->filteredCrawler($xpath));

        return $radio->getAttribute('value') === $field->getValue();
    }

    public function getCurrentUrl(): string
    {
        return 'http://localhost';
    }

    protected function findElementXpaths($xpath): array
    {
        $nodes = $this->crawler()->filterXPath($xpath);
        $elements = [];

        foreach ($nodes as $i => $node) {
            $elements[] = \sprintf('(%s)[%d]', $xpath, $i + 1);
        }

        return $elements;
    }

    /**
     * Returns a crawler filtered for the given XPath, requiring at least 1 result.
     *
     * @throws DriverException when no matching elements are found
     */
    private function filteredCrawler(string $xpath): Crawler
    {
        if (!\count($crawler = $this->crawler()->filterXPath($xpath))) {
            throw new DriverException(\sprintf('There is no element matching XPath "%s"', $xpath));
        }

        return $crawler;
    }

    /**
     * Returns DOMElement from crawler instance.
     *
     * @throws DriverException when the node does not exist
     */
    private function crawlerNode(Crawler $crawler): \DOMElement
    {
        $node = $crawler->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new DriverException('The element does not exist');
        }

        return $node;
    }

    /**
     * Gets the value of an option element.
     *
     * @see ChoiceFormField::buildOptionValue()
     */
    private function optionValue(\DOMElement $option): string
    {
        if ($option->hasAttribute('value')) {
            return $option->getAttribute('value');
        }

        if (!empty($option->nodeValue)) {
            return $option->nodeValue;
        }

        return '1'; // DomCrawler uses 1 by default if there is no text in the option
    }

    private function formField(string $xpath): FormField
    {
        $fieldNode = $this->crawlerNode($this->filteredCrawler($xpath));
        $fieldName = \str_replace('[]', '', $fieldNode->getAttribute('name'));

        $form = $this->formForFieldNode($fieldNode);

        if (\is_array($form[$fieldName])) {
            return $form[$fieldName][$this->fieldPosition($fieldNode)]; // @phpstan-ignore-line
        }

        return $form[$fieldName];
    }

    private function formForFieldNode(\DOMElement $fieldNode): Form
    {
        $formNode = $this->formNode($fieldNode);
        $formId = $this->formNodeId($formNode);

        if (!isset($this->forms[$formId])) {
            $this->forms[$formId] = new Form($formNode, $this->getCurrentUrl());
        }

        return $this->forms[$formId];
    }

    /**
     * Returns form node unique identifier.
     */
    private function formNodeId(\DOMElement $form): string
    {
        return \md5($form->getLineNo().$form->getNodePath().$form->nodeValue);
    }

    /**
     * @throws DriverException if the form node cannot be found
     */
    private function formNode(\DOMElement $element): \DOMElement
    {
        if ($element->hasAttribute('form')) {
            $formId = $element->getAttribute('form');

            if (!$element->ownerDocument) {
                throw new DriverException(\sprintf('The selected node has an invalid form attribute (%s).', $formId));
            }

            $formNode = $element->ownerDocument->getElementById($formId);

            if (null === $formNode || 'form' !== $formNode->nodeName) {
                throw new DriverException(\sprintf('The selected node has an invalid form attribute (%s).', $formId));
            }

            return $formNode;
        }

        $formNode = $element;

        do {
            // use the ancestor form element
            if (null === $formNode = $formNode->parentNode) {
                throw new DriverException('The selected node does not have a form ancestor.');
            }
        } while ('form' !== $formNode->nodeName);

        return $formNode; // @phpstan-ignore-line
    }

    /**
     * Gets the position of the field node among elements with the same name.
     *
     * BrowserKit uses the field name as index to find the field in its Form object.
     * When multiple fields have the same name (checkboxes for instance), it will return
     * an array of elements in the order they appear in the DOM.
     */
    private function fieldPosition(\DOMElement $fieldNode): int
    {
        $elements = $this->crawler()->filterXPath('//*[@name=\''.$fieldNode->getAttribute('name').'\']');

        if (\count($elements) > 1) {
            // more than one element contains this name !
            // so we need to find the position of $fieldNode
            foreach ($elements as $key => $element) {
                /** @var \DOMElement $element */
                if ($element->getNodePath() === $fieldNode->getNodePath()) {
                    return $key;
                }
            }
        }

        return 0;
    }

    private function crawler(): Crawler
    {
        return $this->crawler;
    }
}
