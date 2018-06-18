---
id: 56
title: "A place to meet Statie"
perex: |
    I'm happy to announce that Statie, a newcomer to the field of static site generators written in PHP, received a place where it could promote itself better. [Its own website](https://www.statie.org). Though a really simple one; but it's healthy to start with small things, they say.
lang: en
author: 31
tweet: "Post from Community Blog: A place to meet Statie #php #static #generator"
---

_originally published at [romanvesely.com](https://romanvesely.com) on 2018-01-08_

---

My own blog is being built with [Statie](https://github.com/Symplify/Statie). But why did I choose something with almost no history and with just a few users, when plenty of other possibilities are out there?

## A bit of Story

Well, if you come from Slovakia or Czech Republic you probably know [Nette framework](https://nette.org/) and its awesome [Latte templating engine](https://latte.nette.org/). If you haven't heard about it before I recommend you to take a break and [give it a shot](https://doc.nette.org/en/2.4/quickstart) to make yourself familiar. It may be useful in the future.

To continue, I consider Latte the most elegant among others and that's the reason I chose Statie. Yep, it's that straightforward. Really, just [look at it](https://latte.nette.org/#toc-macros).

I wanted to spread the word and add a mention to [the catalog of static generators](https://www.staticgen.com/) when I realized there is no homepage. By the way, it just blew my mind to see how many of the generators are in there only waiting to be used. Maybe another day...

[![Statie website](/assets/images/posts/2018/a-place-to-meet-statie/statie-web.png)](https://www.statie.org/)

## A bit of Stack

[The website](https://www.statie.org/) runs on Statie, obviously. I just took markdown documentation files, written by the creator [Tomas](https://www.tomasvotruba.cz/), which were lying on GitHub, covered them with a handful of templates, threw some configuration and _voilà_.

### Atomic CSS

I thought I could play a bit and use some things I haven't had a pleasure to try on other projects. Many articles about new (okay, let's say different) CSS principles called **atomic CSS** or **utility-first** wander around the internet. Especially [one by Adam Wathan](https://adamwathan.me/css-utility-classes-and-separation-of-concerns/), a really thorough one, convinced me to try such a technique. Why not use [the framework](https://tailwindcss.com/) he created straight ahead? Let's have a look:

```html
<div class="sm:flex bg-yellow-dark text-center text-grey-darkest">
    <div class="flex-1 mx-4">
        Lorem ipsum...
    </div>
    ...
</div>
```

No custom classes used in above example - look like inline styles but they're not. Building the layout was easy and resulting [styles.css](https://github.com/crazko/statie-web/blob/master/source/assets/css/styles.css) is so lean I want to applaud (not sure whether me or Adam).

### Algolia

Even though Statie's learning material isn't very verbose (yet!) a feature one would certainly expect from a documentation site is the possibility to search. I knew I want to use [Algolia](https://community.algolia.com/docsearch/), widely used in the open-source scene. The setup was a piece of cake and it's working like a charm. Apropos, definitely try their [browser plugin](https://github.algolia.com/) adding new functionality to the GitHub search; have could I live without that?

What's left? [Source code](https://github.com/crazko/statie-web) lives on GitHub, it is [built with Travis](https://travis-ci.org/crazko/statie-web/) and [hosted on GitHub Pages](https://www.statie.org/docs/github-pages/), while SSL certificate comes from [Cloudflare](https://pehapkari.cz/blog/2017/03/13/how-to-add-https-to-github-pages-in-6-steps/).

## A bit of End

And that's it. I hope the site will grow together with Statie and that some of you will pitch in as well.

### Do You Use Statie?

Contribute now and [add your site](https://github.com/crazko/statie-web/edit/master/source/_data/projects.yml) to the list of projects!
