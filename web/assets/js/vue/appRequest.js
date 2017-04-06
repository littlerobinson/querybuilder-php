var databaseConfigJson = $("#databaseConfigJson").val();

/* *********************************************************************************** */
/* ******************************* Bootstrap REQUEST ************************************* */
/* *********************************************************************************** */
let request = new Vue({
    el: '#app-request',
    http: {
        emulateJSON: true,
        emulateHTTP: true
    },
    data: {
        dbObj: {},
        items: {},
        from: {},
        listTables: [],
        checkedTables: [],
        checkedRows: [],
        foreignTables: [],
        foreignKeys: [],
        depth: 0,
        data: [],
        columns: [],
        searchQuery: ''
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        let $items = {};
        $items.name = "Logiciel des inscrits";
        $items.firstParent = true;
        $items.display = true;
        $items.rows = [];
        /// Update database items
        for (let $tableName in this.dbObj) {
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
    methods: {
        search: function () {
            /// create json query with from attribute
            let $query = {};
            $query.from = this.from;
            let jsonQuery = JSON.stringify($query);
            console.log(jsonQuery);
            this.$http.post('/query.php', {action: 'execute_query_json', json_query: jsonQuery}).then(
                response => {
                    this.data = response.body.items;
                    this.columns = response.body.columns;
                }, response => {
                    console.log('response callback', response)
                }
            );
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


