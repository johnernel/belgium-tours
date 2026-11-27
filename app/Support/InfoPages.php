<?php

namespace App\Support;

/**
 * Starter records for the three "About Us" menu pages (Meet Our Team, Testimonials, FAQs) and the sample team.
 * One source for the seeder (fresh installs) and the migration (existing databases such as the live site).
 * Nothing here is a business fact: the team members are placeholders until the client supplies real people.
 */
class InfoPages
{
    /** Banner photo and its description for the four pages in the About Us menu: the same photo each page has in that menu. */
    public const BANNERS = [
        'about' => ['images/photos/about-menu-village', 'A stone castle above a river beside a village and an arched bridge'],
        'team' => ['images/photos/about-menu-fens', 'A wooden boardwalk across misty moorland under a pale sky'],
        'testimonials' => ['images/photos/about-menu-namur', 'Aerial view of a hilltop citadel above two rivers and a town'],
        'faqs' => ['images/photos/about-menu-marsh', 'Two storks in a coastal marsh at sunset'],
    ];

    public static function pages(): array
    {
        return [
            'team' => [
                'title' => 'Meet Our Team',
                'seo_title' => 'Meet Our Team | Go Belgium Trips',
                'meta' => 'Meet the people who plan your Belgium trip and look after you along the way.',
                'noindex' => true, // sample team until real people are added
                'content' => [
                    'hero_headline' => 'Meet Our *Team*',
                    'hero_sub' => 'The people who plan your Belgium trip and look after you along the way.',
                    'hero_image' => self::BANNERS['team'][0],
                    'hero_image_alt' => self::BANNERS['team'][1],
                ],
            ],
            'testimonials' => [
                'title' => 'Testimonials',
                'seo_title' => 'Traveler Testimonials | Go Belgium Trips',
                'meta' => 'Read what travelers say about their Belgium trips with Go Belgium Trips.',
                'noindex' => false,
                'content' => [
                    'hero_headline' => 'What Travelers *Say About Us*',
                    'hero_sub' => 'Read what other travelers say about their Belgium trips.',
                    'hero_image' => self::BANNERS['testimonials'][0],
                    'hero_image_alt' => self::BANNERS['testimonials'][1],
                ],
            ],
            'faqs' => [
                'title' => 'FAQs',
                'seo_title' => 'Frequently Asked Questions | Go Belgium Trips',
                'meta' => 'Answers to common questions about quotes, tours and planning your Belgium trip.',
                'noindex' => false,
                'content' => [
                    'hero_headline' => 'Frequently Asked *Questions*',
                    'hero_sub' => 'Quick answers about quotes, tours and planning your trip.',
                    'hero_image' => self::BANNERS['faqs'][0],
                    'hero_image_alt' => self::BANNERS['faqs'][1],
                ],
            ],
        ];
    }

    public static function teamMembers(): array
    {
        return array_map(fn ($i) => [
            'name' => '[PLACEHOLDER: team member name]',
            'role' => '[PLACEHOLDER: role]',
            'bio' => '[PLACEHOLDER: a short, real introduction written by or approved by this team member.]',
            'sort' => $i,
            'is_placeholder' => true,
            'active' => true,
        ], [1, 2, 3]);
    }
}
