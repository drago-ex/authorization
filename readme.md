<p align="center">
  <img src="https://avatars0.githubusercontent.com/u/11717487?s=400&u=40ecb522587ebbcfe67801ccb6f11497b259f84b&v=4" width="100" alt="logo">
</p>

<h3 align="center">Drago Extension</h3>
<p align="center">Simple packages built on Nette Framework</p>

## Drago Authorization
Simple dynamic access control list management.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://raw.githubusercontent.com/drago-ex/authorization/master/license.md)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Fauthorization.svg)](https://badge.fury.io/ph/drago-ex%2Fauthorization)
[![Build Status](https://travis-ci.com/drago-ex/authorization.svg?branch=master)](https://travis-ci.com/drago-ex/authorization)

## Technology
- PHP 8.0 or higher
- composer

## Installation
```
composer require drago-ex/authorization
```

## Extension registration
```php
extensions:
	authorization: Drago\Authorization\DI\AuthorizationExtension
```

## Use trait in base presenter for access control

```php
use Drago\Authorization\Authorization
```

## Use trait in admin presenter for settings access control

```php
use Drago\Authorization\Control\AuthorizationControl
```

## Use components in admin latte
```
{snippet permissions}
  {control permissionsControl}
{/snippet}

{snippet permissionsRecords}
  {control permissionsControl:records}
{/snippet}

{snippet roles}
  {control rolesControl}
{/snippet}

{snippet rolesRecords}
  {control rolesControl:records}
{/snippet}

{snippet resources}
  {control resourcesControl}
{/snippet}

{snippet resourcesRecords}
  {control resourcesControl:records}
{/snippet}

{snippet privileges}
  {control privilegesControl}
{/snippet}

{snippet privilegesRecords}
  {control privilegesControl:records}
{/snippet}
```

## Use Nette ajax for reset form
```
{control resetControl}
```
