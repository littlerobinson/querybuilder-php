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
            newValue2: '',
            type: 'text',
            newCondition: [],
            ruleOperators: [],
            logicalOperators: [
                {value: 'AND', name: 'ET'},
                {value: 'OR', name: 'OU'}
            ],
            selectedLimit: -1,
            limits: [
                {value: 1, name: 1},
                {value: 10, name: 10},
                {value: 25, name: 25},
                {value: 50, name: 50},
                {value: 100, name: 100},
                {value: 250, name: 250},
                {value: 500, name: 500},
                {value: -1, name: '-- Aucune --'},
            ]
        }
    },
    computed: {
        adding: function () {
            return (this.newLogicalOperator !== ''
                && this.newRuleTable !== ''
                && this.newRuleRow !== ''
                && this.newRuleOperator !== ''
                && this.newValue !== '');
        },
    },
    watch: {
        selectedLimit: function () {
            request.limit = this.selectedLimit;
        }
    },
    methods: {
        addCondition: function () {
            /// Case parent or not
            let $field = this.newRuleTable.table + '.' + this.newRuleRow;
            /// Create condition
            let $newCondition = {
                logicalOperator: this.newLogicalOperator,
                field: $field,
                ruleOperator: this.newRuleOperator,
                value: this.newValue,
                value2: this.newValue2
            };
            this.conditions.push($newCondition);
        },
        deleteCondition: function ($conditionKey) {
            if (this.conditions.hasOwnProperty($conditionKey)) {
                delete this.conditions.splice($conditionKey, 1);
            } else {
                console.log('object not exist.');
            }
        },
        addRuleRows: function () {
            this._clearNewCondition();
            let $newRuleTable = this.newRuleTable;

            for (let $tableName in this.dbObj[$newRuleTable.table]) {
                let $isFK = (this.dbObj[$newRuleTable.table]['_FK'] && this.dbObj[$newRuleTable.table]['_FK'][$tableName]) ? true : false;
                /// If is not a row or not visible or FK continue
                if (
                    '_' === $tableName.substring(0, 1)
                    || !this.dbObj[$newRuleTable.table][$tableName]._field_visibility
                    || $isFK
                ) {
                    continue;
                }

                $translation = this.dbObj[$newRuleTable.table][$tableName]._field_translation !== null
                    ? this.dbObj[$newRuleTable.table][$tableName]._field_translation
                    : this.dbObj[$newRuleTable.table][$tableName].name;

                this.rows[this.dbObj[$newRuleTable.table][$tableName].name] = $translation;
            }
        },
        addRuleOperator: function () {
            let $rowType;
            $rowType = this.dbObj[this.newRuleTable.table][this.newRuleRow].type;
            console.log($rowType);
            switch ($rowType) {
                case 'string':
                    this.ruleOperators = [
                        {value: 'EQUAL', name: 'Est égal à'},
                        {value: 'LIKE', name: 'Contient'},
                        {value: 'BEGINS_WITH', name: 'Commence par'},
                        {value: 'ENDS_WITH', name: 'Fini par'}
                    ];
                    this.type = 'text';
                    break;
                case 'datetime':
                    this.ruleOperators = [
                        {value: 'EQUAL', name: 'Est égal à'},
                        {value: 'LESS_THAN', name: 'Inférieur à'},
                        {value: 'MORE_THAN', name: 'Supérieur à'},
                        {value: 'BETWEEN', name: 'Entre'}
                    ];
                    this.type = 'date';
                    break;
                case 'integer':
                    this.ruleOperators = [
                        {value: 'EQUAL', name: 'Est égal à'},
                        {value: 'LESS_THAN', name: 'Inférieur à'},
                        {value: 'MORE_THAN', name: 'Supérieur à'}
                    ];
                    this.type = 'number';
                    break;
                default:
                    this.ruleOperators = [
                        {value: 'EQUAL', name: 'Est égal à'},
                        {value: 'LIKE', name: 'Contient'},
                        {value: 'BEGINS_WITH', name: 'Commence par'},
                        {value: 'ENDS_WITH', name: 'Fini par'}
                    ];
                    this.type = 'text';
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