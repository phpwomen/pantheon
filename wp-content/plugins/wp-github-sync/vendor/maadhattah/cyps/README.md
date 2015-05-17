**Cyps** is a YAML loader/dumper written in pure PHP. Given a YAML document, Cyps will return an array that
you can use however you see fit. Given an array, Cyps will return a string which contains a YAML document 
built from your data.

**YAML** is an amazingly human friendly and strikingly versatile data serialization language which can be used 
for log files, config files, custom protocols, the works. For more information, see http://www.yaml.org.

Cyps supports YAML 1.0 specification.

## Using Cyps

Using Cyps is trivial:

```
<?php
require_once "cyps.php";
$Data = Cyps::YAMLLoad('cyps.yaml');
```

or (if you prefer functional syntax)

```
<?php
require_once "cyps.php";
$Data = cyps_load_file('cyps.yaml');
```
