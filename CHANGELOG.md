# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.14] - 2020-04-08
### Changed
- The page dedicated to the purchase of packages in the user area has been modified 

## [1.0.13] - 2020-03-05

### Changed

- On CarsBonus entity, the method getUnplugEnable() manage the null value

## [1.0.12] - 2020-03-02

### Add

- New documentation management through views and pages are stored on database (this for FAQ, legal notes, etc.) (see [ticket 650](https://sharengo.freshdesk.com/a/tickets/650))
- New entities and repositories for Documents and Languages

## [1.0.11] - 2020-02-28

### Changed

- added to model Trip the possibility to modify the start date 

## [1.0.10] - 2020-02-28

### Changed

- added to class DatatableService the possibility to make filters on multiple columns 

## [1.0.9] - 2020-02-25

### Changed

- fix the trigger on wong payments during the pay invoice action
- update the NotifyCustomerPay

## [1.0.8] - 2020-02-17

### Changed

- Improvements inside AddressController (update trip's address non payable, filter some latitude or longitude invalid)

## [1.0.7] - 2020-02-13

### Changed

- Modified Location Service for reverse geocoding on https://nominatim.openstreetmap.org/

## [1.0.6] - 2020-02-11

### Changed

- Mails that ended with '@bcaoo.com' are blocked (see [ticket 778](https://sharengo.freshdesk.com/a/tickets/778) )

## [1.0.5] - 2019-12-10

### Added

- New payment gateway [Bankart](https://gateway.bankart.si/documentation/gateway)


## [1.0.4] - 2019-12-03

### Changed

- clean libraries smsgatewayme, criteo and Intercom (ticket 349)


## [1.0.3] - 2019-11-27

### Changed

- fix null in NugoService
- fix validator for personal id for Slovenia

## [1.0.2] - 2019-11-20

### Changed

- modification of the RecapService class and addition of the necessary orm models.

## [1.0.1] - 2019-11-18

### Changed

- fix ordering on columns send, consumed and cancelled, in reservations table