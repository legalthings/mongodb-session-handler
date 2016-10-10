MongoDB session handler
===

[![Build Status](https://travis-ci.org/legalthings/mongodb-session-handler.svg?branch=master)](https://travis-ci.org/legalthings/mongodb-session-handler)
[![Code Coverage](https://scrutinizer-ci.com/g/legalthings/mongodb-session-handler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/legalthings/mongodb-session-handler/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/legalthings/mongodb-session-handler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/legalthings/mongodb-session-handler/?branch=master)

The LegalThings MongoDB session handler stores sessions as structured data in MongoDB. This allows a process to fetch
and modify or remove a session based on its data.

## Use cases

### Remove user sessions when a password changes

When you change your password, you want to remove all the other sessions of the user. This is a security measure, the
old password may be comprimised and a hacker may already have logged in. Without invalidating his session, he can
continue to use the user account even after the password has changed.

### Cache and update user information

For performance reasons you might store the user's information like name, image, team name, etc in the session. When
a team changes it's name, you want to find all the sessions of the users within that team and update the information.

## Installation

```
composer require legalthings/mongodb-session-handler
```

**This libary uses the legacy Mongo driver.** If you're running PHP7 or simply are already using the new MongoDB
driver, please install [`alcaeus/mongo-php-adapter`](https://packagist.org/packages/alcaeus/mongo-php-adapter).

## Usage

```php
$mongo = new MongoClient();
$collection = $mongo->some_db->sessions;
$handler = new MongodbSessionHandler($collection);

session_set_save_handler($handler);
```

To create read-only sessions use `new MongodbSessionHandler($collection, 'r')`
