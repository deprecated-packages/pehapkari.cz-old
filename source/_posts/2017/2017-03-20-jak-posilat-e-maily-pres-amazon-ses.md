---
id: 28
title: "Jak posílat e-maily přes Amazon SES"
perex: "Posílat emaily přes Amazon SES je snadné a levné. Zkuste to taky."
author: 20
tweet: "Urodilo se na blogu: Jak posílat e-maily přes #Amazon SES #aws"
---

## Cloudové služby Amazonu

Amazon Web Services (AWS) je soubor cloudových služeb a stává se čím dál populárnějším řešením jako základ pro infrastrukturu, kterou může využívat vaše aplikace. AWS používá dnes už tolik služeb/webů, že je snad prakticky nemožné používat aspoň jednu službu, která by nebyla se službami Amazonu alespoň nějak propojená.

V tomto článku bych rád ukázal, **jak snadno posílat e-maily** s využitím Amazon SES (Simple Email Service) a jak získat informace o tom, pokud e-mail příjemci není doručen (bounces) nebo je označen jako spam (complaints).

## Prerekvizity

K tomu, abyste mohli používat služby Amazonu, musíte mít založený účet. Poté už můžete začít nastavovat a používat služby Amazonu.

Jakmile aktivujete Amazon SES, je samozřejmě ještě před prvními testy nutné si verifikovat doménu, z které budete chtít e-maily posílat, poté ideálně nastavit [DKIM](https://en.wikipedia.org/wiki/DomainKeys_Identified_Mail), [SPF](https://en.wikipedia.org/wiki/Sender_Policy_Framework) záznamy apod.

Více o tom naleznete [zde](http://docs.aws.amazon.com/ses/latest/DeveloperGuide/setting-up-email.html). V případě, že byste chtěli SES použít a nebyli si jisti, jak nastavit na straně Amazonu, klidně mi napište.

## Jak posílat e-maily

A teď už k posílání e-mailů. Emaily můžete posílat dvěma způsoby. Buď s využitím SMTP serveru nebo skrze AWS SDK.

### SMTP

Posílat e-maily přes SMTP je ta nejsnažší varianta. V zásadě vám stačí získat přístupy k SMTP. V **Nette** pak konfigurace může vypadat takto:

```php
$options = [
    'host' => 'email-smtp.eu-west-1.amazonaws.com', // například tento, záleží podle regionu
    'username' => 'xxx',
    'password' => 'yyy',
    'secure' => 'ssl'
    'persistent' => true,
];

$message = new \Nette\Mail\Message();
$message->setFrom('odesilatel@example.com', 'Název odesilatele')
    ->addTo('prijemce@example.com')
    ->setSubject('PHP Předmět')
    ->setHtmlBody('Máme rádi PHP!');

$smtpMailer = new \Nette\Mail\SmtpMailer($options);
$smtpMailer->send($message);
```

Obdobně byste nastavili odesílání e-mailů i kdybyste odesílali e-maily přes SparkPost, Mailgun a další služby. V zásadě všechny tyto služby vám dají přístupy k SMTP serveru a ty jen nastavíte.

Tohle řešení je pohodlné hlavně proto, že prakticky **nemusíte řešit rozdílnost API** jednotlivých poskytovatelů a přepnout je mezi sebou, když by bylo potřeba, není žádná překážka.

### AWS SDK pro PHP

Místo posílání e-mailů s využitím SMTP můžete použít i SDK od Amazonu a posílat e-maily přes API. Toto řešení má minimálně dvě výhody - propustnost při odesílání e-mailů je větší než v případě využití SMTP a ke každému e-mailu vám API vrátí navíc identifikátor přiřazený k dané zprávě.

Instalaci AWS SDK můžete provést přes Composer takto:

```bash
composer require aws/aws-sdk-php
```
V AWS si pak vytvoříte uživatele, který bude mít oprávnění pracovat se službami SES skrze API a vygenerujete tak přístupové údaje, které se pak použijí při konfiguraci.

```php
$options = [
   'region' => 'eu-west-1', // závisí na vybraném regionu
   'version' => 'latest', // verze API
   'credentials' => [
       'key' => 'xxx', // vygeneruje se v IAM
       'secret' => 'yyy' // vygeneruje se v IAM
]];

$sesClient = new \Aws\Ses\SesClient($options);

$request = [];
$request['Source'] = 'odesilatel@example.com';
$request['Destination']['ToAddresses'] = ['prijemce@example.com'];
$request['Message']['Subject']['Data'] = 'PHP Předmět';
$request['Message']['Body']['Html']['Data'] = 'Máme rádi PHP!';
$request['Message']['Body']['Text']['Data'] = 'Máme rádi PHP textově!';

try {
    $result = $sesClient->sendEmail($request);
    $messageId = $result->get('MessageId'); // vrátí id e-mailu
} catch (\Aws\Ses\Exception\SesException $e) {
    // když se nepodaří e-mail odeslat
}
```

Podle mých testů **propustnost posílání e-mailů přes API je větší než přes SMTP**, proto pokud vám jde o to zvládnout v co nejkratším čase poslat co nejvíce e-mailů, může vám použití API pomoci.

Jako další výhodu vidím, že dostanete ke každé zprávě přiřazený unikátní identifikátor, který lze pak použít, pokud chcete sledovat, zda byl e-mail doručen, zda se jednalo o bounce apod.

## Jak získat feedback

Abyste byli schopni udržet dobrou reputaci vaší databáze, je potřeba **odhlašovat kontakty**, kterým kupříkladu nelze doručit e-mail, jejich e-mailová adresa je falešná nebo vás příjemce označí jako spam (tj. mimo jiné o vaše e-maily nemá pravděpodobně dále zájem) apod.

Proto je vhodné propojit SES s SNS (Simple Notification Service) a SQS (Simple Queue Service). Odkaz, jak nastavit, najdete na konci článku ve zdrojích. Každopádně jde o to, že zprávy o bounces a complaints budou končit ve frontách v SQS, odkud si je budete, třeba cronem, pravidelně stahovat a zpracovávat.

Níže je ukázka kódu, kde z fronty **queue-bounces**, kterou jsem si vytvořil a nastavil, aby tam Amazon posílal bounces, stahuji záznamy. Zpravidla budete na záznamy reagovat tím, že uživatele odhlásíte, tj. vypnete mu příjem e-mailů, smažete jej z listu příjemců apod.

```php
// 1. inicializace SQS klienta
$options = [
   'region' => 'eu-west-1', // závisí na vybraném regionu
   'version' => 'latest', // verze API
   'credentials' => [
       'key' => 'xxx', // vygeneruje se v IAM
       'secret' => 'yyy' // vygeneruje se v IAM
]];

$sqsClient = new \Aws\Sqs\SqsClient($options);

// 2. získat URL fronty
$urlResult = $sqsClient->getQueueUrl(['QueueName' => 'queue-bounces']);
$queueUrl = $urlResult->get('QueueUrl');

// 3. vrátí zprávy z fronty
$queueResult = $sqsClient->receiveMessage([
    'QueueUrl' => $queueUrl,
    'MaxNumberOfMessages' => 10
]);

if ($queueResult['Messages'] == null) {
    return;
}

// 4.
foreach ($queueResult['Messages'] as $message) {
    $receiptHandle = $message['ReceiptHandle'];
    $body = \Nette\Utils\Json::decode($message['Body']);
    $messageContent = \Nette\Utils\Json::decode($body->Message);

    if (isset($messageContent->mail)) {
        foreach ($messageContent->mail->destination as $email) {
            // 5. zde se odhlásí uživatel
        }
    }

    // 6. zprávu jsme zpracovali a odstraníme z fronty
    $sqsClient->deleteMessage([
        'QueueUrl' => $queueUrl,
        'ReceiptHandle' => $receiptHandle
    ]);
}
```
Postup je jednoduchý:
1. Inicializujete SQS klienta.
2. Získáte URL fronty.
3. Zavoláte frontu, abyste z ní získali položky ke zpracování.
4. Projdete jednotlivé zprávy, kde **ReceiptHandle** je ID položky ve frontě a v těle položky najdete samotnou zprávu. Příklad zprávy je uveden níže v JSON.
5. Zareagujete na událost - můžete třeba odhlásit uživatele podle e-mailu nebo podle messageId, pokud evidujete.
6. Položku jsme zpracovali a z fronty smažeme.

Příklad reálné zprávy (všimněte si, že k dispozici je **messageId**, které také vrací API při odeslání e-mailu):
```javascript
{
   "notificationType":"Bounce",
   "bounce":{
      "bounceType":"Permanent",
      "bounceSubType":"General",
      "bouncedRecipients":[
         {
            "emailAddress":"nejakyEmailNaSeznamu@seznam.cz",
            "action":"failed",
            "status":"5.1.1",
            "diagnosticCode":"smtp; 550 5.1.1 Sorry, no mailbox here by that name."
         }
      ],
      "timestamp":"2017-02-24T15:15:06.373Z",
      "feedbackId":"0102015a759b6f37-33adac0e-1d47-4a7a-a053-607e21a660fa-000000",
      "remoteMtaIp":"77.75.76.42",
      "reportingMTA":"dsn; a3-5.smtp-out.eu-west-1.amazonses.com"
   },
   "mail":{
      "timestamp":"2017-02-25T14:10:02.000Z",
      "source":"odesilatel@example.com",
      "sourceArn":"arn:aws:ses:eu-west-1:798010509261:identity/example.com",
      "sendingAccountId":"123456789",
      "messageId":"0102015a759b62be-d7e7e1b4-1cdc-4e89-8357-4873cb19f246-000000",
      "destination":[
         "nejakyEmailNaSeznamu@seznam.cz"
      ]
   }
}
```

A to je celá věda. Zpravidla se používají dvě fronty, jedna pro bounces, druhá pro complaints.

## Zkušenosti

Posílání e-mailů přes Amazon je **jednoduché a levné** v porovnání s jinými službami podobného typu. Ty sice často nabízí nějaký pokročilejší logging, ale to často stejně málokdo využije (aspoň co jsem viděl u pár firem).

Na produkci mám zkušenost s AWS SES třeba u [Tipli.cz](https://www.tipli.cz/), kde s tím není žádný problém. Navíc používáme oba způsoby odesílání e-mailů, jak přes SMTP, tak přes SDK. Pro notifikace se používá SMTP, pro newslettery, kde je důležitá rychlost odeslání velkého počtu e-mailů v krátkém časovém okně, se používá SDK.

Pokud vás cokoliv zajímá nebo zvažujete použití SES (a jeho reálné náklady), rád případně zodpovím. Ve článku jsem moc nechtěl probírat nastavení v Amazonu, protože si myslím, že to není tak složité. Kdyby bylo, stačí mi napsat, rád pomůžu, případně se podívejte na odkazy níže, kde jsem se snažil vybrat ty, které vám pomohou doplnit tyto informace.

## Odkazy

Zde uvádím ještě pár odkazů relevantních k problematice výše.

* [Amazon SES](http://docs.aws.amazon.com/ses/latest/DeveloperGuide/Welcome.html) - SES pro developery a kompletní kuchařka.
* [Jak posílat e-maily s odkazy na nastavení](http://docs.aws.amazon.com/ses/latest/DeveloperGuide/sending-email.html) - jak posílat e-maily, jaké jsou možnosti.
* [AWS SDK pro PHP](https://github.com/aws/aws-sdk-php) - AWS SDK pro PHP na Githubu.
* [Video: Jak nastavit Amazon SES](https://www.youtube.com/watch?v=9I5EkSNsKnk) - video o tom, jak nastavit SES na Amazonu.
* [Propojení SES, SNS a SQS](https://aws.amazon.com/blogs/ses/handling-bounces-and-complaints/) - jak nastavit sbírání bounces a complaints, jak propojit SES, SNS a SQS.
