# Admin Tools

## Description

Plugin for Omeka Classic. Reunites several minor plugins and code snippets to give site administrators extra tools:

- **Site Under Maintenance**: blocks out from Public interface not-logged in users (and also from Admin interface some logged-in users), displaying instead an "Under Maintenance" sign (note: if wanting to customize sign, one can edit the style section in *views/shared/maintenance/maintenance.php* file).
- **User Manual**: allows for a User Manual (or other document) to be made available for logged-in users.
- **Cookie Bar**: adds to Public interface a header or footer bar with simple information about cookies and privacy policy via the jQuery cookiebar widget.
- **Limit Visibility to Own**: limits Item/Collection/Exhibit visibility to only the ones created by the user (Admin interface).
- **Public Edit Link**: adds a edit link to Items/Collections/Exhibits/Files/Simple Pages to Public interface for logged-in users.
- **Database Backup**: creates a backup copy of the Omeka database, storing it locally (in Omeka's **files** directory) and also making it available for download.
- **Sessions Table**: allows trimming of the Omeka table recording sessions, in case automatic trimming was not effectively working; a graph can be shown, to keep track of the new sessions.
- **Tags Table**: allows deleting all unused tags (i.e. tags not associated with any record).
- **Plugins**: allows to activate or deactivate all plugins at once.

When installed, the plugin creates an **Admin Tools** page accessible to Super User from the admin navigation sidebar; through the page one can put the website in maintenance mode, clear the languages cache and create a backup copy of Omeka's database. The plugin's configuration page lets fine tune this functions, plus many other ones.


## Installation
Uncompress files and rename plugin folder "AdminTools".

Then install it like any other Omeka plugin.


## Warning
Use it at your own risk.

It’s always recommended to backup your files and your databases and to check your archives regularly so you can roll back if needed.

## Troubleshooting
See online issues on the <a href="https://github.com/DBinaghi/plugin-AdminTools/issues" target="_blank">plugin issues</a> page on GitHub.

## License
This plugin is published under the <a href="https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html" target="_blank">CeCILL v2.1</a> licence, compatible with <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GNU/GPL</a> and approved by <a href="https://www.fsf.org/" target="_blank">FSF</a> and <a href="http://opensource.org/" target="_blank">OSI</a>.

In consideration of access to the source code and the rights to copy, modify and redistribute granted by the license, users are provided only with a limited warranty and the software’s author, the holder of the economic rights, and the successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or developing or reproducing the software by the user are brought to the user’s attention, given its Free Software status, which may make it complicated to use, with the result that its use is reserved for developers and experienced professionals having in-depth computer knowledge. Users are therefore encouraged to load and test the suitability of the software as regards their requirements in conditions enabling the security of their systems and/or data to be ensured and, more generally, to use and operate it in the same conditions of security. This Agreement may be freely reproduced and published, provided it is not altered, and that no provisions are either added or removed herefrom.

## Thanks
Many thanks to [Charles Butcher](https://reephamarchive.co.uk/) for his extensive testing and useful suggestions.

## Copyright
Copyright [Daniele Binaghi](https://github.com/DBinaghi), 2022

For their coding inspiration and contributions, many thanks to the following people:

- plugin-DatabaseBackup: copyright [Anne L'Hôte](https://github.com/annelhote), 2015 
- Omeka-plugin-Translations: copyright [Daniel Berthereau](https://github.com/Daniel-KM), 2018-2019
- omeka-plugin-eucookiebar: copyright [Digital Humanities at the University of Warwick](https://github.com/digihum), 2016
- omeka-plugin-Maintenance: copyright [Biblibre](https://github.com/BibLibre), 2016
- ProjectGuide: copyright [Eric C. Weig](https://github.com/libmanuk), 2019
- DeleteEmptyTags: copyright [Erin Bell](https://github.com/ebellempire), 2025
