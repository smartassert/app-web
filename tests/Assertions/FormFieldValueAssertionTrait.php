<?php

declare(strict_types=1);

namespace App\Tests\Assertions;

use PHPUnit\Framework\Assert;
use Symfony\Component\DomCrawler\Crawler;

trait FormFieldValueAssertionTrait
{
    public function assertFormFieldValue(Crawler $field, string $expected): void
    {
        if ('select' === $field->nodeName()) {
            Assert::assertSame($expected, $field->filter('option[selected]')->attr('value'));
        }

        if ('textarea' === $field->nodeName()) {
            Assert::assertSame($expected, $field->html());
        }

        if ('input' === $field->nodeName() && 'text' === $field->attr('type')) {
            Assert::assertSame($expected, $field->attr('value'));
        }
    }
}
