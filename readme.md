<p align="center">
  <img src="https://avatars0.githubusercontent.com/u/11717487?s=400&u=40ecb522587ebbcfe67801ccb6f11497b259f84b&v=4" width="100" alt="logo">
</p>

<h3 align="center">Drago Extension</h3>
<p align="center">Simple packages built on Nette Framework</p>

## Drago Authorization
Simple dynamic access control list management.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://raw.githubusercontent.com/drago-ex/authorization/master/license.md)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Fauthorization.svg)](https://badge.fury.io/ph/drago-ex%2Fauthorization)
[![Tests](https://github.com/drago-ex/authorization/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml)
[![Coverage Status](https://coveralls.io/repos/github/drago-ex/authorization/badge.svg?branch=master)](https://coveralls.io/github/drago-ex/authorization?branch=master)

## Technology
- PHP 8.0 or higher
- composer

## Installation
```
composer require drago-ex/authorization
```

## Extension registration
```neon
extensions:
	authorization: Drago\Authorization\DI\AuthorizationExtension
```

## Use trait in base presenter for access control

```php
use Drago\Authorization\Authorization
```

## Use trait in presenter for settings access control

```php
use Drago\Authorization\Control\AuthorizationControl
```

## Component creation and configuration

```php
// Minimum configuration.
protected function createComponentPermissionsControl(): PermissionsControl
{
	$control = $this->permissionsControl;
	return $control;
}

protected function createComponentRolesControl(): RolesControl
{
	$control = $this->rolesControl;
	return $control;
}

protected function createComponentResourcesControl(): ResourcesControl
{
	$control = $this->resourcesControl;
	return $control;
}

protected function createComponentPrivilegesControl(): PrivilegesControl
{
	$control = $this->privilegesControl;
	return $control;
}

protected function createComponentAccessControl(): AccessControl
{
	return $this->accessControl;
}

// Configure a custom form template.
$control->setTemplateFile(__DIR__ . '/path/to/file');

// Configure a custom records template.
$control->setTemplateFile(__DIR__ . '/path/to/file', 'records');

// Inserting a translator.
$control->setTranslator(...);
```

## Use components in latte
```latte
{snippet permissions}
	{control permissionsControl}
{/snippet}

{snippet permissionsRecords}
	{control permissionsControl:records}
{/snippet}
```

```latte
{snippet roles}
	{control rolesControl}
{/snippet}

{snippet rolesRecords}
	{control rolesControl:records}
{/snippet}
```

```latte
{snippet resources}
	{control resourcesControl}
{/snippet}

{snippet resourcesRecords}
  {control resourcesControl:records}
{/snippet}
```

```latte
{snippet privileges}
	{control privilegesControl}
{/snippet}

{snippet privilegesRecords}
	{control privilegesControl:records}
{/snippet}
```

```latte
{snippet access}
	{control accessControl}
{/snippet}

{snippet accessRecords}
	{control accessControl:records}
{/snippet}
```

## Use Naja.js for reset form
```latte
{control resetControl}
```
