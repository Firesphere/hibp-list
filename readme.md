# HIBP list

This module is to import Have I Been Pwned JSON lists for a specific domain.

The module can be installed as a bare minimum system, and should not
be included in another system.

This module has no frontend.

## Installation

`composer create-project firesphere/hibp`

## Usage

Download the JSON files from Have I Been Pwned and put them in the
folder named `datafiles` in the document root of your website.

Run the task `/dev/tasks/ImportHIBP`

Then, log in to the backend system, and you can search and filter easily
through the "Breaches" system.

## Emails

In the Settings part of the backend, you can enable and configure
automated emails to the people found in a recent breach.

Note, Pastes aren't yet automatically able to email.