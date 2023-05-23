# Welcome to MyBlog 👋
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#)
[![Twitter: tomcdj71](https://img.shields.io/twitter/follow/tomcdj71.svg?style=social)](https://twitter.com/tomcdj71)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/193fb464761e4d38b5248a686e6fedcc)](https://app.codacy.com/gh/tomcdj71/MyBlog/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

> 5th course of OpenClassrooms - Developpeur d'applications PHP/Symfony formation

## Pre-requesites : 
- PHP 8.2 ('intl' and sqlite3 extension enabled)
- Composer
- npm (I used pnpm)
- sqlite 3
---

## Install

```sh
git clone https://github.com/tomcdj71/MyBlog
cd MyBlog
composer install --no-dev --optimize-autoloader
pnpm -C public/assets install
```

## Usage

You need to copy or rename the .env.php.example to .env.php in src/Config before starting this application.
```sh
cp -pR src/Config/.env.php.example src/Config/.env.php
```
Now, you can start the application :
```sh
php -S localhost:8000 -t public/
```

## Mailing

Don't forget to setup your mailer DSN informations in the .env.php
You can also use [MailCatcher](https://mailcatcher.me) if you are on dev environment and test the full application.
If you don't know how to install it, here's the How-To :
```sh
sudo apt-get update && sudo apt-get install -yqq build-essential software-properties-common libsqlite3-dev ruby-dev
gem install mailcatcher
mailcatcher
```
*[source](https://blog.eldernode.com/install-mailcatcher-on-ubuntu-20-04/)*

Then, you can browse to [http://127.0.0.1:1080/](http://127.0.0.1:1080/) and start catching emails.

## Database
If you want to rebuild the sqlite database, you need to do the following : 
```sh
sqlite3 var/database.db < var/db_dump.sql
```

*NOTE: all accounts passwords are* `pass1234`

## Author

👤 **Thomas Chauveau**

* Twitter: [@tomcdj71](https://twitter.com/tomcdj71)
* Github: [@tomcdj71](https://github.com/tomcdj71)

## Show your support

Give a ⭐️ if this project helped you!


***
_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_