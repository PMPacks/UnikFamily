# BlockCMD
![](http://isitmaintained.com/badge/resolution/kenygamer/BlockCMD.svg)
![](https://img.shields.io/github/release/kenygamer/BlockCMD/all.svg)
![](https://img.shields.io/github/downloads/kenygamer/BlockCMD/total.svg)

A PocketMine-MP plugin to block certain commands from being used by players in your server.

### How to authorize players to use blocked commands?
An operator blocked the /example command. Only players with the `blockcmd.access` or `blockcmd.access.example` permission node will be able to use it.

## Commands
|   Command   |             Usage                  |               Description              |
| ----------- | ---------------------------------- | -------------------------------------- |
| `/blockcmd` | `/blockcmd <add\|list\|remove> ` | Allows you to manage blocked commands. |
## Permissions
```yml
blockcmd:
 description: Allows access to all BlockCMD features.
 default: false
 children:
  blockcmd.access:
   description: Allows access to use all blocked commands.
   default: false
  blockcmd.command:
   description: Allows access to all BlockCMD commands.
   default: op
   children:
    blockcmd.command.blockcmd:
     description: Allows access to the blockcmd command.
     default: op
     children:
      blockcmd.command.blockcmd.add:
       description: Allows access to the BlockCMD add subcommand.
       default: op
      blockcmd.command.blockcmd.list:
       description: Allows access to the BlockCMD list subcommand.
       default: op
      blockcmd.command.blockcmd.remove:
       description: Allows access to the BlockCMD remove subcommand.
       default: op
```
