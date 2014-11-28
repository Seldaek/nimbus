# Nimbus Framework

Nimbus leverages the cloud to store modern PHP web applications free of charge.

It offers PHP evaluation through a free Heroku instance at https://nimbusframework.herokuapp.com/ and data persistence facilitated by the goo.gl URL shortener.

## Features:

- Free cloud hosting
- Fully decoupled controllers
- Content versioning (URLs are forever)
- SSL encryption
- Follows TDD best practices
- JIT-compiled controllers
- Lowers cholesterol

## How to use

Write your controller code in `controllers/*.php`. `default.php` is the site entry point.

Run `php src/deploy.php` to deploy your site to the cloud and receive your unique URL.

## Known issues:

- Circular links are impossible to create within a single app, you can only use uni-directional links due to limitations in the cloud store.
- Every deployment gets a new unique URL due to the versioning feature.
