---
id: 57
layout: post
title: "SOLID principy: Princip jedné odpovědnosti"
perex: |
    Princip jedné odpovědnosti (Single Responsibility Principle) je první z pěti principů SOLID (právě to S).

    Jde o metodu, díky které se kód stává přehlednějším a srozumitelnější. Říká třídě, že je zodpovědná pouze za jednu jedinou věc.
author: 30
related_items: [50]
tweet: "Urodilo se na blogu: #SOLID principy: Princip jedné odpovědnosti"
---

Video (1:26)

[![Video na Youtube](/assets/images/posts/2018/solid-1/youtube.png)](http://www.youtube.com/watch?v=GeezKhlAV-w)

Mám zde například třídu ```Person```, která se uchovává data osoby, ale také validuje email. Což je právě ta věc, která by dle tohohle principu měla být samostatně.

```php
<?php

class Person
{
    private $name;
    private $email;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        if ($this->validateEmail($email)) {
            $this->email = $email;
        }
    }

    private function validateEmail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        throw new InvalidArgumentException("Email is not valid email");
    }
}
```

Řešením zde může být to, že se bude požadovat konkrétní typ pro email.

Vytvoříme třídu ```Email```, která bude ihned při inicializaci validovat vstup. V konstruktoru proběhne validace. Následně ve třídě ```Person``` můžeme vyžadovat typ Email a budeme mít jistotu, že email vždy prošel validací.

```php
<?php

class Person
{
    private $name;
    private $email;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email)
    {
        $this->email = $email;
    }
}

class Email
{
    private $email;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email is not valid email");
        }

        $this->email = $email;
    }

    public function __toString()
    {
        return $this->email;
    }
}
```
