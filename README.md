# Bespoke-Support / Mime #

Reverse lookup from mime type to file extension

[![Build Status](https://travis-ci.org/BespokeSupport/Mime.svg?branch=master)](https://travis-ci.org/BespokeSupport/Mime)

Installation

Add to your project's composer.json

```
"scripts": {
    "post-autoload-dump": ["BespokeSupport\\Mime\\FileMimesGenerator::composerGenerate"]
}
```