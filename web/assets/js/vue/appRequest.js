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
        selectTables: {},
        items: {},
        from: {},
        where: [],
        conditions: [],
        limit: 0,
        offset: 0,
        data: [],
        columns: [],
        searchQuery: '',
        sqlRequest: '',
        searchable: false,
        loading: false,
    },
    mounted () {
        let self = this;
        this._getDbObject(function () {
            let $items = {};
            $items.name = "Logiciel des inscrits";
            $items.firstParent = true;
            $items.display = true;
            $items.rows = [];
            /// Update database items
            for (let $tableName in self.dbObj) {
                if (self.dbObj[$tableName]['_table_visibility'] === true) {
                    $translation = self.dbObj[$tableName]['_table_translation'] !== null
                        ? self.dbObj[$tableName]['_table_translation']
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
            self.items = Object.assign({}, self.items, $items);
        });
    },
    methods: {
        search: function () {
            this.loading = true;
            /// create json query with from attribute
            let $query = {};
            $query.from = this.from;
            /// Add conditions to query (feed this.where)
            this.where = [];
            this.__addQueryConditions();
            $query.where = this.where;
            $query.limit = this.limit;
            $query.offset = this.offset;
            let jsonQuery = JSON.stringify($query);
            console.log(jsonQuery);
            this.$http.post('/query.php', {action: 'execute_query_json', json_query: jsonQuery}).then(
                response => {
                    this.loading = false;
                    this.data = response.body.items;
                    this.columns = response.body.columns;
                    this.sqlRequest = response.body.request;
                }, response => {
                    this.loading = false;
                    console.log('response callback', response)
                }
            );
        },
        spreadsheet: function () {
            this.$http.post('/query.php', {action: 'spreadsheet', columns: this.columns, data: this.data}).then(
                response => {
                    console.log(response);
                    window.open("data:application/vnd.ms-excel, " + response.body);
                }, response => {
                    console.log('response callback', response)
                }
            );
        },
        _getDbObject: function (callback) {
            this.$http.post('/query.php', {action: 'get_db_object'}).then(
                response => {
                    this.dbObj = response.body;
                    callback();
                }, response => {
                    console.log('response callback', response)
                }
            );
        },
        __addQueryConditions: function () {
            for (let $condition in this.conditions) {
                let $where = {};
                let $logicalOperator = this.conditions[$condition].logicalOperator;
                let $field = this.conditions[$condition].field;
                let $ruleOperator = this.conditions[$condition].ruleOperator;
                let $value = this.conditions[$condition].value;

                $where[$logicalOperator] = {};
                $where[$logicalOperator][$field] = {};
                $where[$logicalOperator][$field][$ruleOperator] = [];
                $where[$logicalOperator][$field][$ruleOperator].push($value);

                this.where.push($where);
            }
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


