[![Codacy Badge](https://app.codacy.com/project/badge/Grade/193fb464761e4d38b5248a686e6fedcc)](https://app.codacy.com/gh/tomcdj71/MyBlog/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
# MyBlog

## Pre-requesites : 
---
- PHP 8.2 ('intl' extension enabled)
- Composer
- npm (I used pnpm)

`composer install --no-dev --optimize-autoloader`

then, navigate to public/install and do :
`pnpm install`

if you don't have pnpm, run `npm install`

For the mailing system, I use MailCatcher to catch the emails instead sending to a real address.

On a Linux computer, you can use [use this tutorial](https://blog.eldernode.com/install-mailcatcher-on-ubuntu-20-04/) to get it working,or follow these steps : 
`sudo apt-get update && sudo apt-get install -yqq build-essential software-properties-common libsqlite3-dev ruby-dev`

`gem install mailcatcher`

`mailcatcher --ip 127.0.0.1`

The `env.php` file includes an environment variable. If you set it to 'prod', you will not have exceptions when sending an email without MailCatcher started. You'll get Exception only in 'dev' environment.

Then, you'll find the web interface at [http://127.0.0.1:1080/](http://127.0.0.1:1080/)