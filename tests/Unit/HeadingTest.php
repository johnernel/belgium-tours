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
        $this->assertSame('Every Journey, Personally <em class="heading-accent">Crafted.</em>', (string) Heading::html('Every journey, personally crafted.'));
    }

    public function test_headlines_are_capitalized_but_small_joining_words_stay_lower_case(): void
    {
        $this->assertSame('Three Ways to Travel Belgium', Heading::titleCase('three ways to travel belgium'));
        $this->assertSame('Belgium in Pictures', Heading::titleCase('Belgium in pictures'));
        $this->assertSame('Stories from the *Lowlands.*', Heading::titleCase('Stories from the *lowlands.*'));
        $this->assertSame('Questions, Answered', Heading::titleCase('Questions, answered'));
        $this->assertSame('The Best of Belgium: A Guide for You', Heading::titleCase('the best of belgium: a guide for you'));
        $this->assertSame('Self-Drive Tours of the USA', Heading::titleCase('self-drive tours of the USA'));
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
        $this->assertStringContainsString('&lt;script&gt;', strtolower($html)); // Capitalized Case may change the letter case, never the escaping
    }
}
