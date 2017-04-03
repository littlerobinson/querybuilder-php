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
        data: [],
        columns: [],
        depth: 0
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        let $items = {};
        $items.name = "Liste des tables";
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
    computed: {
        jsonQuery: function () {
            let $query = {};
            $query.from = this.from;
            return JSON.stringify($query);
        }
    },
    methods: {
        search: function () {
            this.$http.post('/query.php', {action: 'execute_query_json', json_query: this.jsonQuery}).then(
                response => {
                    this.data = response.body.items;
                    this.columns = response.body.columns;
                    /// Display result
                    let research = new Vue({
                        el: '#app-result-research',
                        data: {
                            searchQuery: '',
                            gridColumns: request.columns,
                            gridData: request.data
                        }
                    });
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

