# SilverWare Google Maps Module

[![Latest Stable Version](https://poser.pugx.org/silverware/google-maps/v/stable)](https://packagist.org/packages/silverware/google-maps)
[![Latest Unstable Version](https://poser.pugx.org/silverware/google-maps/v/unstable)](https://packagist.org/packages/silverware/google-maps)
[![License](https://poser.pugx.org/silverware/google-maps/license)](https://packagist.org/packages/silverware/google-maps)

Provides a Google Map component with customisable markers for use with [SilverWare][silverware] apps.

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Issues](#issues)
- [To-Do](#to-do)
- [Contribution](#contribution)
- [Maintainers](#maintainers)
- [License](#license)

## Requirements

- [SilverWare][silverware]
- [SilverWare Google Module][silverware-google]

## Installation

Installation is via [Composer][composer]:

```
$ composer require silverware/google-maps
```

## Configuration

In order for the map component to work, you will need to create a Google API Key. The key can be entered either into
the `SiteConfig` area of the CMS (in SilverWare settings), or you can define the API key using YAML config:

```yml
SilverWare\Google\API\GoogleAPI:
  api_key: '<paste-api-key-here>'
```

## Usage

### Google Map Component

This module provides a `GoogleMapComponent` for use with your SilverWare app. The map component can be added to
your SilverWare templates, layouts or panels using the CMS. After creating a map, you'll need to define the latitude
and longitude to set the location for the map.

On the Style tab for the component, you may select Auto or Manual for the height mode. Auto mode will always
maintain the map dimensions on any device or screen. Manual mode requires the pixel height of the map to be
entered into the "Height manual" field. In manual mode, the map will always remain at the height entered.

On the Options tab, you may select the default zoom level for the map, and also the type of map from the following
options (Roadmap is the default type):

- Roadmap
- Satellite
- Hybrid
- Terrain

### Map Markers

By enabling the "Show marker for location" option on the Main tab, you may define the title, label and content for the
default map location marker.

On the Markers tab, you may add additional markers to the map, each with it's own coordinates, title, label and content.
It is recommended to use a label of short length for each marker (e.g. a single letter or number) due to space

## Issues

Please use the [GitHub issue tracker][issues] for bug reports and feature requests.

## To-Do

- add support for geocoding of map locations (e.g. via street address)

## Contribution

Your contributions are gladly welcomed to help make this project better.
Please see [contributing](CONTRIBUTING.md) for more information.

## Maintainers

[![Colin Tucker](https://avatars3.githubusercontent.com/u/1853705?s=144)](https://github.com/colintucker) | [![Praxis Interactive](https://avatars2.githubusercontent.com/u/1782612?s=144)](https://www.praxis.net.au)
---|---
[Colin Tucker](https://github.com/colintucker) | [Praxis Interactive](https://www.praxis.net.au)

## License

[BSD-3-Clause](LICENSE.md) &copy; Praxis Interactive

[composer]: https://getcomposer.org
[silverware]: https://github.com/praxisnetau/silverware
[silverware-google]: https://github.com/praxisnetau/silverware-google
[issues]: https://github.com/praxisnetau/silverware-google-maps/issues
