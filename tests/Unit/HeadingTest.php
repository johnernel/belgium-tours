<?php

namespace Tests\Unit;

use App\Support\Heading;
use PHPUnit\Framework\TestCase;

class HeadingTest extends TestCase
{
    public function test_starred_part_becomes_the_accent(): void
    {
        $this->assertSame('Three Ways to <em class="heading-accent">Travel</em> Belgium', (string) Heading::html('Three Ways to *Travel* Belgium'));
    }

    public function test_without_stars_the_last_word_is_the_accent(): void
    {
        $this->assertSame('Every journey, personally <em class="heading-accent">crafted.</em>', (string) Heading::html('Every journey, personally crafted.'));
    }

    public function test_a_single_word_stays_plain(): void
    {
        $this->assertSame('Overview', (string) Heading::html('Overview'));
    }

    public function test_output_is_escaped(): void
    {
        $html = (string) Heading::html('<script>alert(1)</script> and *<b>bold</b>*');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<b>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
