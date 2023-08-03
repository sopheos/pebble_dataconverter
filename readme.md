# Pebble/DataConverter

Bibliothèque de conversion de données structurés.

## Règle de transformation

Les règles de transformations peuvent être :
- un objet de conversion qui implémente `ConverterInterface`
- une fonction de rappel (`callback`)
- Un tableau associatif qui associe des noms de propriétés d'une donnée à une règle.
- Un objet ou un nom de classe à hydrater à partir du jeu de donnée.

## Chaînage

Chaque outil de conversion `ConverterInterface`
permet d'ajouter d'autres outils de conversions à executer ensuite.

- La méthode `one(mixed $rule): static` permet d'ajouter une règle qui va agir sur la donnée.
- La méthode `many(mixed $rule): static` permet d'ajouter une règle qui va agir sur chaque élement d'une donnée itérable.

## Liste des objets de conversion :

- `CallbackConverter` : Transforme une fonction de rappel (callback) en objet de conversion.
- `CollectionConverter` : Applique un objet de conversion a chaque élément d'une donnée itérable
- `DateTimeConverter` : Transforme une date d'un format à un entre
- `HydrateConverter` : Transforme un tableau associatif en un objet hydraté
- `JsonDecodeConverter` : Transforme une chaine en un tableau associatif. Retourne null en cas d'erreur.
- `JsonEncodeConverter` : Transforme un tableau ou un objet en Json.
- `MapConverter` : Associe les propriétés d'un objet ou d'un tableau associatif à une règle.

## Exemple :

````php
class Car
{
    public $number;
    public $model;
    public $date;
}

class User
{
    public $name;
    public $birthdate;
    public array $cars = [];
}

$input = json_encode([
    'name' => 'Toto',
    'birthdate' => '1980-10-03',
    'cars' => [
        [
            'number' => 1651984,
            'model' => 'renault',
            'date' => '2000-01-01'
        ],
        [
            'number' => 2061161,
            'model' => 'audi',
            'date' => '2021-08-19'
        ],
    ]
]);

$carConverter = MapConverter::create()
    ->map('model', function ($input) {
        return ucfirst($input);
    })
    ->map('date', DatetimeConverter::create('Y-m-d', 'd/m/Y'))
    ->one(Car::class);

$userConverter = JsonDecodeConverter::create()
    ->one([
        'birthdate' => DatetimeConverter::create('Y-m-d', 'd/m/Y'),
        'cars' => Converter::create()->many($carConverter)
    ])
    ->one(new User);


$output = $userConverter($input);
````

Résultat : 

````
User Object
(
    [name] => Toto
    [birthdate] => 03/10/1980
    [cars] => Array
        (
            [0] => Car Object
                (
                    [number] => 1651984
                    [model] => Renault
                    [date] => 01/01/2000
                )

            [1] => Car Object
                (
                    [number] => 2061161
                    [model] => Audi
                    [date] => 19/08/2021
                )

        )

)
````
