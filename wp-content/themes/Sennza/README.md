# Sennza's Corporate Site
This is the repository for the themes and plugins used on http://www.sennza.com.au/

## Prerequisites

If you've been allocated a task that involves updating the theme or creating a new plugin for http://www.sennza.com.au then you've come to the right place!

You'll need to have the following installed:

1. [Vagrant](http://vagrantup.com/)
2. [VirtualBox](https://www.virtualbox.org/)
3. [Node.js](http://nodejs.org/)
4. [Ruby](https://www.ruby-lang.org/en/)
5. [Grunt](http://gruntjs.com/)
6. [Git](http://git-scm.com/)

Some operating systems come with some of this software pre-installed. If you aren't sure then you can check by using a terminal and running the following commands:

```
vagrant --version
VirtualBox
nodejs --version
ruby --version
grunt --version
git --version
```

## Setup

Once you've installed all the software you'll now be ready to setup your local development environment by doing the following:

1. Cloning [Chassis](https://github.com/sennza/Chassis). `git clone --recursive git@github.com:sennza/Chassis.git sennzasite` *There are [prerequisites](https://github.com/sennza/Chassis#prerequisites) there that you'll need to check too*
2. `cd sennzasite`
3. Cloning this repository. `git clone --recursive git@github.com:sennza/Sennza.git content`
4. `cd content/themes/sennzav3`
5. `npm install` or `sudo npm install`
6. `cd ../../../`
7. `vagrant up`

*N.B. If this is the first time you've done all of the above then you'd better go grab a coffee (until we build a Sennza base box)*

** If you see an error on your first `vagrant up` it's usually worth running `vagrant up` again just in case **

## Using Grunt during development

If you're working on the theme then you'll need to change directories to your theme e.g. `cd content/themes/sennzav3` and run the following command:

```
grunt watch
```

Grunt will generate your CSS and minify your JavaScript on the fly as you go.

## Using WP-CLI

Because we've bundled a Puppet module for [WP-CLI] (http://wp-cli.org/) we have the ability to speed up the process of installing plugins, themes and importing content. To do this we need to SSH into your Vagrant Box using either the Terminal or PuTTY on Windows.

### Installing a plugin with WP-CLI

SSH into your Vagrant box and change directories to your WordPress root folder

```
cd /vagrant/wp
```

Check that WP-CLI is installed correctly by running
```
wp --info
```

You should see the following output
```
PHP binary:     /usr/bin/php5
PHP version:    5.4.23-1+sury.org~precise+1
php.ini used:   /etc/php5/cli/php.ini
WP-CLI root dir:        /usr/local/src/wp-cli/vendor/wp-cli/wp-cli
WP-CLI global config:
WP-CLI project config:
WP-CLI version: 0.14.0-alpha
```

You can install a plugin by running a command like this:

```
wp plugin install akismet
```

You can even install and active a plugin in one command

```
wp plugin install akismet --activate
```

### Activate a plugin

If a plugin exists in your repository via a submodule or a manual install then you can activate it using WP-CLI as follows:

```
wp plugin activate wordpress-importer
```

### Import the sites content or Theme Unit Test data

You can import content via WP-CLI with the command:

```
wp import ../content/theme-unit-test-data.xml --authors=skip
```
If you want to create users who doesn't exist then use this command:

```
wp import ../content/sennza.wordpress.2014-01-09.xml --authors=create
```