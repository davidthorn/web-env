# Base Springlane [CraftCMS] Project

This repository provides a base project for development of modules, plugins, extensions and general testing of CraftCMS.

For further information about why Springlane chose CraftCMS and interesting topics about CraftCMS please refer to the wiki documentation in confluence using the link below:

[https://wiki.office.springlane.de/display/SWP/Craft+CMS](https://wiki.office.springlane.de/display/SWP/Craft+CMS)

## Prerequisites

1. Docker installed -> see documentation [here](https://wiki.office.springlane.de/display/SWP/Docker)
2. Port 8081 free
3. SSH key has been added to git@deploy.office.springlane.de
4. git installed see documentation [here](https://wiki.office.springlane.de/display/SWP/git)
5. composer installed -> see documentation [here](https://wiki.office.springlane.de/display/SWP/Composer)
6. 755 access to `src` directory

## Installation

The quickest and easiest way at the moment for you to install this project is by using the ./install.sh command.

```bash
./install.sh
```

If you saw the following information outputted in the terminal, then it is more than likely that everything got installed correctly.

```bash
Generating a security key ... done (kwRkmuNlQgRdxepyCklml02sHTKFpk2y)
Generating an application ID ... done (CraftCMS--a6524586-7fb6-49b9-84c6-2addc8f50b88)
Craft is already installed!
*** installing spl-custom-plugin-handle
*** installed spl-custom-plugin-handle successfully (time: 0.110s) 
```

To know 100% that all if working, you should open up the admin portal of CraftCMS to be presented with the login screen, you can open the browser using the following link:

[http://localhost:8081/admin](http://localhost:8081/admin)

or run the following command in the terminal:
```bash
open http://localhost:8081/admin
```

If you have any problems installing the containers or something like that to do with docker, then follow the next step at your own risk.

I dont advise it but you can uncomment the line in the [`install.sh`](./install.sh) file that begins with DO NOT UNCOMMENT. This will remove all images that you currently have conflicting with this installation. 

> --rmi=all tell docker to remove all images that are relevant to this build.
```bash
docker-compose down \
                --remove-orphans \
                # --rmi=all # DO NOT UNCOMMENT - uncomment if you want a super-clean-install by removing all images.

```

## Example Plugin

CraftCMS requires that all plugins are installed in the plugins directory at the root level of the project. It is considered a plugin if it is installed through composer but not a external dependency.

The [`install.sh`](./install.sh) will also create a `plugins` folder in the `src` directory if it does not exist already, it will also delete it if it exists too, so that it can create all of its contents.

If you do not want this, then I suggest commenting out the following line of code:
```bash
rm -rf src/plugins
```

Once the plugins folder has been created, the script will then download and install the `basic-plugin` craft-plugin from `gitlab` so that this project has a plugin that developers can use for test any ideas with plugins that they may have.

If you would like to change the name or the `handle` of the plugin then you will need to update the `src/plugins/basic-plugin/composer.json` file with the new `name` and or `handle`:

```json
"extra": {
    "name": "Your New Plugin Name",
    "handle": "spl-custom-plugin-handle",
    "class": "springlane\\MyCustomPlugin"
}
```

If you do change the `handle`, then you will need to update the `ìnstall.sh` file once again so that it can install your plugin correctly.

> replace `spl-custom-plugin-handle` with your `handle`
```bash
docker exec -it craftcms_webphp php craft plugin/install <handle>
```

## Admin Access

The credentials required to access the admin portal are:

```bash
username: admin
password: password
```

## Localhost Port

If you require to change the port that this can run on, you will be required to change the port in the following files:

1. docker-compose.yml
2. install.sh

## Deployment

This project should not be used for production application, it is solely designed for developers to have a test environment that can be quickly installed and configured on their local machine or even test server.

## TODOs

If there are any tasks that require to be completed, they should be listed here below with any JIRA tickets numbers when possoble.

## Changes

Any changes that have been made will be defined here.