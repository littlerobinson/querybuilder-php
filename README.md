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
    category_id:
        name: category_id
        _field_traduction: 'Catégorie'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    user_id:
        name: user_id
        _field_traduction: 'Utilisateur'
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
    _FK:
        category_id:
            tableName: category
            columns: category_id
            foreignColumns: id
            name: FK_2F4B2CA110298215
            options: { onDelete: null, onUpdate: null }
        user_id:
            tableName: registrant
            columns: user_id
            foreignColumns: id
            name: FK_2F4B2CA13304A716
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
        
user:
    _table_traduction: Utilisateur
    id:
        name: id
        _field_traduction: 'Identifiant'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    group_id:
        name: group_id
        _field_traduction: 'Groupe'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    firstname:
        name: firstname
        _field_traduction: 'Prénom'
        type: string
        default: null
        length: 100
        not_null: true
        definition: null
    lastname:
        name: lastname
        _field_traduction: 'Nom'
        type: string
        default: null
        length: 100
        not_null: true
        definition: null
    _FK:
        group_id:
            tableName: group
            columns: group_id
            foreignColumns: id
            name: FK_2F4B2CA110298216
            options: { onDelete: null, onUpdate: null }
    
group:
    _table_traduction: Groupe
    id:
        name: id
        _field_traduction: 'Identifiant'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    name:
        name: name
        _field_traduction: 'Nom du groupe'
        type: string
        default: null
        length: 50
        not_null: true
        definition: null
            
comment:
    _table_traduction: Commentaire
    id:
        name: id
        _field_traduction: 'Identifiant'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    post_id:
        name: post_id
        _field_traduction: 'Article'
        type: integer
        default: null
        length: null
        not_null: true
        definition: null
    content:
        name: content
        _field_traduction: 'Commentaire'
        type: string
        default: null
        length: 50
        not_null: true
        definition: null
    _FK:
        post_id:
            tableName: post
            columns: post_id
            foreignColumns: id
            name: FK_2F4B2CA110298256
            options: { onDelete: null, onUpdate: null }
```

### Requête
```json
{
   "from":{
      "post":{
         "0":"title",
         "1":{
            "category_id":{
               "0":"id",
               "1":"name"
            }
         },
         "2":{
            "user_id":{
               "0":"firstname",
               "1":"lastname",
               "3":{
                  "group_id":{
                     "0":"name"
                  }
               }
            }
         },
         "3":"id"
      }
   },
   "where":{
      "AND":{
         "group.name":{
            "EQUAL":[
               "ADMIN"
            ]
         }
      }
   },
   "orderBy":{
      "asc":{
         "user":[
            "name",
            "id"
         ]
      }
   }
}
```

### Sortie

```mysql
SELECT 
    post.title AS post_title, 
    category.id AS category_id,
    category.name AS category_name,
    user.firstname AS user_firstname,
    user.lastname AS user_lastname,
    group.name AS group_name,
    post.id AS post_id
FROM
    post AS post
LEFT jOIN
    category as category
ON 
    post.category_id = category.id
LEFT JOIN 
    user AS user
ON 
    post.user_id = user.id
LEFT JOIN
    group AS group
ON
    user.group_id = group.id
WHERE
    group.name = 'ADMIN'
ORDER BY
    user.name, user.id ASC;
```

### Tests
```
phpunit --bootstrap vendor/autoload.php  tests/
```