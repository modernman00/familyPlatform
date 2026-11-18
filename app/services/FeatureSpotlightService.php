<?php

declare(strict_types=1);

namespace App\services;

/**
 * FeatureSpotlightService
 *
 * Curates entertaining, British-English in-feed Feature Spotlight cards
 * that introduce FamilyPlatform's capabilities in a warm, story-driven way.
 */
final class FeatureSpotlightService
{
    public const VALID_ACTIONS = ['dismiss', 'launch', 'shuffle'];

    /**
     * Return the full curated catalog of Feature Spotlights.
     *
     * @param string $userId
     * @return list<array<string, mixed>>
     */
    public static function getCatalog(string $userId = ''): array
    {
        $treeUrl = $userId !== '' ? '/organogram/' . rawurlencode($userId) . '?mode=canvas' : '/profilePage';

        return [
            [
                'id' => 'family-roundtable',
                'badge' => 'Friendly Settlement',
                'subtitle' => 'Family RoundTable',
                'title' => 'Need a family judge for a fun settlement with your brother, sister, or dad?',
                'story' => 'Someone borrowed your favourite jumper in 2023 and "forgot" to give it back? Or still arguing over who actually won Monopoly last Christmas? Bring the case before the Family RoundTable for a witty, fair, and peacemaking verdict.',
                'highlights' => [
                    'Submit both sides of a lighthearted household debate',
                    'Invite a neutral relative as peacemaker',
                    'Receive a warm, memorable Family RoundTable accord',
                ],
                'cta_label' => 'Convene RoundTable',
                'cta_url' => '/family/roundtable',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-shield-shaded',
                'theme' => 'indigo-gold',
            ],
            [
                'id' => 'family-hub',
                'badge' => 'Household Playground',
                'subtitle' => 'Family Hub',
                'title' => 'What can we cook from three random things in the fridge in 15 minutes?',
                'story' => 'Family Hub is your household creative playground — whip up quick fridge meal ideas, turn voice notes into neat summaries, design family flyers, or kick off a spontaneous Family Game Show.',
                'highlights' => [
                    '15-minute fridge rescue meal suggestions',
                    'Transcribed voice notes & illustrated family flyers',
                    'Built-in Family Game Show for cosy evenings',
                ],
                'cta_label' => 'Explore Family Hub',
                'cta_url' => '/family/hub',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-grid-fill',
                'theme' => 'plum-rose',
            ],
            [
                'id' => 'creative-studio',
                'badge' => 'Design & Keepsakes',
                'subtitle' => 'Creative Studio',
                'title' => 'Turn Sunday photos into proper family posters, festive cards, and keepsakes.',
                'story' => 'Why send a plain text when you can send a bespoke birthday flyer, an anniversary greeting card, or a printable heirloom poster in under two minutes?',
                'highlights' => [
                    'Design birthday & reunion flyers in seconds',
                    'Send personalised festive cards via WhatsApp or Messages',
                    'Build living family archives everyone can treasure',
                ],
                'cta_label' => 'Open Creative Studio',
                'cta_url' => '/creative-studio',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-brush-fill',
                'theme' => 'amber-crimson',
            ],
            [
                'id' => 'kinship-calculator',
                'badge' => 'Settle the Debate',
                'subtitle' => 'Kinship Calculator',
                'title' => 'Second cousin once removed, or first cousin twice removed? Let us settle it.',
                'story' => 'Every family gathering has that one moment where nobody quite knows how Uncle Tunde is related to Cousin Maya. Pick two relatives and get the exact relationship degree in one tap.',
                'highlights' => [
                    'Instant relationship degree & shared ancestor path',
                    'Clear, jargon-free explanation anyone can follow',
                    'Great icebreaker at weddings and family reunions',
                ],
                'cta_label' => 'Calculate Kinship',
                'cta_url' => '/kinship-calculator',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-calculator-fill',
                'theme' => 'royal-crimson',
            ],
            [
                'id' => 'family-tree',
                'badge' => 'Heritage & Roots',
                'subtitle' => 'Create Family Tree',
                'title' => 'Map out the whole clan — from Grandad’s generation to the newest arrivals.',
                'story' => 'Your family history shouldn’t live in scattered notebooks. Build an interactive, zoomable family tree where everyone can see how branches connect across generations.',
                'highlights' => [
                    'Interactive visual canvas with parents, spouses & children',
                    'Tap any relative to view their profile & memories',
                    'Automatically powers the Kinship Calculator',
                ],
                'cta_label' => 'View Family Tree',
                'cta_url' => $treeUrl,
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-diagram-3-fill',
                'theme' => 'emerald-teal',
            ],
            [
                'id' => 'avatar-studio',
                'badge' => 'Express Yourself',
                'subtitle' => '3D Avatar Studio',
                'title' => 'Create a 3D family persona and custom emojis that actually look like you.',
                'story' => 'Standard yellow emojis are fine, but reacting to Mum’s Sunday roast with your own custom 3D family sticker hits differently. Customise your look and drop them straight into comments.',
                'highlights' => [
                    'Style your 3D avatar with outfits & accessories',
                    'Generate bespoke reaction emojis for family chats',
                    'Use your custom stickers across posts and FamChat',
                ],
                'cta_label' => 'Enter Avatar Studio',
                'cta_url' => '/member/emoji-studio',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-palette-fill',
                'theme' => 'amber-crimson',
            ],
            [
                'id' => 'gift-wishlists',
                'badge' => 'No-Guesswork Gifting',
                'subtitle' => 'Gift Wishlists',
                'title' => 'Drop subtle birthday and Christmas gift hints — minus the awkward guesswork.',
                'story' => 'Tired of receiving three identical mugs or wondering what your nephew actually wants? Pin your wishlist items for your chosen days so relatives can claim gifts privately without spoiling the surprise.',
                'highlights' => [
                    'Pin birthday, Christmas, or milestone wishlists',
                    'Relatives can quietly reserve items to avoid duplicates',
                    'Works for both adults and little ones',
                ],
                'cta_label' => 'Browse Wishlists',
                'cta_url' => '/wishlists',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-gift-fill',
                'theme' => 'plum-rose',
            ],
            [
                'id' => 'then-and-now',
                'badge' => 'Time Travel',
                'subtitle' => 'Then & Now',
                'title' => 'Recreate that hilarious 1998 holiday photo and put it side by side with today.',
                'story' => 'Nothing brings a family feed to life faster than a Then & Now comparison. Pair a treasured vintage snapshot with a fresh recreation and watch the comments roll in.',
                'highlights' => [
                    'Side-by-side vintage vs modern photo comparison',
                    'Tag the relatives who were in the original shot',
                    'Start a Then & Now chain for siblings to join',
                ],
                'cta_label' => 'Try Then & Now',
                'cta_url' => '/profilePage#thenNowSection',
                'cta_action' => 'scroll',
                'cta_target' => 'thenNowSection',
                'icon' => 'bi-hourglass-split',
                'theme' => 'royal-crimson',
            ],
            [
                'id' => 'family-journal',
                'badge' => 'Private Heirloom',
                'subtitle' => 'Family Journal',
                'title' => 'Save Grandad’s best stories and secret recipes before anyone forgets them.',
                'story' => 'Some memories deserve more than a quick feed post. Use the Family Journal to write thoughtful stories, family recipes, and life lessons in a calm, ad-free space built to last.',
                'highlights' => [
                    'Long-form stories, recipes & family traditions',
                    'Private to your trusted family circle',
                    'Organised chronologically as a living keepsake',
                ],
                'cta_label' => 'Open Family Journal',
                'cta_url' => '/familyJournal',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-journal-bookmark-fill',
                'theme' => 'emerald-teal',
            ],
            [
                'id' => 'family-commitments',
                'badge' => 'Together & Accountable',
                'subtitle' => 'Family Commitments',
                'title' => 'Sunday phone-free dinners or a 5k family walk? Track your shared goals together.',
                'story' => 'Whether it is calling Grandma every Wednesday or saving for a summer trip, Family Commitments turns good intentions into shared streaks everyone can cheer on.',
                'highlights' => [
                    'Set weekly or monthly recurring family targets',
                    'Log quick check-ins and celebrate streaks',
                    'Automatically synced with your Family Calendar',
                ],
                'cta_label' => 'View Commitments',
                'cta_url' => '/commitments',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-calendar2-heart-fill',
                'theme' => 'cobalt-sky',
            ],
            [
                'id' => 'family-rituals-quests',
                'badge' => 'Playful Traditions',
                'subtitle' => 'Family Rituals & Quests',
                'title' => 'Turn everyday family traditions into playful weekend quests and badges.',
                'story' => 'From cooking a traditional dish together to interviewing the eldest relative on camera, Family Rituals & Quests gives everyone a fun excuse to connect this week.',
                'highlights' => [
                    'Curated heritage prompts & weekend challenges',
                    'Complete quests with photos or short stories',
                    'Keep family culture alive across generations',
                ],
                'cta_label' => 'See Rituals & Quests',
                'cta_url' => '/profilePage#familyHeritageSection',
                'cta_action' => 'scroll',
                'cta_target' => 'familyHeritageSection',
                'icon' => 'bi-stars',
                'theme' => 'emerald-teal',
            ],
            [
                'id' => 'famchat',
                'badge' => 'Private & Personal',
                'subtitle' => 'FamChat',
                'title' => 'Send a voice dedication or private note anchored to a special family memory.',
                'story' => 'FamChat keeps your one-to-one and small-group family conversations calm, private, and tied directly to the photos, events, and moments you care about.',
                'highlights' => [
                    'Private messages & warm voice dedications',
                    'Pin conversations to shared family artifacts',
                    'Zero noisy strangers or algorithmic clutter',
                ],
                'cta_label' => 'Open FamChat',
                'cta_url' => '/kin-whispers',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-chat-dots-fill',
                'theme' => 'royal-crimson',
            ],
            [
                'id' => 'photo-gallery',
                'badge' => 'High-Res Memories',
                'subtitle' => 'Photo Gallery',
                'title' => 'Every holiday, birthday, and candid snap — neatly organised in one place.',
                'story' => 'Stop scrolling through thousands of chat messages trying to find that one photo from Auntie’s 60th. Your family Photo Gallery keeps every shared picture crisp and easy to revisit.',
                'highlights' => [
                    'Clean gallery view of all shared family photos',
                    'React and comment directly on any memory',
                    'Safe from compressed messaging-app blur',
                ],
                'cta_label' => 'Open Photo Gallery',
                'cta_url' => '/profilepage/gallery',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-images',
                'theme' => 'indigo-gold',
            ],
            [
                'id' => 'events-and-milestones',
                'badge' => 'Mark the Calendar',
                'subtitle' => 'Create Event & Milestones',
                'title' => 'Planning a birthday dinner, baby shower, or annual reunion? Get everyone on one page.',
                'story' => 'Create a family event in seconds, collect RSVPs without chasing three different group chats, and let Memories & Milestones remind everyone before big dates arrive.',
                'highlights' => [
                    'Quick event creation with date, venue & RSVP',
                    'Automatic birthday & anniversary reminders',
                    'Keep the whole family aligned effortlessly',
                ],
                'cta_label' => 'Create an Event',
                'cta_url' => '/profilePage#createEventModal',
                'cta_action' => 'modal',
                'cta_target' => '#createEventModal',
                'icon' => 'bi-calendar-plus',
                'theme' => 'cobalt-sky',
            ],
            [
                'id' => 'invite-family',
                'badge' => 'Grow Your Circle',
                'subtitle' => 'Invite Others & Family Codes',
                'title' => 'Missing a cousin or an uncle on here? Bring them in with one WhatsApp tap.',
                'story' => 'FamilyPlatform gets twice as lively with every relative who joins. Share your unique family code via WhatsApp or SMS so they land straight in your private circle.',
                'highlights' => [
                    'One-tap WhatsApp & Messages invite links',
                    'Protected by your private Family Code',
                    'Manage connections & approvals anytime',
                ],
                'cta_label' => 'Invite Family',
                'cta_url' => '/profilePage#inviteFamilyModal',
                'cta_action' => 'modal',
                'cta_target' => '#inviteFamilyModal',
                'icon' => 'bi-person-plus-fill',
                'theme' => 'amber-crimson',
            ],
            [
                'id' => 'how-to-use-guide',
                'badge' => 'Guided Playbook',
                'subtitle' => 'How to Use Guide',
                'title' => 'Curious what else FamilyPlatform can do? Take a 60-second tour of the lot.',
                'story' => 'From announcements to interactive games and family archives, our visual How to Use Guide walks you through every corner at your own pace.',
                'highlights' => [
                    'Clear walkthroughs for every studio & tool',
                    'Practical tips for getting elders & kids involved',
                    'Always one tap away in your left menu',
                ],
                'cta_label' => 'Open Guide',
                'cta_url' => '/how-to-use',
                'cta_action' => 'link',
                'cta_target' => '',
                'icon' => 'bi-book-half',
                'theme' => 'cobalt-sky',
            ],
        ];
    }

    /**
     * Check whether a spotlight ID exists in the catalog.
     */
    public static function exists(string $spotlightId): bool
    {
        foreach (self::getCatalog() as $item) {
            if ($item['id'] === $spotlightId) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether an interaction action is valid.
     */
    public static function isValidAction(string $action): bool
    {
        return in_array($action, self::VALID_ACTIONS, true);
    }

    /**
     * Return an ordered list of active spotlights for a user, rotated deterministically
     * by day and user ID, excluding any dismissed IDs.
     *
     * @param string $userId
     * @param list<string> $dismissedIds
     * @param string|null $seedDate
     * @return list<array<string, mixed>>
     */
    public static function getFeedSpotlights(
        string $userId,
        array $dismissedIds = [],
        ?string $seedDate = null
    ): array {
        $catalog = self::getCatalog($userId);
        $dismissedLookup = array_flip($dismissedIds);

        $available = array_values(array_filter(
            $catalog,
            static fn(array $item): bool => !isset($dismissedLookup[(string)$item['id']])
        ));

        $count = count($available);
        if ($count <= 1) {
            return $available;
        }

        $dateKey = $seedDate ?? date('Y-m-d');
        $seed = abs((int)crc32($userId . '|' . $dateKey));
        $offset = $seed % $count;

        return array_merge(
            array_slice($available, $offset),
            array_slice($available, 0, $offset)
        );
    }
}
