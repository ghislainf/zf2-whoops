ZF2 module, integrated [whoops](https://github.com/filp/whoops)

## Module installation
  1. `cd my/project/directory`
  2. create a `composer.json` file with following contents:

     ```json
     {
         "require": {
             "ghislainf/zf2-whoops": "dev-master"
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
  6. copy `config/zf2-whoops.local.php` in `my/project/directory/config/autoload`
  7. edit `my/project/directory/config/autoload/zf2-whoops.local.php`
