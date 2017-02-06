---
layout: post
title: "How add Https to Github Pages in 6 Steps"
perex: '''
    I always loved Github Pages, thanks to open-source and free hosting. Last thing, that made me feel too oldschool was "http://". That was the main argument people have to move from Github Pages to VPS. What a pity.
    <br>
    Fortunately, thanks for Michal Špaček's ability to explain complex stuff in simple way we solved this over the
weekend.
'''
author: 1
lang: en
# reviewed_by:
---

This web now runs on https, see: [https://pehapkari.cz/](https://pehapkari.cz). [Michal](https://www.michalspacek.cz) added a [simple how-to manual](https://github.com/pehapkari/pehapkari.cz/issues/162#issuecomment-272590505) to Github issue, that I will expand here.

<h3>Https is <strike>Hard</strike> Easy</h3>

I thought setting up https was very difficult process, that requires at least owning a VPS, extensive work via SSH, buying, paying and setting up a certificate. Which is practically impossible to do with Github Pages.

Or isn't it?


## How to Enable https in 3 Steps

### 1. [Register on Cloudflare](https://www.cloudflare.com/a/sign-up)

### 2. [Add Your Site](https://www.cloudflare.com/a/add-site)

Cloudflare will prefill he new DNS records by scanning current records. Continue.

Pick free plan.


### 3. Change Name Servers

Go to your domain administration (in Wedos for my case: change DNS servers) and change current name servers to Cloudflare-provided name servers. You can create new NSSET or change the current one.

<img src="/assets/images/posts/2017/https/change-nameservers.png">

Keep in mind that the **servers can be different for each domain you add to Cloudflare**.


## What will happen now?

The site will be served via Cloudflare once the browsers will start to query Cloudflare's DNS servers. There's no downtime, the site should be accessible either via Cloudflare, or not, the site doesn't really change and the origin servers – Github's in this case – are still up and running.

It can [take up to 24 hours](https://support.cloudflare.com/hc/en-us/articles/203045244-How-long-does-it-take-for-CloudFlare-s-SSL-to-activate-).

**This can be verified in the browser or CLI**. See `Server` response header, it should say `cloudflare-nginx`.

```bash
curl -I http://pehapkari.cz | grep Server
Server: cloudflare-nginx
```

If you can type https://pehpakari.cz and it works, we have https enabled!

**BUT!**

The website http://pehapkari.cz also works, so we need to redirect all http request to https ones.

## How to Redirect "http" urls to "https" in 3 Steps

### 1. [Go to Page Rules](https://www.cloudflare.com/a/page-rules/)

### 2. Add New Rule

<img src="/assets/images/posts/2017/https/page-rule-https-create.png">

This will redirect both

- http://pehapkari.cz and
- http://www.pehapkari.cz

to their `https` versions.

<img src="/assets/images/posts/2017/https/page-rule-https-list.png">

### 3. Verify it Works

Just run this diagnostics in your CLI:

```bash
curl -I http://pehapkari.cz | grep Location
curl -I http://www.pehapkari.cz | grep Location
curl -I https://www.pehapkari.cz | grep Location
```

These commands should show:

```bash
Location: https://pehapkari.cz/
Location: https://www.pehapkari.cz/
Location: https://pehapkari.cz/
```

If it does, you're done! If not, **leave us comment bellow** and we'll try to help you and also improve this tutorial.


If you ever get to **any security troubles**, **call [Michal](https://michalspacek.cz)** to the rescue. Thank you!

<img src="/assets/images/posts/2017/https/thank-you.png">
