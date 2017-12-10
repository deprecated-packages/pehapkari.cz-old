---
id: 45
layout: post
title: "How to connect ELK with Monolog"
perex: '''
    ELK is awesome stack for logging. Monolog is awesome PHP logging library. Let's make them work together.
'''
author: 15
lang: en
related_posts: [44]
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


## What is ELK Stack

ELK stack (now known as Elastic stack) stands for [Elasticsearch](https://www.elastic.co/products/elasticsearch),
[Logstash](https://www.elastic.co/products/logstash), [Kibana](https://www.elastic.co/products/kibana) stack.

I [wrote an article about ELK installation](https://pehapkari.cz/blog/2017/10/15/how-to-use-ELK-stack/), check it out if you're not familiar with ELK.

## Integrating Monolog with ELK Stack

As I mentioned, Monolog has many handler which can output logs.
And Logstash has many inputs, so there is not only one way to connect them, but we can choose from multiple options.

### Direct Output of Logs to Elasticseach.
The most straightforward option is to bypass the Logstash and output logs directly to Elasticsearch by using [ElasticSearchHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/ElasticSearchHandler.php).
This most simple approach is very easy to setup, but has some drawbacks when your infrastructure gets more complex.
If your application runs in other server than your elasticsearch instance, you need to deal with authentication and security of Elasticsearch.

And obviously, you can't utilize the Logstash, but that is not so big problem since one of basic features of Logstash is logs formatting and preprocessing,
and Monolog can do all of this too, but in PHP, which is more comfortable than Logstash config.

### Gelf

Other option is using the [Gelf](http://docs.graylog.org/en/2.3/pages/gelf.html).
Gelf sends logs over UDP protocol, which is super fast, but quite hard to debug, when your logs don't arrive to their destination.
Also using UDP has the obvious drawback, cause it does not guarantee that your logs arrived which can be unpleasant.

Monolog provides [GelfHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/GelfHandler.php).
The probably biggest advantage of using Gelf is the [GelfMessageFormatter](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Formatter/GelfMessageFormatter.php)
which adds many useful information to your logs.

Unfortunately, you can not use this formatted directly with other Handlers, becauses it outputs `Gelf\Message` object.

This is how to use it in Monolog in vanilla PHP when testing it in localhost:

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

When playing with Logstash, it's useful to configure it to output to standard output,
so we can see all logs in immediately.


```
output {
	stdout {
	 	codec => rubydebug
	}
}
```

Now we have to restart ELK, so the Logstash loads this new configuration.

Now we can test whether Logstash receives logs from outside in the way we need.

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
because it saves you lots of hours debugging.

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

### Using the RabbitMQ

[RabbitMQ](https://www.rabbitmq.com/) is the most widely known implementation of [AMQP](https://en.wikipedia.org/wiki/Advanced_Message_Queuing_Protocol).

Basically, it consumes messages on one side and outputs them on the other side. RabbitMQ is very powerful,
but we'll use only its basic features.

Logs are basically just messages, so we can use RabbitMQ as middleware between Monolog and Logstash.

This setup has advantage because RabbitMQ instance can run on same machine as our web application.
That means Monolog sends logs only to localhost and our app is not delayed by the network.
Then, RabbitMQ sends logs to Logstash.

In RabbitMQ, there are two important terms, exchanges and queues. Exchange is like entrypoint for messages.

Queue is (surprisingly) a queue, it holds our logs until they are processed by some consumer.
We can bind one or more queues to one exchange.
As I said, we need only basic setup, so we'll have one exchange and one queue.

For simplicity, we can use RabbitMQ in docker. We can use official `rabbitmq:3-management` image.
All `*-management` images contain web management interface of RabbitMQ, where you can play with RabbitMQ configuration.
But the mainstream way to configure out rabbits is config file, surprisingly.
When using `docker-compose`, we can run RabbitMQ as follows:
```yaml
version: "3"
services:
    rabbitmq:
        image: rabbitmq:3-management
        ports:
            - '5672:5672'
            - '15672:15672'
        volumes:
            - ./rabbitmq-data:/var/lib/rabbitmq/mnesia
            - ./rabbitmq.config /etc/rabbitmq/rabbitmq.config
            - ./rabbit.json /etc/rabbitmq/rabbit.json
```

Port 5672 is used for the RabbitMQ itself, port 15672 is used for the web interface.

`/var/lib/rabbitmq/mnesia` is where data is stored.

`/etc/rabbitmq/rabbitmq.config` is config file.

`/etc/rabbitmq/rabbit.json` specifies our exchanges and queues setup.

`/etc/rabbitmq/rabbitmq.config` should look like this:

```smartyconfig
[
  {
    rabbit,
      [
        { loopback_users, [] }
      ]
  },
  {
    rabbitmq_management,
      [
        { load_definitions, "/etc/rabbitmq/rabbit.json" }
      ]
  }
]
```

In the `rabbitmq_management` we specify where is stored our queues definitions file.

`/etc/rabbitmq/rabbitmq.config` should look like:

```json
{
    "rabbit_version": "3.6.12",
    "users": [
        {
            "name": "guest",
            "password_hash": "iG25ELqd4wB2c3pmqBwdI4nH9czcb8JKRZSEVSuyuhOienVF",
            "hashing_algorithm": "rabbit_password_hashing_sha256",
            "tags": "administrator"
        }
    ],
    "vhosts": [
        {
            "name": "/"
        }
    ],
    "permissions": [
        {
            "user": "guest",
            "vhost": "/",
            "configure": ".*",
            "write": ".*",
            "read": ".*"
        }
    ],
    "parameters": [],
    "global_parameters": [
        {
            "name": "cluster_name",
            "value": "rabbit@5a81d356219a"
        }
    ],
    "policies": [],
    "queues": [
        {
            "name": "logs",
            "vhost": "/",
            "durable": true,
            "auto_delete": false,
            "arguments": {}
        }
    ],
    "exchanges": [
        {
            "name": "logs",
            "vhost": "/",
            "type": "fanout",
            "durable": true,
            "auto_delete": false,
            "internal": false,
            "arguments": {}
        }
    ],
    "bindings": [
        {
            "source": "logs",
            "vhost": "/",
            "destination": "logs",
            "destination_type": "queue",
            "routing_key": "",
            "arguments": {}
        }
    ]
}
```

Now we have configured RabbitMQ instance which is prepared to be connected to the Monolog and Logstash.

We'll modify our `logstash.conf` and configure the input:

```
input {
    rabbitmq {
    	host => [my host ip]
        port => 5672
		queue => "logs"
        durable => true
        passive => true
        exchange => "logs"
        user => "guest"
        password => "guest"
    }
}
```

The guest:guest is the default user in the RabbitMQ, be sure to change it in production.

For Monolog, there is a little lenghty setup involved.

1. We install the `amqp` library by `composer install php-amqplib/php-amqplib`.
2. This step is different for usage in Symfony and usage in vanilla PHP:

	- For symfony:
		We register the necessary services in `services.yml`:
```yaml
	    PhpAmqpLib\Channel\AMQPSocketConnection:
	        arguments:
	            $connection: '@PhpAmqpLib\Connection\AMQPSocketConnection'
	    PhpAmqpLib\Connection\AMQPConnection:
	        arguments:
	            $host: localhost
	            $port: 5672
	            $user: guest
	            $password: guest
```
	Then, we add the [AmqpHandler](https://github.com/Seldaek/monolog/blob/master/src/Monolog/Handler/AmqpHandler.php) in the `config.yml`:
```yaml
		monolog:
		    handlers:
		        main:
		            type: amqp
		            exchange: 'PhpAmqpLib\Channel\AMQPChannel'
		            exchange_name: 'logs'
		            formatter: 'AppBundle\Monolog\MyFormatter'
		            level: debug
```

	- In vanilla PHP:
```php
		$host = 'localhost';
		$port = 5672;
		$user = 'guest';
		$password = 'guest';
		$connection = new \PhpAmqpLib\Connection\AMQPSocketConnection($host, $port, $user, $password);
		$channel = new \PhpAmqpLib\Channel\AMQPChannel($connection);
		$handler = new \Monolog\Handler\AmqpHandler($channel, 'logs');
```

And now we are done and we can happily send logs from Monolog to ELK stack.

#### Note
There are multiple possibilites of AMQP connection.
When condidering speed, [this benchmark](https://github.com/mente/php-amqp-benchmark) shows that `AMQPSocketConnection` is much faster than `AMQPStreamConnection`,
which is the reason why I used it in this tutorial.


## The End

Now our application sends logs to ELK and finally we can fully utilize information from logs, becaus the look much better in nice charts than in files.

