# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]

## 1.1.0 - 2020-02-16

### Changed

- `times` no longer returns a collection. Instead it returns a `Factory` instance that is a clone of the previous factory which is configured to generate the given amount of models.

### Removed

- Removed `timesMake` method
- Removed `timesRaw` method

## 1.0.0 - 2020-02-16

- initial release

[Unreleased]: https://github.com/kodekeep/laravel-fabrik/compare/master...develop
