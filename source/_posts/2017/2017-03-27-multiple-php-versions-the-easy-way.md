---
id: 30
title: "Multiple PHP versions, the easy way"
perex: "Always wanted to try or run your application with a different PHP version without breaking everything else? Why not, there is a way to run multiple versions in parallel!"
author: 17
lang: en
tweet: "Post from Community Blog: Multiple #PHP versions, the easy way"
---


## Basics

There's a lot of use cases when one would need multiple PHP versions side by side.
As an example, assume we have one legacy application and one modern application.
The legacy one runs only on PHP 5.x whereas the modern one runs only on PHP 7.x.
Virtually impossible and disjunctive setup you'd say, right?
Before you start thinking about setting up second server with new PHP, consider the option of having two PHP versions side by side and letting your application _choose_ one.


## What do we need

#### Debian Stretch

We'll use Debian Stretch for this purpose.
Yes, at the time of writing, it's still an unreleased version, already in freeze though.
But it's going to have native support for co-installable PHP versions, so we'll use it for convenience.
You'll see later that it'd be eventually also possible to achieve this even with currently stable Jessie, although not with the native PHP package it distributes (`php5`).
Or you can use Ubuntu as well, it's based on Debian after all. :-)

#### NGINX

Of course, we can suffer with the old friend Apache, but why would we?
NGINX performs much better and is way easier to configure with PHP FPM.
Also considering other features, i.e. HTTP/2, multi-certificate setup etc., there's really no show-stopper.

#### PHP with co-installability support

This is probably the trickier part.
Luckily for us, starting with Debian Stretch there will be a new infrastructure for PHP packages that handles versioning natively.
No need to mess with the source code or modifying Debian packages themselves!


## Putting it all together

#### Install NGINX

Installing NGINX is as simple as running this command:

```
$ apt-get install nginx
```

This will install NGINX with default modules and configuration.

#### Installing PHP 7.0

Install PHP 7.0 from Debian archive.
This will be (sadly) the default version in Stretch, 7.1 came out too late to squeeze into Stretch's timeline.
Do this using the following command:

```
$ apt-get install php7.0-cli php7.0-fpm
```

Notice the different pattern of the package name.
Older Debian installations used simply `php5` whereas newer infrastructure uses `phpX.Y`.
This is the obvious part that efficiently allows us to use multiple PHPs in parallel.
With this structure, you can install each of the minor versions next to each other.

#### Installing PHP 5.6

Here's the catch.
Debian only offers a single PHP version in the official repository.
Fortunately there are packages directly from a maintainer of Debian's PHP packages, Ondřej Surý.
Visit [his page](https://deb.sury.org/) about packaging to learn more.
(There is also a PPA repository in case you'd rather use Ubuntu instead of Debian.)

We'll now add his repository (as well as enable HTTPS for APT and register the APT key):
```
$ apt-get install apt-transport-https
$ curl https://packages.sury.org/php/apt.gpg | apt-key add -
$ echo 'deb https://packages.sury.org/php/ stretch main' > /etc/apt/sources.list.d/deb.sury.org.list
$ apt-get update
```

Now that we have the repository added, we can install the packages from there:
```
$ apt-get install php5.6-cli php5.6-fpm
```

This will install PHP 5.6 in parallel to PHP 7.0 installed earlier.
We can check this is true by simply running:
```
$ php7.0 -v
PHP 7.0.15-1 (cli)
$ php5.6 -v
PHP 5.6.30-5+0~20170223133422.27+stretch~1.gbp1ee0cb (cli)
```

Note that for conviniency there is also a `php` command provided by _alternatives_ (which defaults to the newest version):
```
$ php -v
PHP 7.0.15-1 (cli)
```

You can switch the default version using update-alternatives, just run the following command and pick the version you prefer:
```
$ update-alternatives --config php
```

#### Configuring PHP

Configuration is stored in versioned locations as well.
Additionally the configuration is separate for each SAPI.
Same applies to PHP modules so you don't have to worry about incompatible modules between versions.

We are looking for FPM configuration.
PHP 7.0 FPM configuration is stored in `/etc/php/7.0/fpm` and PHP 5.6 in `/etc/php/5.6/fpm`.
Each FPM instance consists of multiple pools.
Ideally each site/project should have its separate pool, but that's out of scope of this article, so we'll just use the default pool called _www_.
Open `/etc/php/7.0/fpm/pool.d/www.conf` and look for the `listen` option.
It should equal `/run/php/php7.0-fpm.sock` or similar.
Now do the same for 5.6, it should contain the same with just 5.6 instead of 7.0.
Note that it could also be a bind address, i.e. IP address with port (which is performance-wise more suitable for production than sockets).

#### Configuring NGINX

NGINX configuration is stored inside `/etc/nginx`.
There are multiple files and directories, here's what we will need to know:
* By convention, all available virtual hosts are stored inside _sites-available_ directory.
* All production sites are then just symbolic links from _sites-enabled_ to those files.
* Any code intended for reuse across multiple virtual hosts is stored inside _snippets_ folder.
* The `fastcgi.conf` file contains all FastCGI-specific variables that are passed to PHP.
* The `snippets/fastcgi-php.conf` is just a helper file to do all necessary before passing the request to PHP.

Finally remove anything in `/etc/nginx/sites-enabled`, we don't want any default configuration to clutter with our setup.


## Example setup

Now that we have everything ready, let's create two virtual hosts.
For simplicity we'll just run them on a different port so we don't have to worry with setting up the hostnames.

#### Site with PHP 7.0

First, create folder for our new site and just add a `phpinfo()` there:
```
$ mkdir /var/www/site-with-php7.0
$ echo -e '<?php\nphpinfo();' > /var/www/site-with-php7.0/index.php
```

Now create a simple virtual host with this content.
Put the following into `/etc/nginx/sites-available/site-with-php7.0`:
```
server {
	listen 8870 default_server;
	listen [::]:8870 default_server;
	server_name _;
	root /var/www/site-with-php7.0;
	index index.php;
	location / {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php7.0-fpm.sock; # adjust for the listen setting discussed above
	}
}
```

#### Site with PHP 5.6

We'll do just the same for 5.6, except we'll change the port, root directory and FastCGI backend:

```
mkdir /var/www/site-with-php5.6
echo -e '<?php\nphpinfo();' > /var/www/site-with-php5.6/index.php
```

Put the following into `/etc/nginx/sites-available/site-with-php5.6`:
```
server {
	listen 8856 default_server;
	listen [::]:8856 default_server;
	server_name _;
	root /var/www/site-with-php5.6;
	index index.php;
	location / {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php5.6-fpm.sock; # adjust for the listen setting discussed above
	}
}
```

Enable these sites:
```
$ ln -s ../sites-available/site-with-php5.6 /etc/nginx/sites-enabled
$ ln -s ../sites-available/site-with-php7.0 /etc/nginx/sites-enabled
```

Reload NGINX and we're done:
```
$ systemctl reload nginx.service
```


## Testing everything out

We should now test that our setup works, shouldn't we?

#### Testing site with 7.0

Head over to your browser and open `http://localhost:8870/`.
You should see the output of `phpinfo()` telling you that you are running PHP 7.0.

#### Testing site with 5.6

Now do the same for 5.6 and open `http://localhost:8856/`.
You should be seeing PHP 5.6.


## Conclusion

The article should shed some of the myths about the needs and (im)possibility of running multiple PHP versions.
You now know how simple and straightforward such setup is and can deploy it right away.
It's of course also possible to install PHP 7.1 or 5.5, both are available in the aforementioned repository, configuration would be equivalent.
You can also use this setup on Ubuntu systems, everything is same except that you'll just do it the Ubuntu-way and use the mentioned PPA repository.


### Complete example with Docker

You can also find this example in the following [GitHub Gist](https://gist.github.com/Majkl578/08bb58780344603ad253a9e3b0552eb0).
If you would like to try it yourself, simply clone the Gist, build the image and run it locally:
```
$ git clone https://gist.github.com/08bb58780344603ad253a9e3b0552eb0.git /tmp/multiphp
$ docker build -t multiphp /tmp/multiphp
$ docker run --rm -P multiphp
```
Now just visit http://localhost:8870/ and http://localhost:8856/ respectively to see the result!
