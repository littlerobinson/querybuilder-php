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
        tableRows: ''
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
        getTableRows: function (event) {
            var $rows = {};
            var $items = this.items;

            /// Change checkbox status
            this.items[event.target.value].status = event.target.checked;

            this.checkedTables.forEach(function ($table) {
                Object.keys($rows).map(function ($rowName, $index) {
                    $rows[$table][$rowName] = $rowName;
                });
            });
            this.tableRows = Object.assign({}, this.tableRows, $rows);
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