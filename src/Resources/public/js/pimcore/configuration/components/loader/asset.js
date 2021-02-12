
pimcore.registerNS("pimcore.plugin.pimcoreDataHubBatchImportBundle.configuration.components.loader.asset");
pimcore.plugin.pimcoreDataHubBatchImportBundle.configuration.components.loader.asset = Class.create(pimcore.plugin.pimcoreDataHubBatchImportBundle.configuration.components.abstractOptionType, {

    type: 'asset',

    buildSettingsForm: function() {
        if(!this.form) {

            this.component = new Ext.form.TextField({
                name: this.dataNamePrefix + 'assetPath',
                value: this.data.assetPath,
                fieldCls: 'pimcore_droptarget_input',
                width: 500,
                enableKeyEvents: true,
                allowBlank: false,
                msgTarget: 'under',
                listeners: {
                    render: function (el) {
                        // add drop zone
                        new Ext.dd.DropZone(el.getEl(), {
                            reference: this,
                            ddGroup: "element",
                            getTargetFromEvent: function (e) {
                                return this.reference.component.getEl();
                            },

                            onNodeOver: function (target, dd, e, data) {
                                if (data.records.length === 1 && this.dndAllowed(data.records[0].data)) {
                                    return Ext.dd.DropZone.prototype.dropAllowed;
                                }
                            }.bind(this),

                            onNodeDrop: this.onNodeDrop.bind(this)
                        });

                        el.getEl().on("contextmenu", this.onContextMenu.bind(this));

                    }.bind(this)
                }
            });

            let composite = Ext.create('Ext.form.FieldContainer', {
                fieldLabel: t('asset'),
                layout: 'hbox',
                items: [
                    this.component,
                    {
                        xtype: "button",
                        iconCls: "pimcore_icon_delete",
                        style: "margin-left: 5px",
                        handler: this.empty.bind(this)
                    },{
                        xtype: "button",
                        iconCls: "pimcore_icon_search",
                        style: "margin-left: 5px",
                        handler: this.openSearchEditor.bind(this)
                    }
                ],
                width: 900,
                componentCls: "object_field object_field_type_manyToOneRelation",
                border: false,
                style: {
                    padding: 0
                },
                listeners: {
                    afterrender: function () {
                    }.bind(this)
                }
            });

            this.form = Ext.create('DataHub.BatchImport.StructuredValueForm', {
                defaults: {
                    labelWidth: 200,
                    width: 600
                },
                border: false,
                width: 900,
                items: [
                    composite
                ]
            });
        }

        return this.form;
    },

    onNodeDrop: function (target, dd, e, data) {

        if(!pimcore.helpers.dragAndDropValidateSingleItem(data)) {
            return false;
        }

        data = data.records[0].data;

        if (this.dndAllowed(data)) {
            this.component.setValue(data.path);
            return true;
        } else {
            return false;
        }
    },

    onContextMenu: function (e) {

        var menu = new Ext.menu.Menu();
        menu.add(new Ext.menu.Item({
            text: t('empty'),
            iconCls: "pimcore_icon_delete",
            handler: function (item) {
                item.parentMenu.destroy();
                this.empty();
            }.bind(this)
        }));

        menu.add(new Ext.menu.Item({
            text: t('search'),
            iconCls: "pimcore_icon_search",
            handler: function (item) {
                item.parentMenu.destroy();
                this.openSearchEditor();
            }.bind(this)
        }));

        menu.showAt(e.getXY());

        e.stopEvent();
    },

    openSearchEditor: function () {
        pimcore.helpers.itemselector(false, this.addDataFromSelector.bind(this), {
            type: ['asset'],
            subtype: {
                asset: ['text', 'document']
            },
            specific: {}
        }, {});
    },

    addDataFromSelector: function (data) {
        this.component.setValue(data.fullpath);
    },

    empty: function () {
        this.component.setValue("");
    },

    dndAllowed: function (data) {
        if (data.elementType === 'asset') {
            return data.type === 'document' || data.type === 'text';
        }
        return false;
    }


});