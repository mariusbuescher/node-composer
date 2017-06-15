# node-composer

> composer plugin for a better frontend setup

PHP projects mostly are Web-Applications. Many Web-Applications also need a frontend part which runs in the browser. In
modern Web-Development there often a whole build-chain connected to the frontend, so you can compile e.g. your scss, build
your JavaScript with webpack and optimize your images.

This plugin provides a way to automatically download the right version of node.js and npm as a package. The binaries are
linked to the bin-directory specified in your composer.json.

After that your can use node and npm in your scripts.

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
                "node-version": "4.8.3"
            }
        }
    }
}
```
