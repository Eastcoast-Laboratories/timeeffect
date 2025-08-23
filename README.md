TIMEEFFECT
==========

TIMEEFFECT is a PHP/MySQL based, multi user time tracking system for recording time
employees spent on different projects. It includes facilities to manage agents,
customers and projects.
Users may generate reports and statistics as well as accounting reports in CSV 
and PDF format. This project has been refactored to PHP 8 and has moved to 
GitHub from Sourceforge, where you can see some sample 
images: https://sourceforge.net/projects/timeeffect/

The aim of TIMEEFECT is to provide project participants with time recording possibilities.

 - First: project workers are able to track their personal
   efforts spent on the several projects they are involved.
 - Second: project managers are able to view statistics about
   the amount of efforts spent on specific projects/customers.
 - Third: project accounters are able to extract accounting
   information about the projects for creation of invoices.

# 1. Dependencies
To run TIMEEFFECT you need a working MySQL database server (version 3.23 or higher).

Furthermore you need a running a webserver with PHP 8.4 or use docker.

# 2. Preparation
## 2.1 MySQL
Before you actually install the TIMEEFFECT package you have to create a new
database and a database user with SELECT, INSERT, UPDATE and DELETE rights on
the created database within your MySQL system.
By default the prepared database name is `timeffect_db`, the appropriate user
is `timeeffect` with the password `very_unsecure_timeeffect_PW1`. If you stick
to those parameters you won't have to change the data during the installation of
the system but it is recommended to select a secure password before you go live.

## 2.2	 PHP
To have TIMEEFFECT running you need to set the value of the directive
`short_open_tag` in your php.ini to `On` (`short_open_tag = On`). You can figure
out where your `php.ini` is located by creating a php script with the following
content: `<?php phpinfo(); ?>`.
By opening this script in your browser you will get a detailed overview of your
PHP settings.

# 3. Installation
extract the contents of this repository in a directory which is located under
the document root of your web server. Then follow [DEPLOYMENT.md](../DEPLOYMENT.md)

# 4. Customizing
After Installation you can further edit the generated file `include/config.inc.php` and `.env`
which is located in the root directory.

-----

## History of this project
Imported from https://sourceforge.net/projects/timeeffect/ with this script https://gist.github.com/rubo77/8f22193cf940837d000a996c7132dae0
initially 
