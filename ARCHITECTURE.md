# Architecture: magento2

## Purpose

Magento 2 is a full-featured PHP e-commerce platform for enterprise retail. It provides a modular component system, REST/GraphQL APIs, extensible UI components, a theming layer, and a comprehensive admin panel.

## Directory Structure

```
app/
  code/Magento/           — Core Magento modules (Catalog, Checkout, Customer, Sales, etc.)
  design/                 — Frontend and adminhtml themes
  etc/                    — Global configuration (di.xml, module list)
dev/
  tests/                  — Integration, functional, and static tests
generated/                — Auto-generated DI factories, interceptors, proxies
lib/
  internal/               — Magento's fork of Zend Framework components
  web/                    — Frontend JavaScript libraries
pub/
  index.php               — Web entry point
  static/                 — Compiled/deployed static assets
setup/                    — Installation wizard and CLI upgrade scripts
vendor/                   — Composer dependencies
```

## Module Structure (app/code/Magento/ModuleName/)

```
Api/                      — Service contracts (interfaces for public API)
Api/Data/                 — Data interfaces (DTOs)
Block/                    — View blocks (HTML rendering helpers)
Console/                  — CLI commands
Controller/               — HTTP controllers (one action class per endpoint)
Cron/                     — Scheduled job classes
etc/
  module.xml              — Module declaration and sequence
  di.xml                  — Dependency injection configuration
  events.xml              — Event observer declarations
  webapi.xml              — REST/SOAP API route declarations
  schema.graphqls         — GraphQL schema definitions
Helper/                   — Utility classes (deprecated pattern; prefer services)
Model/                    — Domain models and resource models
Model/ResourceModel/      — Database-layer (Magento 2 data mapper pattern)
Observer/                 — Event observers
Plugin/                   — Interceptors (before/after/around method interception)
Setup/                    — Install/upgrade schema and data scripts
Test/                     — Module unit and integration tests
UI/                       — UI component XML definitions
view/                     — Layout XML, templates, JavaScript components
```

## Key Design Decisions

- **Service contracts**: Public module APIs are defined as PHP interfaces in `Api/`; implementations are wired via `di.xml`. This enables substitution without core hacks
- **Plugin (interceptor) system**: Any public method on any injectable class can be intercepted with before/after/around plugins declared in `di.xml`, without inheritance
- **Generated code**: Factory classes, interceptors, and proxies are generated into `generated/` at deployment time by `bin/magento setup:di:compile`
- **Event system**: `Magento\Framework\Event\ManagerInterface::dispatch()` fires named events; observers are stateless listeners declared in `events.xml`
- **UI Components**: Admin grids, forms, and listing pages are defined in XML (`view/adminhtml/ui_component/*.xml`) and rendered by a JavaScript framework

## Extension Points

- Create a module under `app/code/Vendor/Module/` with `registration.php` and `etc/module.xml`
- Use `di.xml` preferences to override any service contract implementation
- Use plugins to intercept any public method on an injectable class
- Subscribe to events via `events.xml`

## Dependency Flow

```
HTTP request → pub/index.php
  → Magento\Framework\App\Http (front controller)
  → Router → Controller Action
  → Block/Template (layout XML → blocks → .phtml)
  → Response

CLI: bin/magento commandName
  → Magento\Framework\Console\Cli → Command class
```
