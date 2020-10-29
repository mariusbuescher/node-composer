# node-composer

> composer plugin for a better frontend setup

PHP projects mostly are Web-Applications. Many Web-Applications also need a frontend part which runs in the browser. In
modern Web-Development there often a whole build-chain connected to the frontend, so you can compile e.g. your scss, build
your JavaScript with webpack and optimize your images.

This plugin provides a way to automatically download and install the right version of node.js, npm and yarn. The binaries
are linked to the bin-directory specified in your composer.json.

After that your can use node, npm and yarn in your composer-scripts.

## Setup

Simply run this command in your console:

```bash
composer require mariusbuescher/node-composer
```

or if you want to use only for dev environment, you can run this command:
```bash
composer require --dev mariusbuescher/node-composer
```

Note: if for some reason, you want to install the package older than 1.2.2, you must [configure some composer.json values manually](#manual-configuration) before running this command. 

## Versioning

The package after 1.2.x version uses versioning similar to NodeJS. When a new NodeJS version is released, the package version also increases. 

The last number of the package version is internal number to make some difference for some internal package changes. 

So basically, the version after 1.2.x will look like this format: `NODEJS_VERSION-INTERNAL_PACKAGE_VERSION`. F.e., `15.0.0-1`.

## Configuration

Any package after 1.2.x has possibility automatically to configure what version to install. Although it can be configured manually, we do not recommend doing so, because this prevents such tools like [Dependabot](https://dependabot.com) to automatically detect when also NodeJS needs to be updated for a project.  

### Manual configuration

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
