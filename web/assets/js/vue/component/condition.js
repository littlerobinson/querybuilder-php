Vue.component('conditionItem', {
    template: '#condition-item',
    props: {
        checkedTables: {
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
        dbObj: {
            type: Object,
            default: function () {
                return {}
            }
        }
    },
    data: function () {
        return {
            conditions: [],
            rows: [],
            newRuleTable: '',
            newRuleRow: '',
            newOperator: '',
            newValue: '',
            newCondition: [],
            operatorList: [
                {value: 'EQUAL', name: 'Est égal à'},
                {value: 'LIKE', name: 'Contient'}
            ]
        }
    },
    methods: {
        addCondition: function () {
            let $newCondition = {
                rule: this.newRuleRow + ' (' + this.newRuleTable + ')',
                operator: this.newOperator,
                value: this.newValue
            };
            this.conditions.push($newCondition);
        },
        addRuleRows: function () {
            this._clearNewCondition();
            let $newRuleTable = this.newRuleTable;

            for (let $tableName in this.dbObj[$newRuleTable]) {
                if('_' === $tableName.substring(0, 1) || !this.dbObj[$newRuleTable][$tableName]._field_visibility) {
                    continue;
                }
                $translation = this.dbObj[$newRuleTable][$tableName]._field_translation !== null
                    ? this.dbObj[$newRuleTable][$tableName]._field_translation
                    : this.dbObj[$newRuleTable][$tableName].name;

                this.rows.push($translation);
            }
        },
        _clearNewCondition: function () {
            this.newRuleRow = '';
            this.newOperator = '';
            this.newValue = '';
            this.rows = [];
        }
    }
});