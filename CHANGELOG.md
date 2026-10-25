# Changelog

All notable changes to `hetzner-laravel` will be documented in this file

## Unreleased

- support Laravel 13
- support multiple Hetzner projects, each with its own API token, via `HetznerLaravel::project('name')`
- add `HetznerLaravel::withToken($token)` for tokens only known at runtime
- add `HetznerLaravel::setDefaultProject()` to switch the default project at runtime
- add `dnsZones()` to the facade docblock
- fix reading response data as properties, e.g. `$response->server`
- add `networks()->members()` for the new list network members endpoint
- add `dnsZones()->rrsets()->updateRecords()` to update the comments of DNS records
- add `location()` to servers and primary IPs, `datacenter()` now returns null since Hetzner removed it on 1 July 2026
- add `category()` and `locations()` to server types, and `deprecation()` to server types, load balancer types and images
- update examples to the `cpx22` server type, `cpx11` can no longer be ordered in EU locations

## 1.0.0 - 2025-10-19

- initial release
