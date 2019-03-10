
# Webtrees 2 Custom Module: ⚶ Vesta Extended Relationships

This [webtrees](https://www.webtrees.net/) custom module provides an extended 'Relationships' chart. It also displays additional relationship information via the extended 'Families' tab, and the extended 'Facts and Events' tab.
The project’s website is [cissee.de](https://cissee.de).

## Contents

* [Features](#features)
* [Demo](#demo)
* [Download](#download)
* [Installation](#installation)
* [License](#license)

### Features<a name="features"/>

* The extended relationship chart provides additional options for calculating reationships. Common ancestors are included in the relationship path.

![Screenshot](chart.png)

* The module uses proper German relationship names, following [the German kinship system](https://de.wikipedia.org/wiki/Verwandtschaftsbeziehung#/media/File:European_kinship_system_de.svg) rather than using direct translations of [the system used in the English-speaking world](https://en.wikipedia.org/wiki/Family#/media/File:Relatives_Chart.svg): E.g. "Urgroßonkel 2. Grades" instead of "Cousin 3. Grades in aufsteigender Folge".
* In the extended 'Families' tab, the relationship to the default individual is displayed, as well as prior relationships between married individuals. The latter are calculated relative to the date of the marriage (to avoid the trivial relationship husband/wife), which allows us to show e.g. that someone married his brother's wife's sister.

![Screenshot](relatives.png)

* In the extended 'Facts and Events' tab, relationships to associated individuals are calculated similarly (relative to the date of the event).	
* Most features are configurable via module administration.
* The extended 'Facts and Events' tab, even when used standalone (without this module), fixes [Webtrees issue #1192](https://github.com/fisharebest/webtrees/issues/1192). When 'Events of close relatives' are activated, the inverse associations and relationships are also displayed:

![Screenshot](inverse.png)

### Demo<a name="demo"/>

See the <a href="./webtrees">[Müller Family tree](https://cissee.de/webtrees).

### Download<a name="download"/>

* Current version: 2.0.0-alpha.5.1
* Based on and tested with webtrees 2.0.0-alpha.5. Cannot be used with webtrees 1.x!
* Requires the ⚶ Vesta Common module ('vesta_common').
* Displays data via the ⚶ Vesta Families module ('vesta_families'). 
* Displays data via the ⚶ Vesta Facts and events module ('vesta_personal_facts'). 
* Download the zipped module, including all related modules, [here](https://cissee.de/vesta.latest.zip).
* Support, suggestions, feature requests: <ric@richard-cissee.de>. See also [this webtrees forum thread](https://www.webtrees.net/index.php/en/forum/4-customising/31587-extended-relationships-module-feature-requests).
* Issues also via <https://github.com/ric2016/extended_relationships/issues>

### Installation<a name="installation"/>

* Unzip the files and copy them to the modules_v4 folder of your webtrees installation. All related modules are included in the zip file. It's safe to overwrite the respective directories if they already exist (they are bundled with other custom modules as well), as long as other custom models using these dependencies are also upgraded to their respective latest versions.
* Enable the extended 'Families' module via Control Panel -> Modules -> Module Administration -> ⚶ Vesta Families.
* Enable the extended 'Facts and Events' module via Control Panel -> Modules -> Module Administration -> ⚶ Vesta Facts and Events.
* Enable the main module via Control Panel -> Modules -> Module Administration -> ⚶ Vesta Extended Relationships. After that, you may configure several options.
* Configure the visibility of the old and the extended 'Families' tab via Control Panel -> Modules -> Tabs (usually, you'll want to use only one of them. You may just disable the original 'Families' module altogether).		
* Configure the visibility of the old and the extended 'Facts and Events' tab via Control Panel -> Modules -> Tabs (usually, you'll want to use only one of them. You may just disable the original 'Facts and Events' module altogether).

### License<a name="license"/>

* **vesta_extended_relationships: a webtrees custom module**
* Copyright (C) 2019 Richard Cissée
* Derived from **webtrees** - Copyright (C) 2010 to 2019 webtrees development team.
* French translations provided by Michel Rivière.
* Dutch translations provided by Frans Weber.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
