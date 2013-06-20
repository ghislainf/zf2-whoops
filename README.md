ZF2 module, integrated [Whoops](https://github.com/filp/whoops) and support for [Zf2 Logger](https://www.google.ee/search?q=zf2+logger&oq=zf2+Logger)

-----

![Whoops!](http://i.imgur.com/xiZ1tUU.png)

**Whoops** is an error handler base/framework for PHP. Out-of-the-box, it provides a pretty
error interface that helps you debug your web projects, but at heart it's a simple yet
powerful stacked error handling system.

## Module installation
  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "inditel/zf2-whoops": "dev-master"
         }
     }
     ```
  3. install composer via `curl -s http://getcomposer.org/installer | php` (on windows, download
     http://getcomposer.org/installer and execute it with PHP)
  4. run `php composer.phar install`
  5. open `my/project/directory/configs/application.config.php` and add the following key to your `modules`, :

     ```php
     'Zf2Whoops',   // must be added as the first module
     ```
  6. copy `config/zf2-whoops.config.php` in `my/project/directory/config/autoload`
  7. edit `my/project/directory/config/autoload/zf2-whoops.config.php`
