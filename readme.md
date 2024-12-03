
# DimplesPay Test API

## Requirements

- PHP 8.1 or higher
- Composer 2.7.1
- MySQL/MariaDB
- Symfony CLI 5.8 (Optional)

## Installation

1. Clone the repository
 `git clone https://github.com/Asmitta-01/DimplesPay-api.git`

2. Install dependencies
 `cd your-folder` then `composer install`

3. Configure environment variables: Create a .env file  and set the necessary environment variables, such as database credentials.

4. Load the initial data for your API, run the following command:

```bash
> php bin/console doctrine:fixtures:load
# Or
> symfony console doctrine:fixtures:load
```

This will populate your database with the fixtures defined in the [src/DataFixtures](./src/DataFixtures/) directory.

Note: Make sure you have configured your database connection in the .env file before running this command.
