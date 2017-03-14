Installation
============

## Requirements

* Icinga Director (≥ 1.3.0)
* php-curl
* HTTP(s) connectivity to the iTop server

This module may work with older versions of Icinga Director but those are untested.

## Install the iTop module

As with any Icinga Web 2 module, installation is pretty straight-forward.
In case you're installing it from source, all you have to do is to drop the iTop module in one of your module paths.
You can examine (and set) the module path(s) in _Configuration > Application_.
In a typical environment you'll probably drop the module to `/usr/share/icingaweb2/modules/itop`.
Please note that the directory name MUST be `itop` and not `icingaweb2-module-itop` or anything else.

Last but not least go to _Configuration > Modules_ and enable the **itop** module.
Otherwise you could also do so on CLI by running:
```sh
icingacli module enable itop
```

That's all, now you are ready to define your first [Import Source](20-ImportSource.md) definitions!
