Vue.component('selectItem', {
    template: '#select-item',
    props: {
        dbObj: Object,
        model: Object,
        items: Object,
        checkedTables: {
            type: Array,
            default: function () {
                return []
            }
        },
        foreignKeys: {
            type: Array,
            default: function () {
                return []
            }
        },
        foreignTables: {
            type: Array,
            default: function () {
                return []
            }
        }
    },
    data: function () {
        return {
            selected: false
        }
    },
    computed: {
        object: function () {
            if (this.model && this.model.isFK === false) {
                return false;
            } else {
                return true;
            }
        }
    },
    methods: {
        changeStatus: function () {
            this.model.status = !this.model.status;
            if (this.model.status && this.dbObj[this.model.table]) {
                if(this.checkedTables.indexOf(this.model.table) === -1 && this.object) {
                    this.checkedTables.push(this.model.table);
                }
                this._addRow(this.model.table);
            } else if (!this.model.status) {
                if(this.checkedTables.indexOf(this.model.table) > -1 && this.object) {
                    this.checkedTables.splice(this.checkedTables.indexOf(this.model.table), 1);
                }
                delete this.model.rows;
                this._updateDisplaySelect();
            }
            this.selected = !this.selected;
        },
        _addRow: function ($tableName) {
            var $fields = this.dbObj[$tableName];

            for (var $field in $fields) {
                if ($field[0] === '_' || $fields[$field]._field_visibility === false) {
                    continue;
                }
                var $table = this.model.table;
                var $isFK = false;

                if (!this.model.rows) {
                    Vue.set(this.model, 'rows', []);
                }

                if ($fields._FK && $fields._FK[$fields[$field].name]) {
                    $table = $fields._FK[$fields[$field].name].tableName;
                    $isFK = true;
                }

                this.model.rows.push({
                    'name': $fields[$field].name,
                    'table': $table,
                    'isFK': $isFK,
                    'translation': $fields[$field]._field_translation,
                    'status': false,
                    'display': true,
                    'firstParent': false
                });
            }
            this._updateDisplaySelect();
        },
        _updateDisplaySelect: function () {
            if (this.model.firstParent === false) {
                return
            }
            $display = (!(event.target.checked === true));

            for (var $index in this.items.rows) {
                if (this.items.rows[$index].firstParent === true && this.items.rows[$index].status === false) {
                    this.items.rows[$index].display = $display;
                }
            }
        }
    }
});