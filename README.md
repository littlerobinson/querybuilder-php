# Query Builder SDK

Permet de générer des requête dynamiquement sur une base de données.
L'outil permet de mettre la configuration d'une base de données dans un fichier YML.

Un développeur peut modifier certaines valeurs de ce fichier de configuration pour par exemple mettre des traductions des champs d'une table.

Le query builder prend en entrée un fichier json avac les champs sur lequel requeter ainsi que les conditions.
De ce fichier il va contruire la requête, l'exécuter puis retourner le résultat.

## Exemples

### Fichier de configuration
```yaml
post:
    _table_traduction: Article
    id:
        name: id
        _field_traduction: 'Identifiant'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    title:
        name: title
        _field_traduction: 'Titre'
        type: string
        default: null
        length: 50
        not_null: true
        definition: null
    
    ...
    
    category_id:
        name: category_id
        _field_traduction: 'Catégorie'
        type: integer
        default: null
        length: null
        not_null: false
        definition: null
        FK:
            tableName: category
            columns: category_id
            foreignColumns: id
            name: FK_2F4B2CA110298215
            options: { onDelete: null, onUpdate: null }
            
category:
    _table_traduction: Catégorie
    id:
        name: id
        _field_traduction: 'Identifiant'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    title:
        name: name
        _field_traduction: 'Nom'
        type: string
        default: null
        length: 100
        not_null: true
        definition: null
```

### Requête
```json
{
    "from": {
        "post": [
            "id", "title", "content"
        ],
        "category": [
            "id", "name"
        ]
    },
    "where": {
        "AND|OR": {
            "category.name = != IN": [
                "info", "elec"
            ]
        }
    },
    "orderBy": {
        "asc": {
            "post": ["name", "id"]
        }
    }
}
```

### Sortie

```mysql
SELECT 
    post.id, 
    post.tittle, 
    post.content,
    category.id,
    category.name
FROM
    post AS post
LEFT jOIN
    category as category
ON 
    post.category_id = category.id
WHERE
    category.name in("info", "elec")
ORDER BY
    post.name, post.id ASC;
```

