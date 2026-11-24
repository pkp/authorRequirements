# Author Requirements Plugin

This plugin allows certain author fields to be made optional.

## Requirements
* OJS/OMP 3.4.0, or 3.5.0-4 or later
* PHP 8.0 or later (8.2 or later for OJS/OMP 3.5.0)

## Installation

Install this as a "generic" plugin in OJS or OMP. The preferred installation method is through the Plugin Gallery. To install manually via the filesystem, extract the contents of this archive to an `authorRequirements` directory under `plugins/generic` in your OJS or OMP root.

## Configuration

The plugin allows certain author fields that are normally required to be optional. Optional fields may be selected on the plugin settings page. Currently, author emails are the only field that can be made optional using this plugin. Others may be added in the future.

## Usage

The plugin makes it possible to add an author to a list of contributors without using certain required fields (e.g. email). This is useful in cases where required information does not exist for authors.

Contributors without an email address are not sent any emails. This includes ORCID verification requests: the request can be made, but no email is sent, so the contributor's ORCID cannot be verified. Emails that would go only to contributors without an email address are still recorded in the submission's email log.

When exporting to JATS or native XML, contributors without an email address have an empty `<email>` element.

Making author emails optional changes the database so that any author can be saved without an email address. This change applies to the whole site and is not reversed by turning the setting off or disabling the plugin. Turning the setting off makes the email required again in the forms of that journal or press only.

## Author / License

Written by Erik Hanson for the [Public Knowledge Project](https://pkp.sfu.ca). Copyright (c) Simon Fraser University. Copyright (c) John Willinsky

Distributed under the GNU GPL v3.
