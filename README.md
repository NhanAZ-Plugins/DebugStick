# DebugStick

DebugStick lets an operator edit anvil and bamboo block properties in an Axolotl-PM world. It provides a tagged stick through `/debugstick` and stores each selected property per player.

## Requirements and compatibility

- Axolotl-PM API 5.0.0 or newer within the 5.x API series. The [build workflow](.github/workflows/build.yml) records the exact source revisions tested. Compatibility with every 5.x release has not been verified.
- PHP 8.1 or newer, as required by Axolotl-PM 5.
- Operator access by default, or both `debugstick.command` and `debugstick.use` permissions.

## Installation

Download the PHAR from [GitHub Releases](https://github.com/NhanAZ-Plugins/DebugStick/releases), place it in the server's `plugins` directory and restart the server.

## Usage

Run `/debugstick` as a player. Interact with an anvil or bamboo block while holding the given stick. Sneak and click to select a property. Click without sneaking to change the selected value. The current selection or value appears as a tip.

| Block | Selectable properties | Changes |
|---|---|---|
| Anvil | Facing, damage | Right click moves forward through the values. Left click moves backward. |
| Bamboo | Leaf size, ready, thick | Right click moves leaf size forward and left click moves it backward. Either click toggles ready or thick. |

The selection is stored separately for each player name while the server runs. A normal stick without the DebugStick item tag does not edit blocks. The plugin has no configuration file.

## Development and validation

The [build workflow](.github/workflows/build.yml) tests block state changes and permission handling against pinned Axolotl-PM source revisions. It uses [DevTools](https://github.com/NhanAZ/DevTools) to run PHPStan at maximum level, build the plugin PHAR and inspect the artifact. Exact tested revisions and results are available in [GitHub Actions](https://github.com/NhanAZ-Plugins/DebugStick/actions).

The tests use a world fixture. Interaction with a Bedrock client and every Axolotl-PM 5.x release has not been verified.

## Support

Join [NhanAZ Discord](https://discord.gg/j2X83ujT6c) for support.

## License

DebugStick is distributed under the [GNU General Public License version 3](LICENSE).
