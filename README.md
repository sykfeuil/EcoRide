# EcoRide
Web app for car travel

## Table of contents
- [Prerequisites](#Prerequisites)
- [Installation](#Installation)

## Prerequisites

Here is what I used for dev :
- Visual Studio Code : https://code.visualstudio.com
- GitBash : https://git-scm.com/downloads
- Composer : https://getcomposer.org/download/
- Symfony CLI : https://symfony.com/download
- PHP : https://www.php.net
- MySQL Workbench : https://dev.mysql.com/downloads/workbench/

## Installation

Clone the EcoRide master repository to your local environment on visual studio code for example :
- Open a folder with VSCode
- Open the console and type : ```git clone https://github.com/sykfeuil/EcoRide.git```

Once the repository is cloned you should have most of the symfony project with all controllers, templates, forms, assets.

Only the bundles used for dev should be missing, you can see the list in the composer.json file

Run ```composer install``` to install all packages listed in the composer.json. It will update the vendor folder

Now we need to compile the assets so the project looks good.

Run ```php bin/console asset-map:compile``` to compile the assets in the public folder.

In your .env file you need to configure :
- Your APP_ENV variable to set it to "dev" for dev environment or "prod" for using environment
- Your DATABASE_URL variable corresponding to your database service.

If you use MySQL Workbench like me it should be :
- DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/EcoRide?serverVersion=8.0.32&charset=utf8mb4"
- app : username to connect to DB
- !ChangeMe! : password

Then run ```php bin/console doctrine:database:create``` to create the database.

And ```php bin/console doctrine:migrations:migrate``` to update the database with EcoRide structure.

Finally run ```symfony server:start``` to launch the server.

It should be accessible on localhost:8000

