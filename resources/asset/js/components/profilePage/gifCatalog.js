// Curated family GIFs, shared by the post composer and the comment box.
// Hosts must stay within the Giphy allowlist enforced by ProfilePage::sanitizeCommentGifUrl().
// We use Giphy's verified 200.gif renditions which are verified to return active images
// and never trigger Giphy's "This content is not available" placeholder image.

export const FAMILY_GIFS = [
    // Celebrate & Congrats
    { id: 'g_cel_1', cat: 'celebrate', title: 'Party Confetti Celebration', url: 'https://media.giphy.com/media/artj92V8o75VPL7AeQ/200.gif' },
    { id: 'g_cel_2', cat: 'celebrate', title: 'Cheers Toast Champagne', url: 'https://media.giphy.com/media/g9582DNuQppxC/200.gif' },
    { id: 'g_cel_3', cat: 'celebrate', title: 'Celebration Fireworks', url: 'https://media.giphy.com/media/26tOZ42Mg6pbTUPHW/200.gif' },
    { id: 'g_cel_4', cat: 'celebrate', title: 'High Five Teamwork', url: 'https://media.giphy.com/media/3oEjHV0z8S7WM4MwnK/200.gif' },
    { id: 'g_cel_5', cat: 'celebrate', title: 'Clapping Hands Applause', url: 'https://media.giphy.com/media/111ebonMs90YLu/200.gif' },
    { id: 'g_cel_6', cat: 'celebrate', title: 'Celebrate Good Times', url: 'https://media.giphy.com/media/ibolLe3mOqHE3PQTtk/200.gif' },
    { id: 'g_cel_7', cat: 'celebrate', title: 'Confetti Yay Celebration', url: 'https://media.giphy.com/media/jJQC2puVZpTMO4vUs0/200.gif' },
    { id: 'g_cel_8', cat: 'celebrate', title: 'Celebration Toast', url: 'https://media.giphy.com/media/xT0xezQGU5xCDJuCPe/200.gif' },
    { id: 'g_cel_9', cat: 'celebrate', title: 'Congratulations High Five', url: 'https://media.giphy.com/media/3o7abKhOpu0NwenH3O/200.gif' },
    { id: 'g_cel_10', cat: 'celebrate', title: 'Winner Celebration', url: 'https://media.giphy.com/media/26u4cqiYI30juCOGY/200.gif' },

    // Love & Hugs
    { id: 'g_lov_1', cat: 'love', title: 'Family Love Heart', url: 'https://media.giphy.com/media/M90mJvfWfd5mbUuULX/200.gif' },
    { id: 'g_lov_2', cat: 'love', title: 'Sending Hugs Bear', url: 'https://media.giphy.com/media/3bqtLDeiDtwhq/200.gif' },
    { id: 'g_lov_3', cat: 'love', title: 'Big Warm Hug', url: 'https://media.giphy.com/media/MDJ9IbxxvDUQM/200.gif' },
    { id: 'g_lov_4', cat: 'love', title: 'Sweet Family Love', url: 'https://media.giphy.com/media/cMso9wDwqSy3e/200.gif' },
    { id: 'g_lov_5', cat: 'love', title: 'Lots of Love Hugs', url: 'https://media.giphy.com/media/lTQF0ODLLjhza/200.gif' },

    // Family & Heritage
    { id: 'g_fam_1', cat: 'family', title: 'Proud Parents Cheer', url: 'https://media.giphy.com/media/3ohzdIuqJoo8QdKlnW/200.gif' },
    { id: 'g_fam_2', cat: 'family', title: 'Family Reunion Celebration', url: 'https://media.giphy.com/media/l0HlvtIPzPdt2usKs/200.gif' },
    { id: 'g_fam_3', cat: 'family', title: 'Family Team High Five', url: 'https://media.giphy.com/media/3o7TKr3nzbh5WgCFxe/200.gif' },
    { id: 'g_fam_4', cat: 'family', title: 'Family Dance Together', url: 'https://media.giphy.com/media/3o6Zt6KHxJTbXCnSvu/200.gif' },
    { id: 'g_fam_5', cat: 'family', title: 'Proud Parents Clapping', url: 'https://media.giphy.com/media/xT9IgG50Fb7Mi0prBC/200.gif' },
    { id: 'g_fam_6', cat: 'family', title: 'Family Fun Moments', url: 'https://media.giphy.com/media/3o7TKMt1VVNkHV2PaE/200.gif' },
    { id: 'g_fam_7', cat: 'family', title: 'Family Together Forever', url: 'https://media.giphy.com/media/l0MYt5jPR6QX5pnqM/200.gif' },
    { id: 'g_fam_8', cat: 'family', title: 'Family Generational Love', url: 'https://media.giphy.com/media/3oKIPnAiaMCws8nOsE/200.gif' },

    // Laughs & Reactions
    { id: 'g_lau_1', cat: 'laugh', title: 'Laughing Out Loud LOL', url: 'https://media.giphy.com/media/10JhviFuU2gWD6/200.gif' },
    { id: 'g_lau_2', cat: 'laugh', title: 'Giggle Chuckle Cute', url: 'https://media.giphy.com/media/1d5Zn8FqmJqApu4hNU/200.gif' },
    { id: 'g_lau_3', cat: 'laugh', title: 'Rolling On Floor Laughing', url: 'https://media.giphy.com/media/CoDp6NnSmItoY/200.gif' },
    { id: 'g_lau_4', cat: 'laugh', title: 'Steve Carell Happy Laugh', url: 'https://media.giphy.com/media/14smAwp2uHM3Di/200.gif' },
    { id: 'g_lau_5', cat: 'laugh', title: 'Minions Hysterical Laugh', url: 'https://media.giphy.com/media/BYhoMtJMQsYVy/200.gif' },
    { id: 'g_lau_6', cat: 'laugh', title: 'SpongeBob Giggle', url: 'https://media.giphy.com/media/ZqlvCTNHpqrio/200.gif' },
    { id: 'g_lau_7', cat: 'laugh', title: 'Hearty Chuckle Smile', url: 'https://media.giphy.com/media/B0vFTrb0ZGDf2/200.gif' },
    { id: 'g_lau_8', cat: 'laugh', title: 'Super Excited Laughter', url: 'https://media.giphy.com/media/nXxOjZrbnbRxS/200.gif' },
    { id: 'g_lau_9', cat: 'laugh', title: 'Burst Out Laughing', url: 'https://media.giphy.com/media/wWue0rCDOphOE/200.gif' },

    // Birthday & Milestones
    { id: 'g_bdy_1', cat: 'birthday', title: 'Birthday Balloons Celebration', url: 'https://media.giphy.com/media/GvjRy6pUMUjQxiKNlh/200.gif' },
    { id: 'g_bdy_2', cat: 'birthday', title: 'Happy Birthday Candle Wish', url: 'https://media.giphy.com/media/3o7TKDkDbIDJieKbVm/200.gif' },
    { id: 'g_bdy_3', cat: 'birthday', title: 'Birthday Cake Surprise', url: 'https://media.giphy.com/media/yoJC2GnSClbPOkV0eA/200.gif' },
    { id: 'g_bdy_4', cat: 'birthday', title: 'Birthday Party Sparkler', url: 'https://media.giphy.com/media/3oz8xAFtqoOUUrsh7W/200.gif' },

    // Joy & Dance
    { id: 'g_dnc_1', cat: 'dance', title: 'Happy Dance Groove', url: 'https://media.giphy.com/media/blSTtZehjAZ8I/200.gif' },
    { id: 'g_dnc_2', cat: 'dance', title: 'Pure Joy Dancing Snoopy', url: 'https://media.giphy.com/media/chzz1FQgqhytWRWbp3/200.gif' },
    { id: 'g_dnc_3', cat: 'dance', title: 'Victory Groove Celebration', url: 'https://media.giphy.com/media/l3q2wJsC23ikJg9xe/200.gif' },
    { id: 'g_dnc_4', cat: 'dance', title: 'Bruno Mars Happy Groove', url: 'https://media.giphy.com/media/d31w24psGYeekCZy/200.gif' },
    { id: 'g_dnc_5', cat: 'dance', title: 'Joyful Celebration Dance', url: 'https://media.giphy.com/media/l3vR85PnGsBwu1PFK/200.gif' },

    // Kids & Babies
    { id: 'g_kid_1', cat: 'kids', title: 'Cute Baby Dancing', url: 'https://media.giphy.com/media/26ufdipQqU2lhNA4g/200.gif' },
    { id: 'g_kid_2', cat: 'kids', title: 'Toddler Big Thumbs Up', url: 'https://media.giphy.com/media/l0Exk8EUzSLsrErEQ/200.gif' },
    { id: 'g_kid_3', cat: 'kids', title: 'Excited Happy Kid', url: 'https://media.giphy.com/media/3o7527pa7qs9kCG78A/200.gif' },
    { id: 'g_kid_4', cat: 'kids', title: 'Kids High Five', url: 'https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/200.gif' }
];

export const GIF_CATEGORIES = [
    { key: 'all', label: '🌟 All' },
    { key: 'celebrate', label: '🎉 Celebrate' },
    { key: 'love', label: '❤️ Love & Hugs' },
    { key: 'family', label: '👨‍👩‍👧‍👦 Family' },
    { key: 'laugh', label: '😂 Laughs' },
    { key: 'birthday', label: '🎂 Birthday' },
    { key: 'dance', label: '💃 Joy & Dance' },
    { key: 'kids', label: '👶 Kids' }
];

export const isAllowedGifUrl = (url) => /^https:\/\/(media\d*\.)?giphy\.com\//.test(String(url || ''));
