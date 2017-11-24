# Changelog

The project follows Semantic Versioning (http://semver.org/)

## 4.3.0 - tbd
### Added
- event listener for failed slmQueue jobs that send an email to all queue admins

## 4.2.0 - 2017-11-17
### Added
- shutdown the slmQueue worker when the entityManager was closed (due to an
  exception) so it can be restarted by supervisor with a fresh instance

## 4.1.0 - 2017-10-10
### Added
- test & cs config

### Changed
- updated dependencies
- applied code styling

## 4.0.1 - 2017-02-02
### Fixed
- dependencies

## 4.0.0 - 2017-02-02
### Added
- route/navigation/action/form to support the new notification settings
  in Vrok\Entity\User
### Changed
- DB schema update is required through changes in VrokLib 4.0.0
- require PHP 7.1+

## 3.1.0 - 2016-12-29
### Changed
- enabled Vrok\Mvc\View\Http\ErrorLoggingStrategy by default to also log
  application internal errors (that result in the error page shown)

## 3.0.1 - 2016-10-24
### Fixed
- lazy service config
- vier helper case sensitive since ZF3

## 3.0.0 - 2016-10-13
### Changed
- require PHP 7.0+
- require ZF3, implemented ZF3 compatibility

## 2.0.0 - 2016-09-02
### Changed
- bumped vrok-lib to backward incompatible version
- removed usage of currentUser() helpers

## 1.0.0 - 2016-08-29