# Presto! by Sitback

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/3ca20fbf911a42debffcaccaffffefce)](https://www.codacy.com/app/sitback/presto?utm_source=github.com&utm_medium=referral&utm_content=Sitback/presto&utm_campaign=badger) [![Build Status](https://travis-ci.org/Sitback/presto.svg?branch=8.x-1.x)](https://travis-ci.org/Sitback/presto)

Presto is a Drupal 8 distribution with batteries included! It sets up some sane content editing defaults and helps you kickstart your site with Drupal Commerce.

## Installing Presto
The recommended way to install Presto is to use our [Composer project scaffolding](https://github.com/Sitback/presto-project):
```bash
composer create-project sitback/presto-project MYPROJECT --stability dev --no-interaction
```

You can also [download a pre-packaged tarball off drupal.org](https://www.drupal.org/project/presto), however, due to limitations of the drupal.org packager, **installing eCommerce is not supported with this installation method**.

**Note:** Only PHP 5.6 and above are officially supported. The profile should run under PHP 5.5 however it isn't tested and may have unforeseen quirks.

## Included in Presto

Presto includes the following functionality pre-configured and ready to use right out-of-the-box:

* An _Article_ content type with some pre-configured fields
* A _Basic Page_ content type with a [Paragraphs](https://www.drupal.org/project/paragraphs)-based body field
* Some pre-configured Paragraph types:
  - Textbox
  - Image
  - Promo bar
  - Divider
  - Carousel
  - Block (allows the embedding of Drupal blocks)
* An _Articles_ listing page (at `/articles`) which displays a paginated listing of articles, sorted by publish date
* A contact page (at `/contact`) that contains an embedded contact form
  - Submissions are emailed and also stored within Drupal
* Google Analytics installed (but not configured!)
* The ability to add meta tags to all entities on the site
* Automated Cron disabled by default
  - The recommendation is to [setup server cron instead](https://www.drupal.org/docs/7/setting-up-cron-for-drupal/configuring-cron-jobs-using-the-cron-command) ([the easiest way is to use Drush](http://www.drush.org/en/master/cron/))
  - You can always enable the `automated_cron` module if you have no other alternative
* A couple of pre-configured user roles: _Administrator_ & _Editor_
* The ability to share nodes on social media
* Pathauto for automatical URL aliasing
* Canonical redirect functionality
  - e.g. if you add an alias to _node:1_, users accessing `/node/1` will be redirected to the alias
* XML sitemap generation
* A shiny, magical theme, based off Bootstrap 4
* Optional, pre-configured eCommerce functionality, via Drupal Commerce.
  - See the [eCommerce](#ecommerce) section for full details.

To supply this functionality, Presto ships with the following contributed modules enabled by default:

* Admin Toolbar
* Contact Block
* Contact Storage
* Display Suite
* Google Analytics
* Paragraphs
* Pathauto
* Social Media
* Metatag
* Simple Sitemap
* Token
* View Mode Selector
* Redirect

## eCommerce

Presto ships with a pre-configured Drupal Commerce with the below functionality (this is optional and can be turned off via the installer!):

* A demo store that sells books, based in Australia
* Two products with variations: _Physical Book_ and _eBook_
  - Physical Book: _Paperback_ and _Hardcover_
  - eBook: _ePub_, _Mobi_, _PDF_, and _HTML_
* Flatrate shipping
* A dummy payment gateway for testing purposes
  - The _Commerce Stripe_ module is also included but not installed by default
* Two order order item types: _Digital Item_ and _Physical Item_
  - Physical items have shipping, digital items do not
* Tax (GST) setup for Australia
* A products listing view and page (at `/products`)

Enabling eCommerce installs the following contributed modules:

* Drupal Commerce (and its included modules)
* Drupal Commerce Shipping
* Physical Fields
* Commerce Variation Cart Form

## Known Issues

* If you embed a block with a form in it onto a node, via the _Component: Block_ Paragraph type, it may break the `node/edit` page for that node. We're monitoring an ongoing issue in the `block_field` module that should resolve this. ([#42](Sitback/presto#42))

## Migration from drupal 8.4.X to 8.5.x and media.

Process to follow:
 * Ensure your site is up to date: `cd web`, `../vendor/bin/drush updb`
 * Remove folders:
   * /vendor
   * /web/core
   * /web/modules/contrib
   * /composer.lock
 * Update your composer.json with: "drupal/core": "8.*",
 * If you composer.json contains some reference using media_entity, please update the versions accordingly.
 * Run: `cd ..`, `composer require sitback/presto:8.x-2.x`
 * Run: `composer require drupal/media_entity_browser:2.0-alpha1`
 * Then run the update database again to find if there is some dependencies to fix, and disable or fix them.
 * Run `cd web`, `../vendor/bin/drush updb -y`
 * Run `cd ..`, `composer require drupal/media_entity:2.0-alpha1`
 * Run `cd web`, `../vendor/bin/drush en media -y`
 * Run `../vendor/bin/drush updb -y`
 * Run `../vendor/bin/drush cr`
 * Your site should be up and running again.
