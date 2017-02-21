var databaseConfigJson = $("#databaseConfigJson").val();

/* *********************************************************************************** */
/* ***************************** Bootstrap SELECT ************************************ */
/* *********************************************************************************** */
var select = new Vue({
    el: '#app-select',
    data: {
        databaseConfigJson: databaseConfigJson,
        items: [],
        dbObj: '',
        checkedTables: [],
        foreignTables: [],
        foreignKeys: []
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        var $items = {};
        for (var $tableName in this.dbObj) {
            /// Push table name
            if (this.dbObj[$tableName]['_table_visibility'] === false) {
                continue;
            }
            $items[$tableName] = {};
            $items[$tableName].status = false;
            if (this.dbObj[$tableName]['_table_translation'] !== null) {
                $items[$tableName].name = this.dbObj[$tableName]['_table_translation'];
            } else {
                $items[$tableName].name = $tableName;
            }
            /// Push rows name
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
                    if (this.dbObj[$tableName][$fieldName]['_field_translation'] !== null) {
                        $items[$tableName]['rows'][$fieldName]['name'] = this.dbObj[$tableName][$fieldName]['_field_translation'];
                    } else {
                        $items[$tableName]['rows'][$fieldName]['name'] = $fieldName;
                    }
                }
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
        changeRowStatus: function (table, row) {
            /// Change checkbox status
            this.items[table].rows[row].status = event.target.checked;
        }
    },
    computed: {
        tableToDisplay: function () {
            if (this.checkedTables.length > 0) {
                return this.checkedTables.concat(this.foreignTables);
            } else {
                var $tableList = [];
                for (var index in this.items) {
                    $tableList.push(index);
                }
                return $tableList;
            }
        }
    }
});

/* *********************************************************************************** */
/* **************************** Bootstrap CONDITION ********************************** */
/* *********************************************************************************** */
var condition = new Vue({
    el: '#app-condition',
    data: {
        select: null
    }
});