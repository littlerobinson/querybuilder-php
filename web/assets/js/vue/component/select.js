Vue.component('selectItem', {
    template: '#select-item',
    props: {
        dbObj: Object,
        tables: Object,
        model: Object,
        items: Object,
        from: Object,
        checkedTables: Array,
        checkedRows: Array
    },
    data: function () {
        return {
            selected: false,
            depth: ''
        }
    },
    computed: {
        object: function () {
            return !(this.model && this.model.isFK === false);
        }
    },
    methods: {
        changeStatus: function () {
            this.model.status = !this.model.status;
            /// Case adding table
            if (this.model.status && this.dbObj[this.model.table] && this.object) {
                if (this.checkedTables.indexOf(this.model.table) === -1) {
                    this.checkedTables.push(this.model.table);
                }
                /// Create From variable with select data
                this._addFrom();
                this._addRow(this.model.table);
            } else if (!this.model.status) {
                if (this.checkedTables.indexOf(this.model.table) > -1 && this.object) { /// Case uncheck table
                    this.checkedTables.splice(this.checkedTables.indexOf(this.model.table), 1);
                    delete this.from[this.model.table];
                }
                delete this.model.rows;
                this._updateDisplaySelect();
            }
            this.selected = !this.selected;
            /// Push/Delete in checkedRows array
            if (this.selected && !this.object) {
                this._addFrom();
                this.checkedRows.push(this.model.table + '.' + this.model.name);
            } else if (!this.selected && !this.object) { /// Case uncheck row
                this.checkedRows.splice(this.checkedRows.indexOf(this.model.table + '.' + this.model.name), 1);
                delete this.from[this.model.table][this.model.name];
            }
        },
        _addRow: function ($tableName) {
            let $fields = this.dbObj[$tableName];

            for (let $field in $fields) {
                /// add table visibility condition
                if ($field[0] === '_' || $fields[$field]._field_visibility === false) {
                    continue;
                }
                let $table = this.model.table;
                let $parentName = this.model.name;
                let $isFK = false;

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
                    'firstParent': false,
                    'parentName': $parentName
                });
            }
            this._updateDisplaySelect();
        },
        _updateDisplaySelect: function () {
            if (this.model.firstParent === false) {
                return
            }
            $display = (!(event.target.checked === true));

            for (let $index in this.items.rows) {
                if (this.items.rows[$index].firstParent === true && this.items.rows[$index].status === false) {
                    this.items.rows[$index].display = $display;
                }
            }
        },
        _addFrom: function () {
            this.depth = this.$parent.depth !== '' ? this.$parent.depth + '.' + this.model.name : this.model.name; /// Add depth info
            let $listDepth = this.depth.split('.');
            let $tmpDepth = {};
            if ($listDepth.length === 1) {
                this.from[$listDepth[0]] = {};
                /// Add to list of Table
                this.tables[$listDepth[0]] = {};
                this.tables[$listDepth[0]].parentName = null;
                this.tables[$listDepth[0]].table = $listDepth[0];
                this.tables[$listDepth[0]].name = $listDepth[0];
            } else {
                for (let $index in $listDepth) {
                    if ($index === '0') { /// First loop
                        $tmpDepth = this.from[$listDepth[$index]];
                    } else if ($index != ($listDepth.length - 1)) { /// Middle loops
                        $tmpDepth = $tmpDepth[$listDepth[$index]];
                    } else { /// Last loop
                        if (this.object) {
                            $tmpDepth[$listDepth[$index]] = {};
                            /// Add list of Table
                            this.tables[$listDepth[$index]] = {};
                            this.tables[$listDepth[$index]].parentName = this.tables[this.model.parentName].table;
                            this.tables[$listDepth[$index]].name = this.model.name;
                            this.tables[$listDepth[$index]].table = this.model.table;
                        } else {
                            $tmpDepth[$listDepth[$index]] = $listDepth[$index];
                        }
                    }
                }
            }
        }
    }
});