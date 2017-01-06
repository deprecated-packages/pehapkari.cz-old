---
layout: post
title: "Why articles with code examples should be CI tested"
perex: "I know many great articles, that go right to the point and when I use code examples, they work. But when I recommend these article to people I mentor, I realize they are 2 years old and probably not working any more. From hero to zero. Today I will show how to keep them alive lot longer with minimal effort."
author: 1
lang: en
---

Do you know [Awesome lists](https://github.com/sindresorhus/awesome/)? If not, got check them. They collect best sources about some topic. When I try to learn something new, I start on Github looking for "awesome <technology>". I recommend at least briefly checking them.
 
The idea behind Awesome list is to have source that:

- **are up-to-date with modern technology**
- **are the best in the field**
- **are easy to learn by beginners**


## How "Awesome Doctrine" was Born

When I was working with Nette, I met **Doctrine ORM** thanks to [Filip Procházka](https://filip-prochazka.com/) and his great [Kdyby](http://github.com/Kdyby) open-source boom.

One day I decided to learn more about Doctrine. Documentation looked like a manual for experts rather than something I could learn from. I was also curious **how people use Doctrine in real applications, how to overcome performance issues, some cool features and pro tips**.
 
I was already familiar with [Awesome PHP](https://github.com/ziadoz/awesome-php) by [ziadoz](https://github.com/ziadoz), so I looked for "Awesome Doctrine". 

0 results. Really? Why nobody made this? It's so obvious this would be useful.

Ah, it's my job then. And the joyful hell started.


### Many Source on Many Versions
 
I was lucky to found many articles about Doctrine. One about Filters, Events or Criteria. But when I tried to use the code, it often didn't work. After digging I found out there was version 1.0, which was completely different. 

> Tip: if you write post about software, at least mention its version &ndash; even if it has only single version.

So I liked the concept in article and wanted to use it, but I didn't know what is different in version 2.4. I closed it.

I also read [Czech series on Zdroják](https://www.zdrojak.cz/clanky/doctrine-2-uvod-do-systemu/) written by [Jan Tichý](http://www.jantichy.cz/). It could give me great insights, but it was about Doctrine 2-beta. I closed it.


### What to put in the list?

I've decided to focus on sources released in that year. When articles and Doctrine version are the same - Doctrine 2.4 - it will be great source to learn from.

Idea was good, [List](https://github.com/TomasVotruba/awesome-doctrine) was done. I was happy until...

### ...Doctrine 2.5 was out!

So now every from 20 sources in the list got bit deprecated. Thank you!

Oh, so that's why nobody made it in the first place :).

Now I also understand why programmers hate new versions of software and want to stick with version they already know. It makes sense in such conditions.


## "Awesome Symfony"  

Before I realized It makes no sense to make list of sources, because next year I could drop most of them, I make [Awesome list for Symfony](https://github.com/Pehapkari/awesome-symfony-education).
 
New Symfony version is released every single year, so the list is even more outdated than Doctrine. 

**So what this leads to?**


## Running in Circles

If I get back to the **useful source** idea from the beginning.

- **are up-to-date with modern technology**
- **are the best in the field**
- **are easy to learn by beginners**


To make this happen, I would have to create "Awesome * List" every year.
To make that happen, each article would have to be rewritten with each new version.

That would mean around 50 articles on Doctrine every year. **And all this work just to keep status quo**. In big communities like Symfony and Laravel, this happens, but I still consider too much wasted work (constructive ideas coming bellow).

So every year sources are more outdated, useful source then it used to be in the day of release of article. Writing article in such environment would be as useless as writing 100% test coverage for Christmas campaign microsite. 

Thus, motivation to write article software is getting low, even when software is being released. - I call this *Know How Sharing Lag*.  


## This Sounds like Legacy Code 

Let's say we have application with legacy code. It brings me money and I want keep it alive and growing as much longer as possible. 
 
...

Mm, I would write tests and start refactoring?

Could this be possible to integrate to a blog or website?

### Dream Big

It would have to be:

- **integrated in blog**, because another external source would deprecate - thanks Sculpin
- **composer supported** - thanks Github Pages and Travis  
- open-source hosted, so the author won't burn out on yearly fixes - thanks Github Pages
- **CI supported** - thanks Travis
- **tested daily** - thanks Travis Cron Jobs (Beta)


This idea was created in late 2015 with no solution ahead. I want to thank [Jáchym Toušek](https://twitter.com/enumag) for consulting this idea and making [the first prototype](https://github.com/enumag/enumag.cz/commit/3efc82717b9965bb19a2609e4caddc0c5467552d).  


And that why and how *tested articles* were born.



### On this Blog by Default!

As you can see, there are few tested articles already:

<img src="/assets/images/posts/2017/tested-article/covered.png">

This is how tested article looks like in [single commit](https://github.com/pehapkari/pehapkari.cz/commit/85b69950b32c39b9e972582720a23a18a1adc4be). It will last for years, will work on Symfony 4 and maybe further. 

**Feel free to send one.** We'll make sure it will make it into 2018 :) 

P.S.: If you need more detailed tutorial, how to do it, let me know in the comments. 
