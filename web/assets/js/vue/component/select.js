Vue.component('selectItem', {
    template: '#select-item',
    props: {
        dbObj: Object,
        selectTables: Object,
        model: Object,
        items: Object,
        from: Object,
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
        },
    },
    watch: {
        selected: function () {
            request.searchable = (Object.keys(this.selectTables).length > 0);
        }
    },
    methods: {
        changeStatus: function (event) {
            this.model.status = !this.model.status;
            /// Case adding table
            if (this.model.status && this.dbObj[this.model.table] && this.object) {
                /// Create From variable with select data
                this._manageFrom();
                this._addRow(this.model.table, event);
            } else if (!this.model.status) {
                if (this.object) { /// Case uncheck table
                    this._deleteInObject(this.from, this.model.name);
                    delete this.selectTables[this.model.name];
                }
                if (this.model.table) {
                    delete this.model.rows;
                }
                this._updateDisplaySelect(event);
            }
            this.selected = !this.selected;
            /// Manage from row
            if (!this.object) {
                this._manageFrom();
            }
        },
        _addRow: function ($tableName, event) {
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
            this._updateDisplaySelect(event);
        },
        _updateDisplaySelect: function (event) {
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
        _manageFrom: function () {
            this.depth = this.$parent.depth !== '' ? this.$parent.depth + '.' + this.model.name : this.model.name; /// Add depth info
            let $listDepth = this.depth.split('.');
            let $tmpDepth = {};
            if ($listDepth.length === 1) {
                this.from[$listDepth[0]] = {};
                /// Add to list of Table
                this.selectTables[$listDepth[0]] = {};
                this.selectTables[$listDepth[0]].parentName = null;
                this.selectTables[$listDepth[0]].table = $listDepth[0];
                this.selectTables[$listDepth[0]].name = $listDepth[0];
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
                            this.selectTables[$listDepth[$index]] = {};
                            this.selectTables[$listDepth[$index]].parentName = this.selectTables[this.model.parentName].table;
                            this.selectTables[$listDepth[$index]].name = this.model.name;
                            this.selectTables[$listDepth[$index]].table = this.model.table;
                        } else {
                            if (this.selected) { /// Case add row
                                $tmpDepth[$listDepth[$index]] = $listDepth[$index];
                            } else { /// Case delete row
                                delete $tmpDepth[$listDepth[$index]];
                            }
                        }
                    }
                }
            }
        },
        _deleteInObject: function ($obj, $index) {
            for (let $property in $obj) {
                if ($obj.hasOwnProperty($property)) {
                    if (typeof $obj[$property] === "object") {
                        if ($property === $index) {
                            delete $obj[$property];
                            return;
                        }
                        this._deleteInObject($obj[$property], $index);
                    }
                }
            }
        }
    }
});