Vue.component('conditionItem', {
    template: '#condition-item',
    props: {
        conditions: {
            type: Array,
            default: function () {
                return []
            }
        },
        items: {
            type: Object,
            default: function () {
                return {}
            }
        },
        selectTables: {
            type: Object,
            default: function () {
                return {}
            }
        },
        dbObj: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },
    data: function () {
        return {
            rows: {},
            newLogicalOperator: '',
            newRuleTable: '',
            newRuleRow: '',
            newRuleOperator: '',
            newValue: '',
            newCondition: [],
            ruleOperators: [
                {value: 'EQUAL', name: 'Est égal à'},
                {value: 'LIKE', name: 'Contient'}
            ],
            logicalOperators: [
                {value: 'AND', name: 'ET'},
                {value: 'OR', name: 'OU'}
            ]
        }
    },
    methods: {
        addCondition: function () {
            /// Case parent or not
            let $field = (this.newRuleTable.parentTable) ? this.newRuleTable.parentTable + '.' + this.newRuleTable.field : this.newRuleTable.table + '.' + this.newRuleRow;
            /// Create condition
            let $newCondition = {
                logicalOperator: this.newLogicalOperator,
                field: $field,
                ruleOperator: this.newRuleOperator,
                value: this.newValue
            };
            this.conditions.push($newCondition);
        },
        addRuleRows: function () {
            this._clearNewCondition();
            let $newRuleTable = this.newRuleTable;

            for (let $tableName in this.dbObj[$newRuleTable.table]) {
                if ('_' === $tableName.substring(0, 1) || !this.dbObj[$newRuleTable.table][$tableName]._field_visibility) {
                    continue;
                }
                $translation = this.dbObj[$newRuleTable.table][$tableName]._field_translation !== null
                    ? this.dbObj[$newRuleTable.table][$tableName]._field_translation
                    : this.dbObj[$newRuleTable.table][$tableName].name;

                this.rows[this.dbObj[$newRuleTable.table][$tableName].name] = $translation;
            }
        },
        _clearNewCondition: function () {
            this.newRuleRow = '';
            this.newRuleOperator = '';
            this.newValue = '';
            this.rows = {};
        }
    }
});