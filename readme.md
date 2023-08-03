## Drago Authorization
Simple dynamic access control list management.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://raw.githubusercontent.com/drago-ex/authorization/master/license.md)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Fauthorization.svg)](https://badge.fury.io/ph/drago-ex%2Fauthorization)
[![Tests](https://github.com/drago-ex/authorization/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml)
[![CodeFactor](https://www.codefactor.io/repository/github/drago-ex/authorization/badge)](https://www.codefactor.io/repository/github/drago-ex/authorization)
[![Coverage Status](https://coveralls.io/repos/github/drago-ex/authorization/badge.svg?branch=master)](https://coveralls.io/github/drago-ex/authorization?branch=master)

## Technology
- PHP 8.0 or higher
- Bootstrap
- composer

## Installation
```
composer require drago-ex/authorization
```

## Extension registration
```neon
extensions:
	- Drago\Authorization\DI\AuthorizationExtension
```

## Use trait in base presenter for access control

```php
use Drago\Authorization\Authorization

// Add module for redirect to sign in.
private string $module = ':Module:Presenter:';
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
	return $this->permissionsControl;
}

protected function createComponentRolesControl(): RolesControl
{
	return $this->rolesControl;
}

protected function createComponentResourcesControl(): ResourcesControl
{
	return $this->resourcesControl;
}

protected function createComponentPrivilegesControl(): PrivilegesControl
{
	return $this->privilegesControl;
}

protected function createComponentAccessControl(): AccessControl
{
	return $this->accessControl;
}

// Configure a custom template.
$control->templateControl = __DIR__ . '/path/to/file.latte';
$control->templateGrid = __DIR__ . '/path/to/file.latte';


// Inserting a translator.
$control->translator = $this->getTranslator();
```

## Use components in latte
```latte
{control permissionsControl}
{control rolesControl}
{control resourcesControl}
{control privilegesControl}
{control accessControl}
```
