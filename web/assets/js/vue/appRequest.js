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
        dbTitle: 'Database name',
        selectTables: {},
        items: {},
        from: {},
        where: [],
        conditions: [],
        limit: 0,
        offset: 0,
        data: [],
        columns: [],
        query: {},
        searchQuery: '',
        sqlRequest: '',
        saveQuery: {},
        saveTitle: '',
        message: {},
        searchable: false,
        showModal: false,
        loading: false,
        saveStyle: {
            background: '#000',
            fontSize: '24px',
        }
    },
    mounted () {
        let self = this;
        this.getListQuery();
        this._getDbTitle();
        this._getDbObject(function () {
            let $items = {};
            $items.name = self.dbTitle;
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
            this.query.from = this.from;
            /// Add conditions to query (feed this.where)
            this.where = [];
            this._addQueryConditions();
            this.query.where = this.where;
            this.query.limit = this.limit;
            this.query.offset = this.offset;
            let $jsonQuery = JSON.stringify(this.query);
            this.$http.post(this.queryPath, {action_query_builder: 'execute_query_json', json_query: $jsonQuery}).then(
                response => {
                    this.loading = false;
                    this.data = response.body.items;
                    this.columns = response.body.columns;
                    this.sqlRequest = response.body.request;
                }, response => {
                    this.loading = false;
                    this.message.error = 'Echec lors de la recherche';
                }
            );
        },
        spreadsheet: function () {
            this.$http.post(this.queryPath, {action_query_builder: 'spreadsheet', columns: this.columns, data: this.data}).then(
                response => {
                    console.log(response);
                    window.open("data:application/vnd.ms-excel, " + response.body);
                }, response => {
                    this.message.error = 'Echec lors de l\'enregistrement du fichier';
                }
            );
        },
        save: function ($isPrivate = 0) {
            this.loading = true;
            this.query.from = this.from;
            /// Add conditions to query (feed this.where)
            this.where = [];
            this._addQueryConditions();
            this.query.where = this.where;
            this.query.limit = this.limit;
            this.query.offset = this.offset;
            this.$http.post(this.queryPath, {
                action_query_builder: 'save_query',
                query: this.query,
                title: this.saveTitle,
                is_private: $isPrivate
            }).then(
                response => {
                    this.showModal = false;
                    this.loading = false;
                    this.saveTitle = '';
                    this.getListQuery();
                    this.message.success = 'Enregistrement de la requête effectuée avec succès';
                }, response => {
                    this.showModal = false;
                    this.loading = false;
                    this.saveTitle = '';
                    this.message.error = 'Echec lors de l\'enregistrement de la requête';
                }
            );
        },
        loadSave: function ($id) {
            this.loading = true;
            this.$http.post(this.queryPath, {action_query_builder: 'load_query', query_id: $id}).then(
                response => {
                    this.loading = false;
                    console.log(response.body.value);
                    console.log(JSON.parse(response.body.value));
                    let $object = JSON.parse(response.body.value);
                    this.from = $object.from;
                    this.where = $object.where;
                    this.limit = $object.limit;
                    this.offset = $object.offset;
                    this.search();
                    this.__cleanQuery();
                    this.message.success = 'Chargement de la requête réussi';
                }, response => {
                    this.__cleanQuery();
                    this.loading = false;
                    this.message.error = 'Echec lors du chargement de la requête';
                }
            );
        },
        deleteSave: function ($id, $index) {
            this.loading = true;
            this.$http.post(this.queryPath, {action_query_builder: 'delete_query', query_id: $id}).then(
                response => {
                    this.saveQuery.splice($index, 1);
                    this.loading = false;
                    this.message.success = 'Suppression de la requête réussi';
                }, response => {
                    this.loading = false;
                    this.message.error = 'Echec lors de la suppression de la requête';
                }
            );
        },
        getListQuery: function () {
            this.loading = true;
            this.$http.post(this.queryPath, {action_query_builder: 'get_list_query'}).then(
                response => {
                    this.showModal = false;
                    this.loading = false;
                    this.saveQuery = response.body;
                }, response => {
                    this.showModal = false;
                    this.loading = false;
                    this.saveQuery = {};
                    this.message.error = 'Echec lors de la récupération de la base de données';
                }
            );
        },
        deleteMessage: function ($key) {
            this.message[$key] = null;
            delete this.message[$key];
        },
        _getDbObject: function (callback) {
            this.$http.post(this.queryPath, {action_query_builder: 'get_db_object'}).then(
                response => {
                    this.dbObj = response.body;
                    callback();
                }, response => {
                    this.message.error = 'Echec lors de connexion à la base de données';
                    console.log('response callback', response)
                }
            );
        },
        _getDbTitle: function () {
            this.$http.post(this.queryPath, {action_query_builder: 'get_db_title'}).then(
                response => {
                    this.dbTitle = response.body;
                }, response => {
                    console.log('response callback', response)
                }
            );
        },
        _addQueryConditions: function () {
            for (let $condition in this.conditions) {
                let $where = {};
                let $logicalOperator = this.conditions[$condition].logicalOperator;
                let $field = this.conditions[$condition].field;
                let $ruleOperator = this.conditions[$condition].ruleOperator;
                let $value = this.conditions[$condition].value;
                let $value2 = this.conditions[$condition].value2;

                $where[$logicalOperator] = {};
                $where[$logicalOperator][$field] = {};
                $where[$logicalOperator][$field][$ruleOperator] = [];
                $where[$logicalOperator][$field][$ruleOperator].push($value);
                if('' !== $value2) {
                    $where[$logicalOperator][$field][$ruleOperator].push($value2);
                }

                this.where.push($where);
            }
        },
        __cleanQuery: function () {
            this.from = {};
            this.where = [];
            this.limit = 0;
            this.offset = 0;
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


