# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 2.0.1 - 2025-06-10
### Fixed
- [issue 17](https://github.com/adinan-cenci/psr-7/issues/17): Fixed an issue with the Stream constructor.

---

## 2.0.0 - 2025-05-20
### Fixed
- [issue 13](https://github.com/adinan-cenci/psr-7/issues/13): Refactored code to comply with PSR-12.
- [issue 13](https://github.com/adinan-cenci/psr-7/issues/13): Improved documentation by addinc doc-blocks.
- [issue 15](https://github.com/adinan-cenci/psr-7/issues/15): Update dependencies and fix unit tests to comply with PSR-7.

### Changed
- Upgraded `psr/http-message` dependency to 2.0.0.

---

## 1.1.4 - 2024-04-21
### Fixed
- [issue 11](https://github.com/adinan-cenci/psr-7/issues/11): Fixed an error in `ServerRequest::get()`, `::cookie()` and `::server()`.

---

## 1.1.3 - 2023-09-09
### Fixed
- [issue 9](https://github.com/adinan-cenci/psr-7/issues/9): Fixed an error in `UploadedFile::moveTo()`.

---

## 1.1.2 - 2023-08-02
### Fixed
- [issue 3](https://github.com/adinan-cenci/psr-7/issues/3): `Stream::read($length)` will not return the specified length.
- [issue 4](https://github.com/adinan-cenci/psr-7/issues/4): Error when calling `Message::withProtocolVersion()`.
- [issue 7](https://github.com/adinan-cenci/psr-7/issues/7): Small improvement on `Stream::getSize()`.

---

## 1.1.1 - 2023-07-30
### Fixed
- [issue 1](https://github.com/adinan-cenci/psr-7/issues/1): `Stream::getSize()` return 0 when measuring `php://input`.

---

## 1.1.0 - 2023-02-02
### Added
- Added the ability of serializing `Stream` objects.

---

## 1.0.3 - 2023-01-31
### Fixed
- Fixed a limitation on `ServerRequest::post()` that prevented it from
  retrieving POST variables.

---

## 1.0.2 - 2023-01-08
### Fixed
- Fixed a typo in the package's name in the `composer.json` file.

---

## 1.0.1 - 2023-01-08
### Fixed
- Added license information to the `composer.json` file.
