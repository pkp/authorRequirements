# Author Requirements Plugin

This plugin allows certain author fields to be made optional.

## Requirements
* OJS/OMP 3.2.0 or higher
* PHP 5.3 or later

## Installation

Install this as a "generic" plugin in OJS or OMP. The preferred installation method is through the Plugin Gallery. To install manually via the filesystem, extract the contents of this archive to an `authorRequirements` directory under `plugins/generic` in your OJS or OMP root.

## Configuration

The plugin allows certain author fields that are normally required to be optional. Optional fields may be selected on the plugin settings page. Currently, author emails are the only field that can be made optional using this plugin. Others may be added in the future.

## Usage

The plugin makes it possible to add an author to a list of contributors without using certain required fields (e.g. email). This is useful in cases where required information does not exist for authors.

## Author / License

Written by Erik Hanson for the [Public Knowledge Project](https://pkp.sfu.ca). Copyright (c) Simon Fraser University. Copyright (c) John Willinsky

Distributed under the GNU GPL v3.
