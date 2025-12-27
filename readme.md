## Drago Authorization
Drago Authorization is a simple and dynamic access control list (ACL) management system built on top of the Nette Framework.
It provides an easy-to-use solution for managing roles, resources, and permissions, with built-in support for PHP 8.3 or higher.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://raw.githubusercontent.com/drago-ex/authorization/master/license.md)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Fauthorization.svg)](https://badge.fury.io/ph/drago-ex%2Fauthorization)
[![Tests](https://github.com/drago-ex/authorization/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/authorization/actions/workflows/coding-style.yml)
[![CodeFactor](https://www.codefactor.io/repository/github/drago-ex/authorization/badge)](https://www.codefactor.io/repository/github/drago-ex/authorization)
[![Coverage Status](https://coveralls.io/repos/github/drago-ex/authorization/badge.svg?branch=master)](https://coveralls.io/github/drago-ex/authorization?branch=master)

## Requirements
- PHP >= 8.3
- Nette Framework
- Composer
- Bootstrap

## Installation
```
composer require drago-ex/authorization
```

## Extension Registration
To use Drago Authorization in your Nette application, register the extension in your `config.neon` file:
```neon
extensions:
	- Drago\Authorization\DI\AuthorizationExtension
```

# Usage
## Use Trait in Base Presenter for Access Control
You can use the `Authorization` trait in your base presenter to manage access control and redirect users to the login page if needed.

```php
use Drago\Authorization\Authorization

// Redirect to a specific login presenter or module
private string $loginLink = ':Module:Presenter:';
```

## UUse Trait in Presenter for Access Control Settings
In each presenter, use the `AuthorizationControl` trait to manage authorization control.
```php
use Drago\Authorization\Control\AuthorizationControl
```

## Component Creation and Configuration
Here’s how to create and configure the main components for managing roles, permissions, and resources:
```php
// Minimum configuration to create components.

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
```

You can also configure custom templates for the components:
```php
// Set custom templates for controls
$control->templateControl = __DIR__ . '/path/to/file.latte';
$control->templateGrid = __DIR__ . '/path/to/file.latte';

// Insert a translator for multi-language support
$control->translator = $this->getTranslator();
```

## Use Components in Latte
Once the components are configured, you can render them in your Latte templates:
```latte
{control permissionsControl}
{control rolesControl}
{control resourcesControl}
{control privilegesControl}
{control accessControl}
```
