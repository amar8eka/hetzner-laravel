# Changelog

All notable changes to `hetzner-laravel` will be documented in this file

## Unreleased

- support Laravel 13
- support multiple Hetzner projects, each with its own API token, via `HetznerLaravel::project('name')`
- add `HetznerLaravel::withToken($token)` for tokens only known at runtime
- add `HetznerLaravel::setDefaultProject()` to switch the default project at runtime
- add `dnsZones()` to the facade docblock
- fix reading response data as properties, e.g. `$response->server`

## 1.0.0 - 2025-10-19

- initial release
