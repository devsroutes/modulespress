<p align="center">
<a href="https://modulespress.devsroutes.co">
  <img src="https://modulespress.devsroutes.co/logo.png" alt="ModulesPress Logo" width="200"/>
</a>
</p>

<p align="center">
<a href="https://packagist.org/packages/modulespress/framework"><img src="https://img.shields.io/packagist/dt/modulespress/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/modulespress/framework"><img src="https://img.shields.io/packagist/v/modulespress/framework" alt="Latest Stable Version"></a>
<a href="https://www.npmjs.com/package/@modulespress/vite-plugin" target="_blank"><img src="https://img.shields.io/npm/v/@modulespress/vite-plugin.svg" alt="NPM Version" /></a>
<a href="https://packagist.org/packages/modulespress/framework"><img src="https://img.shields.io/packagist/l/modulespress/framework" alt="License"></a>
<a href="https://discord.gg/jtUn2X3VeH" target="_blank"><img src="https://img.shields.io/badge/discord-online-brightgreen.svg" alt="Discord"/></a>
</p>

<p align="center">
  <b>ModulesPress</b> is a modern WordPress plugin development framework that brings the power of <b>TypeScript</b>, <b>React</b>, and <b>NestJS-style architecture</b> to WordPress development.
</p>

---

## 🌟 Overview

ModulesPress supercharges your WordPress plugin development by introducing modern development practices and powerful features that make building complex plugins a breeze.

## ✨ Key Features

- 📂 **PSR-4 Autoloader** - All classes are loaded by default
- 🎯 **Modern Architecture** - NestJS-inspired modular design
- ⚡ **Vite Integration** - Bundling and hot reloading of assets
- 🎨 **Blade Templates** - Elegant templating with Laravel's Blade
- 🚀 **TypeScript & React** - First-class support for modern frontend
- 🛠️ **PHP 8+ Attributes** - Use decorators for clean, declarative code
- 🎭 **Dependency Injection** - Powerful IoC container
- 🔌 **Plugin Framework** - Built specifically for WordPress plugins
- 🔄 **Hot Module Replacement** - Instant feedback during development

## 🚀 Quick Start

### Installation

```bash
composer global require modulespress/cli
```

> **Note**: Make sure the composer global bin directory is in your PATH.

After installation, you can access the CLI using either `modulespress` or `mp` in your terminal.

### Documentation

For comprehensive documentation and guides, visit our [Official Documentation](https://modulespress.devsroutes.co/docs).

## 🎯 Framework Philosophy

ModulesPress is built on these core principles:

1. **Modern Development**: Embrace contemporary development practices
2. **Developer Experience**: Provide excellent tooling and debugging
3. **Type Safety**: Leverage TypeScript and PHP 8+ features
4. **Performance**: Optimize for production environments
5. **WordPress Integration**: Seamless WordPress compatibility

## 📝 Example Usage

Here's a simple example of a ModulesPress plugin:

```php
/**
 * Plugin Name: My Amazing Plugin
 * Description: Built with ModulesPress
 * Version: 1.0.0
 */

use MyPlugin\Modules\RootModule\RootModule; 
use ModulesPress\Foundation\ModulesPressPlugin;

if (!defined('ABSPATH')) exit;

final class MyAwesomePlugin extends ModulesPressPlugin {
   
    public const NAME = "My ModulesPress Plugin";
    public const SLUG = "my-modulespress-plugin";

    public function __construct() {
        parent::__construct(
            rootModule: RootModule::class,
            rootDir: __DIR__,
            rootFile: __FILE__
        );
    }

}

(new MyAwesomePlugin(__FILE__))->boot();
```

## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the [MIT License](LICENSE).

## 🌐 Community

Join our [Discord community](https://discord.gg/jtUn2X3VeH) for support and discussions.

## 📚 Documentation

For detailed documentation, please visit our [Wiki](https://modulespress.devsroutes.co/docs).

## 🤝 Support

If you need help or have questions:
- Open an [issue](../../issues)
- Join our [Discord community](https://discord.gg/jtUn2X3VeH)

## 🙏 Acknowledgments

Thanks to all our contributors and the open source community!