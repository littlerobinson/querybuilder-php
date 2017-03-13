var databaseConfigJson = $("#databaseConfigJson").val();

/* *********************************************************************************** */
/* ******************************* Bootstrap REQUEST ************************************* */
/* *********************************************************************************** */
var request = new Vue({
    el: '#app-request',
    data: {
        jsonQuery: {},
        dbObj: {},
        items: {},
        listTables: [],
        checkedTables: [],
        checkedRows: [],
        foreignTables: [],
        foreignKeys: []
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        var $items = {};
        $items.name = "Liste des tables";
        $items.parent = true;
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
                    'status': false,
                    'parent': false
                });
            }
        }
        this.items = Object.assign({}, this.items, $items);
    },
    methods: {
        changeTableStatus: function () {
            /// Change checkbox status
            this.items[event.target.value].status = event.target.checked;
            /**
             * Add Foreign Keys and Disable tables with no relation
             * Add FK if checked
             */
            $foreignKeys = this.dbObj[event.target.value]['_FK'];
            $status = this.items[event.target.value].status;
            for (var $field in $foreignKeys) {
                /// Add foreignKey
                $indexFK = this.foreignKeys.indexOf($foreignKeys[$field]['tableName'] + '.' + $foreignKeys[$field]['columns']);
                $indexFKTable = this.foreignTables.indexOf($foreignKeys[$field]['tableName']);
                $status ? this.foreignKeys.push($foreignKeys[$field]['tableName'] + '.' + $foreignKeys[$field]['columns']) : this.foreignKeys.splice($indexFK, 1);
                if ($status === true && $indexFKTable === -1) {
                    this.foreignTables.push($foreignKeys[$field]['tableName']);
                } else if (
                    this.checkedTables.indexOf($foreignKeys[$field]['tableName']) === -1
                    && $status === false
                    && $indexFKTable > -1
                ) { /// delete foreignTable indexes if not anymore used
                    $selectListTables = []; /// Table list not to delete in foreignTable (keep in select query)
                    /// Get the table list to keep
                    for (var $actualSelect in this.foreignKeys) {
                        $selectTable = this.foreignKeys[$actualSelect].split(".");
                        $selectListTables.push($selectTable[0]);
                    }
                    if ($selectListTables.indexOf($foreignKeys[$field]['tableName']) === -1) {
                        this.foreignTables.splice(this.foreignTables.indexOf($foreignKeys[$field]['tableName']), 1);
                    }
                }
            }
        },
        changeParentRowStatus: function ($table, $row) {
            /// Change checkbox status
            this.items[$table].rows[$row].status = event.target.checked;
            if (this.checkedRows.indexOf($table) === -1) {
                this.checkedRows[$table] = {};
            }
            /// If is foreign key
            if (this.items[$table].rows[$row]._FK !== null) {
                $fk = this.items[$table].rows[$row]._FK.split(".");
                this.checkedTables.push($fk[0]);
                $tableItems = this._getTableItems($fk[0]);
                this.items[$table].rows[$row]['rows'] = {};
                this.items[$table].rows[$row]['rows'] = $tableItems.rows;
            }
        },
        changeChildRowStatus: function ($firstParent, $rowKey, $row, $parent) {
            /// If is table, add rows to item object
            if ($row._FK) {
                $actualFk = $row._FK.split(".");
                this.checkedTables.push($actualFk[0]);
                $rowsPath = this.items[$firstParent].rows[$parent].rows[$];
                Vue.set(this.items[$firstParent].rows[$parent].rows[$rowKey], 'rows', this.dbObj[$actualFk[0]]);
                /// Change checkbox status
                Vue.set(this.items[$firstParent].rows[$parent].rows[$rowKey], 'status', event.target.checked);
                /// Feed checkedRows value
                console.log(this.dbObj[$actualFk[0]]);
                console.log(this.items[$firstParent].rows[$parent]);
                //this.checkedRows[$table]
            }
        },
        search: function () {
            console.log('search result');
            this.$http.post('/', [body], [options]).then(successCallback, errorCallback);
        },
        _getTableItems: function ($tableName) {
            if (typeof this.dbObj[$tableName] !== 'object') {
                return false;
            }
            if (this.dbObj[$tableName]['_table_visibility'] === false) {
                return false;
            }
            var $items = {};
            $items[$tableName] = {};
            $items[$tableName].status = false;
            if (this.dbObj[$tableName]['_table_translation'] !== null) {
                $items[$tableName].name = this.dbObj[$tableName]['_table_translation'];
            } else {
                $items[$tableName].name = $tableName;
            }
            for (var $fieldName in this.dbObj[$tableName]) {
                if ($fieldName[0] != '_') {
                    if (this.dbObj[$tableName][$fieldName]['_field_visibility'] === false) {
                        continue;
                    }
                    if (typeof $items[$tableName]['rows'] !== 'object') {
                        $items[$tableName]['rows'] = {};
                    }

                    $items[$tableName]['rows'][$fieldName] = {};
                    $items[$tableName]['rows'][$fieldName]['status'] = false;
                    $items[$tableName]['rows'][$fieldName]['_FK'] = null;
                    $items[$tableName]['rows'][$fieldName]['rows'] = null;

                    if (typeof this.dbObj[$tableName]['_FK'] !== 'undefined' && typeof this.dbObj[$tableName]['_FK'][$fieldName] !== 'undefined') {
                        $fkTableName = this.dbObj[this.dbObj[$tableName]['_FK'][$fieldName]['tableName']]['_table_translation'] !== null
                            ? this.dbObj[this.dbObj[$tableName]['_FK'][$fieldName]['tableName']]['_table_translation']
                            : this.dbObj[$tableName]['_FK'][$fieldName]['tableName'];
                        $rowName = (this.dbObj[$tableName][$fieldName]['_field_translation'] !== null) ? ' (' + this.dbObj[$tableName][$fieldName]['_field_translation'] + ')' : '';
                        $items[$tableName]['rows'][$fieldName]['name'] = $fkTableName + $rowName;
                        $items[$tableName]['rows'][$fieldName]['_FK'] = this.dbObj[$tableName]['_FK'][$fieldName]['tableName'] + '.' + this.dbObj[$tableName][$fieldName]['name'];
                    } else {
                        $rowName = (this.dbObj[$tableName][$fieldName]['_field_translation'] !== null) ? this.dbObj[$tableName][$fieldName]['_field_translation'] : this.dbObj[$tableName][$fieldName]['name'];
                        $items[$tableName]['rows'][$fieldName]['name'] = $rowName;
                    }
                }
            }
            return $items[$tableName];
        }
    },
    computed: {
        tableToDisplay: function () {
            if (this.checkedTables.length > 0) {
                //return this.checkedTables.concat(this.foreignTables);
                return this.checkedTables;
            } else {
                var $tableList = [];
                for (var index in this.items) {
                    $tableList.push(index);
                }
                return $tableList;
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
