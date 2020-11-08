# wpgen

## Requirements

- [php](http://php.net)
- [nodejs](https://nodejs.org/en/download/)
- [composer](https://getcomposer.org/)

## Installation

**Windows**

1. Clone the repo
1. Run 'composer install' in the repo root directory
1. Add the repo directory to your system's PATH

**Mac**

Ensure composer is installed via:

```bash
brew install composer
```

Clone the repo, run composer install, and chmod the wpgen file so that it is executable.

```bash
git clone git@github.org:zawntech/wpgen.git
cd wpgen
composer install
chmod 755 wpgen
```

Create an alias for the wpgen executable by editing or creating ```~/.bashrc``` and adding the following:

```
alias wpgen='/absolute/path/to/wpgen/wpgen' 
```

Then restart your terminal and run ```wpgen``` to verify successful installation.


## Create Plugin

In order to use **wpgen**, first create a plugin.

Navigate to your WordPress plugins directory, then run:

```
wpgen create:plugin
```

This creates a basic plugin architecture with a namespace of your choice.  

A *wpgen.config.json* file is created in the plugin root 
directory. This file is **required** for all component functionality. 
Do not delete it.

Once your base plugin is generated, go to the new plugin directory and run:
 
```
composer install
```

Then, run wpgen to get a list of commands.

** create: prefixed commands must run from the plugin root directory.

** component: prefixed commands must be run from the specific component directory.