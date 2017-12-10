---
id: 44
layout: post
title: "How to use ELK stack"
perex: '''
    In this article, I'll show you how to use ELK stack for logging. We will use ELK in docker for easy setup.
'''
author: 15
lang: en
related_posts: [45]
---

## What is ELK Stack

ELK stack (now known as Elastic stack) stands for [Elasticsearch](https://www.elastic.co/products/elasticsearch),
[Logstash](https://www.elastic.co/products/logstash), [Kibana](https://www.elastic.co/products/kibana) stack.
These three technologies are used for powerful log management.

Logstash is used to manage logs. It collects, parses and stores them.
There is a lot of existing inputs, filters and outputs.

Elasticsearch is powerful, distributed NoSQL database with REST API.
In ELK stack, Elasticsearch is used as persistent storage for our logs.

And Kibana visualizes logs from Elasticsearch and lets you create many handy visualizations and dashboards
which allow you to see all important metrics in one place.

## How to run ELK Stack: Theory

The most simple installation of ELK is with docker. Because there are 3 services in ELK (obviously),
you need to use `docker-compose` to manage them with ease.

There are many `docker-compose` projects for ELK on github, I use [this repository](https://github.com/deviantony/docker-elk/tree/searchguard).
Note that this links to searchguard branch of the git repository.

Readme of the github repository describes how to run the ELK stack, be sure to check it out for further use.

### Security Note: Use Searchguard

ELK stack does not offer authentication out of the box.

Searchguard is plugin to Elasticsearch, which adds authentication.
You should not use ELK without any kind of authentication!

Note that you need to initialize the searchguard after starting services with `docker-compose up`,
as [described here](https://github.com/deviantony/docker-elk/tree/searchguard#bringing-up-the-stack).
Without it, searchguard will not work.

There is no need to configure elasticsearch for this example, since it can infer schema from data sent to it,
so we will configure only Logstash in this article (except of searchguard configuration).

Logstash has inputs and outputs configuration in [logstash.conf](https://github.com/deviantony/docker-elk/blob/searchguard/logstash/pipeline/logstash.conf).
Note that directory with `logstash.conf` is mounted as volume in [docker-compose.yml](https://github.com/deviantony/docker-elk/blob/searchguard/docker-compose.yml),
so there is no need to rebuild the image when `logstash.conf` is changed.

Also, by default mapping of data in Elasticsearch to host OS is not present.

So we will have to modify `volumes` section in the `docker-compose.yml`, so it maps data from Elasticsearch.

That was some theory and now let's try it in practise.

## How to run ELK Stack: Practise

1. [clone the repo](https://github.com/deviantony/docker-elk.git)
2. switch to searchguard branch by `git checkout searchguard`
3. Update the `docker-compose.yml` file to persist Elasticsearch data:

	Add `    - ./elasticsearch/data:/usr/share/elasticsearch/data` to the `volumes` section of `elasticsearch` service.

	So the `volumes` section should like this:
	```yml
	volumes:
	    - ./elasticsearch/config/elasticsearch.yml:/usr/share/elasticsearch/config/elasticsearch.yml
	    - ./elasticsearch/data:/usr/share/elasticsearch/data
	```

3. run the stack by `docker-compose up`
4. initialize the searchguard by `docker-compose exec -T elasticsearch bin/init_sg.sh`

Now [Elasticsearch runs on port 9200](http://localhost:9200/) and [Kibana runs on port 5601](http://localhost:5601/).

As you could notice, on both sites you are asked for login.
That's because the searchguard branch contains Elasticsearch with Searchguard plugin installed.

To try it out, you can use username `admin` and password `admin`.

So now we have ELK stack fully working!

Of course you don't want to use your ELK stack with these users without changing password,
because using publicly accessible passwords does not offer much security.

### Searchguard Configuration - Changing Passwords

When you run the ELK stack with searchguard, it already contains some predefined users with configured privileges.
You can see these users [in searchguard documentation](https://github.com/floragunncom/search-guard-docs/blob/master/configuration_internalusers.md).

Searchguard users are stored in file [elasticsearch/config/sg_internal_users.yml](https://github.com/deviantony/docker-elk/blob/searchguard/elasticsearch/config/sg_internal_users.yml).

Contents of this file is
```yaml
admin:
  hash: $2a$12$VcCDgh2NDk07JGN0rjGbM.Ad41qVR/YFJcgHp0UGns5JDymv..TOG
  #password is: admin
logstash:
  hash: $2a$12$u1ShR4l4uBS3Uv59Pa2y5.1uQuZBrZtmNfqB3iM/.jL0XoV9sghS2
  #password is: logstash
kibanaserver:
  hash: $2a$12$4AcgAt3xwOWadA5s5blL6ev39OXDNhmOesEoo33eZtrq2N0YrU3H.
  #password is: kibanaserver
kibanaro:
  hash: $2a$12$JJSXNfTowz7Uu5ttXfeYpeYE0arACvcwlPBStB1F.MI7f0U9Z4DGC
  #password is: kibanaro
```

Now, we'll change these passwords. As we can see, passwords are not stored in plaintext in this yml file, but bcrypt hash is used instead.
We can generate has for new password by running hashing script in our docker container.

Hashing script is in the elasticsearch service, because the searchguard is installed there.

By default, `elasticsearch/config/sg_internal_users.yml` is not mapped as volume, but used only during build of the Elasticsearch image.
We will define this file as volume in `docker-compose.yml` to easily change password without need to rebuild the image.

So to change passwords:
1. Add `./elasticsearch/config/sg_internal_users.yml:/usr/share/elasticsearch/config/sg_internal_users.yml` to the `volumes` section of the `elasticsearch` service.

	Now our `volumes` section looks like this:
	```yml
	volumes:
	    - ./elasticsearch/config/elasticsearch.yml:/usr/share/elasticsearch/config/elasticsearch.yml
	    - ./elasticsearch/data:/usr/share/elasticsearch/data
	    - ./elasticsearch/config/sg_internal_users.yml:/usr/share/elasticsearch/config/sg_internal_users.yml
	```

2. Generate hash of new password by
```bash
docker-compose run elasticsearch plugins/search-guard-5/tools/hash.sh -p [some_password]
```

3. Replace default hashes with the new ones in the file `sg_internal_users.yml`
4. Restart the stack by
	```bash
	docker-compose down
	docker-compose up
	```

Congratulations, now you have your ELK stack running and secure.

Log in using the admin account from searchguard, because kibana uses these credentials to access Elasticsearch.
Otherwise you could have problems with accessing logs.

Now you have ELK stack running and secure.