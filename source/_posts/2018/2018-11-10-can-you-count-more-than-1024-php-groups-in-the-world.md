---
id: 78
title: "Can you Count more Than 1024 PHP Groups in The World?"
perex: |
    In April 2018 I started a side project to list meetups in Europe near Prague. **PHP meetups are so much fun** and I didn't find any single-page with a map that would list them. In the start, this site had a small table, with 10 meetups a month, very *modern* black/white Times New Roman design and *advanced* human-manual updating.
    <br><br>
    Since then I got feedback from dozens friends and users with this WTFs and ideas - they helped me to add feature now and then, polish a design with emoji and Bootstrap, **automate everything and even crawl over 150 urls**. I bought [friendsofphp.org](friendsofphp.org) domain and the project became a standalone single page.
    <br><br>
    Today, it's a much bigger project with over...
author: 1
tweet: "New Post on #pehapkari Blog: Can you Count more Than #1024 PHP Groups in The World?      #php #meetupcom #travelling #phpfamily by @votrubaT"
tweet_image: "/assets/images/posts/2018/friends/preview.png"
---

...well, see for yourself:

<div class="text-center">
    <a href="https://friendsofphp.org/">
        <img src="/assets/images/posts/2018/friends/preview.png">
    </a>
</div>

<blockquote class="blockquote text-center">
    1, 2, 3... <strong>1023 groups</strong>
</blockquote>

I bet there is one group out there that isn't listed yet. It might have a local non-PHP name like ["AFUP" community in France](https://friendsofphp.org/groups/#france).

Whoever finds this group, wins 🍺!

## The Most Active Region

I assumed the Czech Republic or Germany is the most active in Europe. I was wrong! Look at Benelux with **over 20 meetups in a month**!

It would be easy to get lost among all these pins, so we put 2 more colors in.

<div class="text-center">
    <img src="/assets/images/posts/2018/friends/colors-before-after.png" class="img-thumbnail">
    <p>
        <em>(Before → After)</em>
    </p>
    <p>
        Traffic lights to save your eyes: 
        <strong>
            <span class="text-success">next 7 days</span> 
            | <span class="text-warning">next 14 days</span> 
            | <span class="text-danger">next 30 days</span>
        </strong>
    </p>
</div>

## 6 More Cool Features

- **Local Storage** - It is really annoying to open a map and always see the same whole Europe. You're from *complete your country* and you have a right to see your near-by meetups. We got you covered - your last selected location is stored in your browser ✅

- **Share Your map**
    - "Hey John, check these meetups in Berlin..."
    - "How?"
    - "You have to zoom to this city and..."
    - "Where in Germany is it?"
    - "Oh wait, there is a share button, I'll send you a link"
    - "Awesome" ✅

- **Locate Yourself** - No need to zoom your country - just use your geo-location ✅. Works great on both pc and smartphones.

- **Mobile Ready** - While you're traveling, you're (on your) mobile and you need the meetup fast. The site runs on Bootstrap 4.1 and has hours of testing in different continents and poor connections ✅

- **Single Site** - No clicking, no menu. Just map and table ✅

- **Move the Map → Table Filters** - See what you need. As you move to map the table will transform - it shows you only meetups visible on the map ✅ . Try F5 - local storage still works.

Any tips? [Add new issue](https://github.com/TomasVotruba/friendsofphp.org/issues/)

## How Does the Website Work?

Meetups are downloaded every day by Travis CRON job from Meetup.com for each of [1023 groups](https://friendsofphp.org/groups/). You'll find them manually added in [this YAML file in repository](https://github.com/TomasVotruba/friendsofphp.org/blob/master/source/_data/groups.yml). No surprise **it runs on [Statie](https://www.statie.org/) and is fully [open-sourced on Github](https://github.com/tomasvotruba/friendsofphp.org)**.

Although there [Meetup API](https://www.meetup.com/meetup_api/) often works as documented, it's not possible to find all PHP groups with it. There is [an issue](https://github.com/meetup/api/issues/249) **when you search for groups in a specific location, it ignores the location and sets back to your origin city**. Pity, that exactly what we need here.

### What now? 

Maybe instead of API... a **crawler could help** - [and it did](https://github.com/meetup/api/issues/249#issuecomment-427548572):

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

// crawling: 'https://www.meetup.com/topics/php/si/'
$code = 'si';

$crawlUrl = sprintf('https://www.meetup.com/topics/%s/%s/', 'php', $code);
$crawler = new Crawler(file_get_contents($crawlUrl));

$meetupNames = [];
// headlines of found groups
foreach ($crawler->filterXPath('//span[@class="text--bold display--block"]') as $node) {
    $meetupNames[] = $node->textContent;
}

var_dump($meetupNames);
```

After 4 hours of debugging broken API, I got this solution working in roughly 60 minutes.
Provide a list of codes for all countries in the world and in a few minutes you have with **800 new PHP groups** ↓

<div class="text-center">
    <img src="/assets/images/posts/2018/friends/groups-before-after.png" class="img-thumbnail">
    <p>
        <em>(Before → After)
    </p>
</div>

<blockquote class="blockquote text-center">
   Lesson learned: use what works.
</blockquote>

Events are stored with their location and rendered to Open Street Maps with amazing [Leaflet framework](https://leafletjs.com/). You don't have to know any Javascript, because the documentation is so well written.

## How much Does it Cost to Travel to abroad Meetup?

There is [plenty of reasons not-to-go](https://www.tomasvotruba.cz/blog/2018/07/23/5-signs-should-never-have-a-talk-abroad/) visit PHP meetup abroad, but the one I hear the most **are money**:

To give you an idea, here **are costs of my trips from Prague** to cities nearby:

- **Dresden** - 2 hours by train, 20 € return ticket
- **Vienna** - 5 hours, 30 € ticket, sleep over at friend from meetup or AirBnb for 30 $
- **Berlin** - 5 hours, 50 € ticket, sleep over at friend from meetup or AirBnb for 40 $

In the start, I had to pay Airbnb. But when you go to meetups more than once, you'll remember people, they'll remember you and they're very helpful with your following visits. **Just ask to stay over for one night on the floor**.

### Do You Need Help With $ or Ask Where to Start?

Let [me know](https://www.tomasvotruba.cz/contact/) - I might know a way to help you.

<br>

That's all folks. Enjoy your offline PHP friends - [friendsofphp.org](https://friendsofphp.org) and have fun! 
