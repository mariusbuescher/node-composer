[![License](https://img.shields.io/packagist/l/mariusbuescher/node-composer)](LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/mariusbuescher/node-composer)](https://packagist.org/packages/mariusbuescher/node-composer) [![Tests](https://github.com/mariusbuescher/node-composer/workflows/Tests/badge.svg?branch=master)](https://github.com/mariusbuescher/node-composer/actions?query=workflow%3ATests)

# node-composer

> composer plugin for a better frontend setup

PHP projects mostly are Web-Applications. Many Web-Applications also need a frontend part which runs in the browser. In
modern Web-Development there often a whole build-chain connected to the frontend, so you can compile e.g. your scss, build
your JavaScript with webpack and optimize your images.

This plugin provides a way to automatically download and install the right version of node.js, npm and yarn. The binaries
are linked to the bin-directory specified in your composer.json.

After that your can use node, npm and yarn in your composer-scripts.

## Setup

The setup is pretty easy. Simply install the plugin in specify the node-version in your composer.json extra configs.

Example composer.json

```json
{
    "name": "my/project",
    "type": "project",
    "license": "MIT",
    "authors": [
        {
            "name": "Marius Büscher",
            "email": "marius.buescher@gmx.de"
        }
    ],
    "require": {
        "mariusbuescher/node-composer": "*"
    },
    "extra": {
        "mariusbuescher": {
            "node-composer": {
                "node-version": "4.8.3",
                "yarn-version": "0.22.0"
            }
        }
    }
}
```

## Configuration

There are three parameters you can configure: The node version (`node-version`), the yarn version (`yarn-version`) and
the download url template for the node.js binary archives (`node-download-url`).

In the node download url the following parameters are replaced:

- version: `${version}`
- type of your os: `${osType}`
- system architecture: `${architecture}`
- file format `${format}`

Example composer.json: 

```json
{
    // ...
    "extra": {
        "mariusbuescher": {
            "node-composer": {
                "node-version": "6.11.0",
                "yarn-version": "0.24.5",
                "node-download-url": "https://nodejs.org/dist/v${version}/node-v${version}-${osType}-${architecture}.${format}"
            }
        }
    }
}
```
