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
        depth: 0,
        data: [],
        columns: [],
        searchQuery: '',
        sqlRequest: ''
    },
    mounted () {
        console.log('mounted method in select app');
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
            /// create json query with from attribute
            let $query = {};
            $query.from = this.from;
            /// Add conditions to query (feed this.where)
            this.where = [];
            this.__addQueryConditions();
            $query.where = this.where;
            /*
             $query.limit = null;
             $query.offset = null;
             */
            let jsonQuery = JSON.stringify($query);
            this.$http.post('/query.php', {action: 'execute_query_json', json_query: jsonQuery}).then(
                response => {
                    this.data = response.body.items;
                    this.columns = response.body.columns;
                    this.sqlRequest = response.body.request;
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


