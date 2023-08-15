# Create ACF Block CLI

A CLI to streamline the process of creating ACF blocks. Quickly generate your Advanced Custom Fields blocks in your WordPress theme.
<!-- 
![CLI Screenshot](path-to-screenshot.png) 
_Optionally, include a screenshot of your CLI in action._ -->

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## Installation

### 1. Downloading the Package

You can install the CLI globally via Composer:

```
composer global require joeyfarruggio/create-acf-block
```


### 2. Ensure Global Composer Binaries are in Your PATH

If it's your first time installing a global Composer package, ensure that the global Composer binaries directory is in your system's PATH. This will allow you to run the `create-acf-block` command globally.

To find out the path to your global composer binaries:

```
composer global config bin-dir --absolute
```

The command above will output a path. You need to add this path to your system's PATH. Below are instructions based on your operating system:

#### macOS and Linux

Add the following to your `.bashrc`, `.bash_profile`, or `.zshrc` file:

```
export PATH=~/.composer/vendor/bin:$PATH
```


_Note: Depending on your setup, the path might be `~/.config/composer/vendor/bin` instead._

#### Windows

1. Right-click on the Computer icon and choose Properties.
2. Click on the Advanced system settings link.
3. Click on the Environment Variables button.
4. In the System Variables section, find the PATH variable, select it, and click Edit.
5. In the Edit Environment Variable window, add a semicolon to the end of the value and then append the path to your global composer binaries.
6. Click OK to save your changes.

## Usage

After installation, you can utilize the CLI tool globally:

`create-acf-block`

Follow the on-screen prompts to create your ACF block.

The first tme you run the command you will be see a series of config prompts. If you ever need to reset these you can run `create-acf-block set-config`.

## Troubleshooting

If you encounter any issues while running the command, ensure that:

- The Composer binaries path is correctly set in your PATH as per the installation instructions.
- You have the required permissions to execute the command.

For further assistance or to report a bug, please open an [issue](https://github.com/joseph-farruggio/create-acf-block-cli/issues).

## Contributing

Contributions are welcome!

## License

This project is licensed under the MIT License. See the LICENSE file for details.

---

Happy coding! 🚀