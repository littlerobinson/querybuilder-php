var databaseConfigJson = $("#databaseConfigJson").val();

/* *********************************************************************************** */
/* ******************************* Bootstrap REQUEST ************************************* */
/* *********************************************************************************** */
var request = new Vue({
    el: '#app-request',
    data: {
        dbObj: {},
        items: {},
        from: {},
        listTables: [],
        checkedTables: [],
        checkedRows: [],
        foreignTables: [],
        foreignKeys: [],
        depth: 0
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        var $items = {};
        $items.name = "Liste des tables";
        $items.firstParent = true;
        $items.display = true;
        $items.rows = [];
        /// Update database items
        for (var $tableName in this.dbObj) {
            if (this.dbObj[$tableName]['_table_visibility'] === true) {
                $translation = this.dbObj[$tableName]['_table_translation'] !== null
                    ? this.dbObj[$tableName]['_table_translation']
                    : null;
                $items.rows.push({
                    'name': $tableName,
                    'table': $tableName,
                    'translation': $translation,
                    'display': true,
                    'status': false,
                    'firstParent': true
                });
            }
        }
        this.items = Object.assign({}, this.items, $items);
    },
    computed: {
        jsonQuery: function () {
            var $query = {};
            $query.from = this.from;
            return JSON.stringify($query);
        }
    },
    methods: {
        search: function () {
            console.log('search result', this.jsonQuery);
            //this.$http.post('/', this.jsonQuery).then(successCallback, errorCallback);
            this.$http.post('/', this.jsonQuery).then((response) => {
                console.log(response);
            });
        }
    },
    filter: {
        getRows: function (path) {
            return this.items.filter(function (el) {
                return el.items == path;
            });
        }
    }
});
