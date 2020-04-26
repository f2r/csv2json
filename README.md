# CSV2JSON

Suite à un défit lancé sur twitter, il était demandé de réaliser une commande pour faire de la conversion CSV en JSON.
Voici ce qui avait été proposé : https://gist.github.com/f2r/2f1e1fa27186ac670c21d8a0303aabf1

Ci dessous, des personnes qui ont relevées le challenge (si vous souhaitez, vous pouvez proposer d'autres versions) :

 - https://github.com/mathroc/exercice-fredbouchery-twitter-2020-04
 - https://github.com/louvelmathieu/csv2json
 - https://github.com/devster/csv2json
 - https://gist.github.com/Ydalb/9a39a655c3c8b5d129405c8dc33b3471
 - https://github.com/TuxBoy/Csv2JsonExo
 - http://www.maximepinot.com/uploads/csv2json_maximepinot.zip
 - https://github.com/RocIT-tech/fred-cli
 - https://github.com/maitrepylos/csvToJson
 - https://github.com/amenophis/csv2json
 - https://github.com/liorchamla/bouchery-csv2json
 
 Ici, un exemple de fichier CSV un peu spécial : 
```csv
 id | name   | value   | date
 1  | foo    | 5998772 | 2020-03-04
 2  | bar    | 5657102 | 2020-03-04
 3  | foobar |         | 2020-03-04
 4  | foobaz | 6657512 | 2020-03-04
 5  | bazfoo | 5587445 | 2020-03-04
 6  | barfoo | 1124587 | 2020-03-05
 7  | foofoo |         | 2020-03-05
 8  | bazbar | 8921863 | 2020-03-05
 9  | bazbaz | null    | 2020-03-05
 10 | barbar | 5586311 | 2020-03-05
 11 | barbaz | 5566488 | 2020-03-05
```

Et un fichier de mapping :
```
id    = integer
name  = string
############################
value = ?integer # ce champ est nullable
#########################
date  = date
```