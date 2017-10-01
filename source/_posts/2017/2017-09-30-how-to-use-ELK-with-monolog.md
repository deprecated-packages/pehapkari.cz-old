---
id: 41
layout: post
title: "How to use ELK with monolog"
perex: '''
    In this article, I'll show you how to send logs from monolog to ELK stack. We will use ELK in docker for easy setup.
'''
author: 15
lang: en
---

## What is Monolog

Monolog is awesome PHP library for managing logs. [Check it on github](https://github.com/Seldaek/monolog).
You can install it with composer, simply by `composer require monolog/monolog`.

In a nutshell, Monolog offers you a logger, where you send your logs. 
This logger has multiple handlers, which send these logs wherever you need them.
Monolog has many handlers, which enable you to to simply send logs to many destinations, e.g. files, e-mails, slack, logstash, and many more. 

Full list of handlers is [in Monolog documentation](https://github.com/Seldaek/monolog/blob/master/doc/02-handlers-formatters-processors.md#handlers). 
You can use Monolog as is, but there are integrations of Monolog to many frameworks, which simplify things a lot.

For Nette, there is [Kdyby package](https://github.com/Kdyby/Monolog) providing Monolog integration.

Symfony uses Monolog [out of the box](https://symfony.com/doc/current/logging.html). 


## What is ELK stack

ELK stack (now known as Elastic stack) stands for [Elasticsearch](https://www.elastic.co/products/elasticsearch), 
[Logstash](https://www.elastic.co/products/logstash), [Kibana](https://www.elastic.co/products/kibana) stack. 
These three technologies are used for powerful log management.

Logstash is used to manage logs. It collects, parses and stores them. 
There is a lot of existing inputs, filters and outputs.

Elasticsearch is powerful, distributed NoSQL database with REST API. 
In ELK stack, Elasticsearch is used as persistent storage for our logs.

And kibana visualizes logs from Elasticsearch and lets you create many handy visualizations and dashboards 
which allow you to see all important metrics in one place.


## How to run ELK stack

The most simple installation of ELK is with docker. Because there are 3 services in ELK (obviously), 
you need to use docker-compose to manage them with ease.

There are many docker-compose projects for ELK on github, I use [this repository](https://github.com/deviantony/docker-elk/tree/searchguard).
Note that this links to searchguard branch of the git repository.

Readme of this repository describes how to run the ELK stack, be sure to check it out, I'll wait here.

ELK stack does not offer authentication out of the box, and searchguard is plugin to Elasticsearch, which adds authentication.
You should not use ELK without any kind of authentication!

Note that you need to initialize the searchguard after starting services with `docker-compose up`, 
as [described here](https://github.com/deviantony/docker-elk/tree/searchguard#bringing-up-the-stack).
Without it, searchguard will not work.

There is no need to configure elasticsearch for this example, since it can infer schema from data sent to it, 
so we will configure only Logstash in this article (except of searchguard configuration).

Logstash has inputs and outputs configuration in [logstash.conf](https://github.com/deviantony/docker-elk/blob/searchguard/logstash/pipeline/logstash.conf).
Note that directory with `logstash.conf` is mounted as volume in [docker-compose.yml](https://github.com/deviantony/docker-elk/blob/searchguard/docker-compose.yml), 
so there is no need to rebuild the image when `logstash.conf` is changed.

### Searchguard configuration

When you run the ELK stack with searchguard, it already contains some predefined users with configured privileges.
You can see these users [in searchguard documentation](https://github.com/floragunncom/search-guard-docs/blob/master/configuration_internalusers.md).

Of course you don't want to use your ELK stack with these users without changing password, 
because using publicly accessible passwords does not offer much security.

Searchguard users are stored in file [sg_internal_users.yml](https://github.com/deviantony/docker-elk/blob/searchguard/elasticsearch/config/sg_internal_users.yml).

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

By default, `sg_internal_users.yml` is not mapped as volume, but used only during build of the Elasticsearch image.
We will define this file as volume in docker-compose to easily change password without need to rebuild the image.
Also, by default, mapping of data in Elasticsearch to host OS is not present.  
Let's modify `volumes` section in the `docker-compose.yml`, so it maps both data from Elasticsearch and passwords of users.
Now our `volumes` section looks like this:
  
```yml
volumes:
    - ./elasticsearch/config/elasticsearch.yml:/usr/share/elasticsearch/config/elasticsearch.yml
    - ./elasticsearch/config/sg_internal_users.yml:/usr/share/elasticsearch/config/sg_internal_users.yml
    - ./elasticsearch/data:/usr/share/elasticsearch/data
```

 
Hashing script is in the elasticsearch service, because the searchguard is installed there. 

So, with our ELK stack running, we can run this command to generate new hash for our password
```bash
docker-compose run elasticsearch plugins/search-guard-5/tools/hash.sh -p [some_password]
```

Then, simply replace default hashes with the new ones, and restart the stack by 
```bash
docker-compose down
docker-compose up
```

Now, you have your ELK stack running and secure.
When we have searchguard working, kibana is also secured by it.
With your ELK stack working, you can visit kibana [here](http://localhost:5601/).

Log in using the admin account from searchguard, because kibana uses these credentials to access Elasticsearch, 
and otherwise you could have problems with accessing logs.


It's time to start integrating it monolog.

## Integrating Monolog with ELK stack

There are many ways to send logs from Monolog to ELK, because Monolog has many handlers, which output our logs, 
and Logstash has many inputs. 

We will use [Gelf](http://docs.graylog.org/en/2.3/pages/gelf.html) to send logs to Logstash.
Gelf sends logs over UDP protocol, which is super fast, but quite hard to debug, when your logs don't arrive to their destination.

Monolog provides [GelfHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/GelfHandler.php).

This is how to use it in raw Monolog:

```php
$host = '127.0.0.1';
$port = 12201;
$handler = new \Monolog\Handler\GelfHandler(new \Gelf\Publisher(new \Gelf\Transport\UdpTransport($host, $port)));
```

In symfony, you can specify it in `config.yml` instead:

```yaml
monolog:
    logstash:
        type: gelf
        publisher:
            hostname: 127.0.0.1
            port: 12201
        formatter: monolog.formatter.gelf_message
```

Now we configure logstash to accept logs from GelfHandler.
We simply add gelf config to input `logstash.conf`, so it looks like this:

```
input {
    gelf {
        port => 12201
        codec => "json"
    }
}
```

We'll also configure Logstash output, so we'll have Logstash fully working after the restart.
We add stdout and elastic search outputs. So the Logstash sends all received logs to both stdout, and Elasticsearch.
We can see all logs in the output, which is handy for debugging. 

```
output {
	stdout {
	 	codec => rubydebug
	}
	elasticsearch {
		hosts => "elasticsearch:9200"
		user => logstash
		password => logstash
	}
}
```

Note that here we configure user and password for HTTP authentication for searchguard. 
Change this password to your new password for logstash user.

So whole content of `logstash.conf` looks like this:
```
input {
    gelf {
        port => 12201
        codec => "json"
    }
}

## Add your filters / logstash plugins configuration here

output {
	stdout {
	 	codec => rubydebug
	}
	elasticsearch {
		hosts => "elasticsearch:9200"
		user => logstash
		password => logstash
	}
}
```

Now we have to restart ELK, so the Logstash loads this new configuration.

Now we should have whole ELK stack working, so now we can test whether Logstash receives logs from outside.

```bash
echo '{"version": "1.1","host":"example.org","short_message":"A short message that helps you identify what is going on","full_message":"Backtrace here\n\nmore stuff","level":1,"_user_id":9001,"_some_info":"foo","_some_env_var":"bar"}' | gzip | nc -u -w 1 127.0.0.1 12201
```

This bash script sends json message over UDP protocol to 127.0.0.1 to 12201 port.
If we see this message in the logstash output, it works correctly.

Now we can test whether our PHP application sends logs to Logstash by simply sending some log to our Monolog logger.
```php
$logger = new \Monolog\Logger('main', [$handler]);
$logger->log(\Monolog\Logger::INFO, 'short message');
```

If we see `short message` in the output of Logstash, everthings works.

When you use [symfony/console](https://github.com/symfony/console) in your project, you can have command to send some logs.
I use this command for testing whether logs are sent:

```php
<?php declare(strict_types=1);

namespace AppBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateLogsCommand extends ContainerAwareCommand {
	/** @var LoggerInterface */
	private $logger;

	public function __construct(LoggerInterface $logger, ?string $name = null) {
		parent::__construct($name);
		$this->logger = $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configure(): void {
		$this
			->setName('app:generate:logs')
			->setDescription('Just generates some logs to see whether monolog works')
			->addArgument('level', InputArgument::REQUIRED, 'Level')
			->addArgument('repeat', InputArgument::OPTIONAL, 'number of repeats', 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void {
		$level = $input->getArgument('level');
		$levels = [
			'debug' => Logger::DEBUG,
			'info' => Logger::INFO,
			'warning' => Logger::WARNING,
			'error' => Logger::ERROR,
		];
		$repeat = $input->getArgument('repeat');
		for ($i = 0; $i < $repeat; $i++) {
			$this->logger->log($levels[$level], 'This is generated log.');
		}

		$output->writeln("Just wrote $repeat log messages.");
	}
}
```

and then I generate some logs, e. g., `php bin/console app:generate:logs info 10` sends 10 info messages.

When installing ELK myself, I spent many hours by resolving why my app does not send logs by Gelf. 
I set `localhost` as host for Logstash instead of `127.0.0.1`, so my advice is to use ip address instead of domain name when we can, 
because it saves you lots of hours debugginf.

Some notes for debugging this:
we can simply send some message by UDP from bash to Logstash, so we can easily see, whether the message arrives. 

But testing whether our applications send message by UDP to outside world is little bit trickier. 

On linux, you can use tcpdump for this:

```bash
tcpdump -i lo udp port 12201 -vv -X
```

will capture packets your application sends, so you can see if it works.

For windows, you can download RawCap from [here](http://www.netresec.com/?page=RawCap)
and then use it by running

```bash
RawCap.exe 127.0.0.1 localhost_capture.pcap
```

## The end

This is all for today, now you have working ELK stack and PHP application sending logs there through Monolog. 