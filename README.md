<!--
SPDX-FileCopyrightText: MindFabrik UG <support@mindfabrik.de>
SPDX-License-Identifier: CC0-1.0
-->

# CTF Viewer - Task list for tax advisors for NextCloud 22+
Place this app in **nextcloud/apps/**

## Description
This NextCloud App allows administrators or a defined group to assign tasks
to an other NextCloud user. In general, the app creates a .ctf file, which 
can be shared on every NextCloud share. 
The app acts as editor/viewer for .ctf files and presents the assigned tasks.

## Screenshots
### Add new Task
![Add new Task](/img/editor.jpg)
### Task Overview
![Task Overview](/img/taskoverview.jpg)
### E-Mail notification
![E-Mail Notification](/img/emailnotification.jpg)

## Current Limitations
The current version still has the following limitations, which will be fixed in further versions:
- The UI and E-Mail notification is only available in German
- Tax Advisor users must be part of a group called 'Kanzlei-Mitarbeiter' or 'admin'
- The E-Mail notification text is currently hard-coded
- The names 'Steuerberater/Kanzlei' and 'Mandant' are currently hard-coded and cannot be translated to other businesses.
- No multi-language support
- Requires still global JQuery

## Support
THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
