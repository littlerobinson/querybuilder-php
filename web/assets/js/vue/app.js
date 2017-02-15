// bootstrap the select

var databaseConfigJson = $("#databaseConfigJson").val();

var select = new Vue({
    el: '#app-select',
    data: {
        databaseConfigJson: databaseConfigJson,
        items: [],
        dbObj: '',
        checkedTables: [],
        tableRows: ''
    },
    mounted: function () {
        console.log('mounted method in select app');
        this.dbObj = JSON.parse(databaseConfigJson);
        var $checkedTables = this.checkedTables;
        var $items = {};
        for (var $tableName in this.dbObj) {
            /// Push table name
            if (this.dbObj[$tableName]['_table_visibility'] === false) {
                continue;
            }
            $items[$tableName] = {};
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
                    if (this.dbObj[$tableName][$fieldName]['_field_translation'] != null) {
                        $items[$tableName]['rows'][$fieldName] = this.dbObj[$tableName][$fieldName]['_field_translation'];
                    } else {
                        $items[$tableName]['rows'][$fieldName] = $fieldName;
                    }
                }
            }
        }
        this.items = Object.assign({}, this.items, $items);
    },
    methods: {
        getTableRows: function () {
            var $tableRows = [];
            var $dbObj = JSON.parse(databaseConfigJson);
            var $checkedTables = this.checkedTables;

            /**
             * Manage checkbox when checked or uncheck
             */
            Object.keys($dbObj).map(function (objectValue, index, array) {
                $checkedTables.forEach(function (table) {
                    if (objectValue === table) { /// checkbox checked
                        console.log(objectValue, index, table);
                    } else { /// checkbox unchecked
                        $tableRows = [];
                    }
                });
            });

            this.checkedTables.forEach(function (table) {
                console.log('enter checkedTables loop');
                $tableRows[table] = [];
                var $i = 0;
                Object.keys($dbObj[table]).map(function (objectKey, index) {
                    if (objectKey[0] != '_') {
                        if ($dbObj[table][objectKey]['_field_visibility'] === false) {
                            return;
                        }
                        $tableRows[table][$i] = [];
                        if ($dbObj[table][objectKey]['_field_translation'] !== null) {
                            $tableRows[table][$i].push($dbObj[table][objectKey]['_field_translation']);
                        } else {
                            $tableRows[table][$i].push(objectKey);
                        }
                        $i++;
                    }
                });
            });
            console.log($tableRows);
            this.tableRows = Object.assign({}, this.tableRows, $tableRows);
        }
    }
});

// bootstrap the condition
var condition = new Vue({
    el: '#app-condition',
    data: {
        select: null
    }
});