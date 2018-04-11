---
id: 26
layout: post
title: "How to add HTTPS to GitHub Pages in 6 Steps"
perex: |
    I always loved GitHub Pages, thanks to open-source and free hosting. Last thing that made me feel too oldschool was the plain "http://" protocol. That is usually the main argument why people move from GitHub Pages elsewhere, i.e. their own VPS. What a pity.
    <br>
    Fortunately, thanks for Michal Špaček's ability to explain complex stuff in a simple way, we solved this over a single weekend.
author: 1
lang: en
reviewed_by: [17, 18]
related_items: [18]
tweet: "Post from Community Blog: How to add #HTTPS to #GitHub Pages in 6 Steps #cloudflare"
---

This site now runs on HTTPS, see: [https://pehapkari.cz/](https://pehapkari.cz) thanks to [Michal Špaček](https://www.michalspacek.com) who added [a simple how-to manual](https://github.com/pehapkari/pehapkari.cz/issues/162#issuecomment-272590505) to the GitHub issue. I will try to expand it here.

<h3>HTTPS is <strike>Hard</strike> Easy!</h3>

I thought setting up HTTPS is a very difficult process that requires at least owning a VPS, extensive work via SSH and buying, paying and setting up the certificate. This is practically impossible to do with just GitHub Pages.

But is it really?


## How to Enable HTTPS in 3 Steps

### 1. [Register on Cloudflare](https://www.cloudflare.com/a/sign-up)

### 2. [Add Your Site](https://www.cloudflare.com/a/add-site)

Cloudflare will pre-fill the DNS records by scanning your current records. Continue.

Pick free plan.


### 3. Change Name Servers

Go to your domain administration (look for "change DNS servers" or similar) and change current name servers to those provided by Cloudflare. You can either create a new NSSET or change the current one, if your registry supports NSSETs.

<img src="/assets/images/posts/2017/https/change-nameservers.png">

Keep in mind that **the servers may be different for each domain you add to Cloudflare**.


## What will happen now?

The site will be served via Cloudflare once the browsers notice the change, that is when the DNS changes are propagated world-wide. That could take some time, from minutes to even days, depending on the expiration of these records in the DNS. Don't worry though, there's no downtime, the site should be accessible either via Cloudflare or not, the change we made doesn'ẗ affect the origin servers – GitHub's in this case – those are still up and running.

**The changes could be verified in the browser or using the command-line interface**. Look for the `Server` response header, it should contain `cloudflare-nginx`.

```bash
curl -I http://pehapkari.cz | grep Server
Server: cloudflare-nginx
```

Now try https://pehapkari.cz, if there's no error, we have HTTPS enabled!

Note that it can [take up to 24 hours](https://support.cloudflare.com/hc/en-us/articles/203045244-How-long-does-it-take-for-CloudFlare-s-SSL-to-activate-) for HTTPS to enable.

**Not quite there yet**

Now we have a website running over HTTPS. But it is also accessible without HTTPS, through http://pehapkari.cz, so we need to redirect all HTTP requests to the HTTPS ones. This is especially important due to security, but also to avoid duplicate content (that would hurt SEO).

## How to Redirect "HTTP" urls to "HTTPS" in 3 Steps

### 1. [Go to Crypto](https://www.cloudflare.com/a/crypto/)

Scroll down a bit, roughly to the middle of the page.

### 2. Enable "Always use HTTPS"

This will redirect both

- http://pehapkari.cz and
- http://www.pehapkari.cz

to their `https://` versions.

If you need more or different redirections you can use [Page Rules](https://www.cloudflare.com/a/page-rules/).

### 3. Verify it Works

Now run this diagnostics using your command-line interface:

```bash
curl -I http://pehapkari.cz | grep -i Location
curl -I http://www.pehapkari.cz | grep -i Location
curl -I https://www.pehapkari.cz | grep -i Location
```

These commands should show:

```bash
Location: https://pehapkari.cz/
Location: https://www.pehapkari.cz/
Location: https://pehapkari.cz/
```

If they do, you're done! If not, **leave us a comment below** and we'll try to help you and also improve this tutorial.


If you ever get to **any security troubles**, **call [Michal](https://www.michalspacek.com)** to the rescue. Thank you!

<img src="/assets/images/posts/2017/https/thank-you.png">
