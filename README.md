# Wize - CodeIgniter 3 CLI Enhancer 🚀

**Wizely develop your CodeIgniter 3 application!**
Wize is a CLI tool designed to supercharge your CodeIgniter 3 development experience with enhanced command-line utilities.

## 📥 Installation

You can install Wize using Composer.

```sh
composer require --dev wildanmzaki/wize
```

Next copy `vendor/wildanmzaki/wize/wize` file into your root project directory. Normally you can get the file just by running this command on bash terminal

```sh
cp vendor/wildanmzaki/wize/wize ./
```

Last, run `php wize init` to get your wize config file, and this file can make you easily modify the tool behaviour

```sh
php wize init
```

## 📌 Getting Started

Run the `wize list` command to see available options:

```sh
php wize list
```

## 🛠️ Available Commands

### **General Commands**

| Command            | Description                                 |
| ------------------ | ------------------------------------------- |
| `php wize author`  | Display tool's author(s) and contributor(s) |
| `php wize info`    | Display Wize tool information               |
| `php wize init`    | Initialize the Wize CLI in a project        |
| `php wize list`    | List all available commands                 |
| `php wize migrate` | Run all database migrations                 |
| `php wize serve`   | Serve the CodeIgniter 3 application         |
| `php wize version` | Display Wize version                        |

### **Creation Commands**

| Command                                        | Description                   |
| ---------------------------------------------- | ----------------------------- |
| `php wize create:command MyCommand`            | Create a new custom command   |
| `php wize create:controller MyController`      | Create a new controller       |
| `php wize create:helper general`               | Create a new helper file      |
| `php wize create:library MyLibrary`            | Create a new library          |
| `php wize create:migration create_users_table` | Generate a migration SQL file |
| `php wize create:model MyModel`                | Create a new model            |
| `php wize create:module MyModule`              | Create a new module           |

### **Migration Commands**

| Command                   | Description                                   |
| ------------------------- | --------------------------------------------- |
| `php wize migrate`        | Import all`.sql`files in`database/migrations` |
| `php wize migrate:fresh`  | Reset the database and re-run migrations      |
| `php wize migrate:status` | Show migration status                         |

### **Configuration Commands**

| Command               | Description                  |
| --------------------- | ---------------------------- |
| `php wize set:alias`  | Set additional command alias |
| `php wize set:config` | Configure Wize settings      |

---

## 🎯 Global Options

Wize supports global options that work with any command:

`-h` or `--help`, it would show command-specific help

Example:

```sh
php wize migrate --help
```

## 🎯 Usage Examples

### **Start Local Development Server**

Defaultly, it would running at `localhost` with port `8080`. But you can customize it like the example below:

```sh
php wize serve --host=127.0.0.1 --port=9000
```

Then open [http://127.0.0.1:9000](http://127.0.0.1:9000) in your browser.

### **Run Migrations**

```sh
php wize migrate
```

### **Reset & Re-run Migrations**

```sh
php wize migrate:fresh
```

### **Create a New Controller**

```sh
php wize create:controller Home
```

\*Note: You can specify module if you use HMVC paradigm when creating controller.

You can do it with 2 method:

1. Define module in the option like:

    ```sh
    php wize create:controller Login --module=auth
    ```

2. Place module name before the controller name

    ```sh
    php wize create:controller auth/Login
    ```

---

## 🛠️ Configuration

When running `php wize init`, Wize generates a configuration file:
📄 **wize.config.json**

Default structure:

```json
{
    "env": "development",
    "module": true,
    "theme": "default",
    "extend": "wize.extend",
    "paths": {
        "root": "/",
        "application": "application",
        "system": "system",
        "views": "application/views",
        "database": "database"
    },
    "migration": {
        "connection": "default",
        "table": "ci_migrations"
    },
    "aliases": {}
}
```

You can modify this file to customize the tool behavior. It can be modified easily just by running `set:config key value` command. Example:

```sh
php wize set:config theme customized
```

Go to deeper property:

```sh
php wize set:config migration.table my_migrations
```

## 📜 License

Wize is open-source and licensed under the MIT License.
See [LICENSE]() for details.

## 👨‍💻 Credits

Developed by **Wildan M Zaki**
Maintained by [contributors](https://github.com/WildanMZaki/wize/graphs/contributors).

For questions or support, open an issue on GitHub. 🚀
